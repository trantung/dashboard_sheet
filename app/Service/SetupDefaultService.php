<?php

namespace App\Service;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Temp;

class SetupDefaultService
{
    public function addDomainToDns($fullDomain, $projectPath, $portProject = 3001)
    {
        try {
            $apiToken = getenv('CLOUD_FLARE_API_TOKEN');
            $ip = getenv('SERVER_IP_ADDRESS');
            [$sub, $domain] = $this->extractSubdomainAndDomain($fullDomain, DOMAIN_BASE);
            $zoneId = $this->getZoneIdByDomain($apiToken, $domain);

            if (!$zoneId) {
                return response()->json([
                    'status' => false,
                    'message' => "Could not find Zone ID for {$domain}"
                ]);
            }

            $response = $this->createSubdomainOnCloudflare($apiToken, $zoneId, $sub, $domain, $ip);
            // $projectPath = '/var/www/html/sheetany_blog/dist';
            $vhostResult = $this->addApacheVirtualHost($fullDomain, $projectPath, $portProject);

            return response()->json([
                'status' => true,
                'message' => 'Subdomain and VirtualHost created successfully.',
                'data' => [
                    'cloudflare_response' => json_decode($response, true),
                    'vhost_result' => $vhostResult
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Error while creating subdomain: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function extractSubdomainAndDomain($fullDomain, $domainRoot = DOMAIN_BASE)
    {
        if (!str_ends_with($fullDomain, $domainRoot)) {
            throw new Exception("The domain name does not match the root domain ({$domainRoot})");
        }

        $subdomain = str_replace(".$domainRoot", '', $fullDomain);
        return [$subdomain, $domainRoot];
    }

    public function getZoneIdByDomain($apiToken, $domain) {
        $url = "https://api.cloudflare.com/client/v4/zones?name={$domain}";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$apiToken}",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        return $data['success'] === true && count($data['result']) > 0 ? $data['result'][0]['id'] : null;
    }

    public function createSubdomainOnCloudflare($apiToken, $zoneId, $subdomain, $domain, $ipAddress) {
        $url = "https://api.cloudflare.com/client/v4/zones/{$zoneId}/dns_records";

        $postData = [
            "type"    => "A",
            "name"    => "{$subdomain}.{$domain}",
            "content" => $ipAddress,
            "ttl"     => 1,
            "proxied" => false
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$apiToken}",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function addApacheVirtualHost($domain, $projectPath, $portProject = 3001)
    {
        try {
            $confPath = "/etc/apache2/sites-available/{$domain}.conf";
            $vhostConfig = <<<EOL
                <VirtualHost *:80>
                    ServerName {$domain}
                    ErrorLog \${APACHE_LOG_DIR}/{$domain}_error.log
                    CustomLog \${APACHE_LOG_DIR}/{$domain}_access.log combined
                    ProxyPreserveHost On
                    ProxyRequests Off
                    ProxyPass / http://localhost:{$portProject}/
                    ProxyPassReverse / http://localhost:{$portProject}/
                    RewriteEngine on
                    RewriteCond %{HTTP:Upgrade} =websocket [NC]
                    RewriteRule /(.*) ws://localhost:{$portProject}/\$1 [P,L]
                </VirtualHost>
                EOL;

            // Create the virtual host configuration file
            $command = "echo " . escapeshellarg($vhostConfig) . " | sudo tee {$confPath} > /dev/null";
            exec($command, $output1, $code1);
            if ($code1 !== 0) {
                return [
                    'status' => false,
                    'message' => "Unable to create configuration file (possibly due to missing sudo permissions)."
                ];
            }

            // Enable the virtual host
            exec("sudo a2ensite {$domain}.conf", $out1, $c1);

            // Ensure SSL configuration exists
            $this->ensureSSLConfig($domain, $portProject);

            // Reload Apache
            exec("sudo systemctl reload apache2", $out3, $c2);

            if ($c1 === 0 && $c2 === 0) {
                return [
                    'status' => true,
                    'message' => "Successfully created and enabled VirtualHost for {$domain}.",
                    'output' => [$out1, $out3]
                ];
            }

            return [
                'status' => false,
                'message' => 'An error occurred while enabling the VirtualHost.',
                'output' => [$out1 ?? '', $out3 ?? '']
            ];
        } catch (\Exception $e) {
            Log::error("Error while creating VirtualHost: " . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    public function ensureSSLConfig($domain, $portProject = 3001)
    {
        try {
            $sslConfPath = "/etc/apache2/sites-available/{$domain}-le-ssl.conf";
            $httpConfPath = "/etc/apache2/sites-available/{$domain}.conf";
            $certDir = "/etc/letsencrypt/live/{$domain}";
            $reloadCmd = "sudo systemctl reload apache2";

            // Helper function để check file/directory bằng sudo (vì cert được tạo bởi root)
            $checkFile = function($path) {
                exec("sudo test -f " . escapeshellarg($path) . " && echo 'exists' || echo 'not_exists'", $checkOutput, $checkCode);
                $result = !empty($checkOutput) && $checkOutput[0] === 'exists';
                return $result;
            };
            $checkDir = function($path) {
                exec("sudo test -d " . escapeshellarg($path) . " && echo 'exists' || echo 'not_exists'", $checkOutput, $checkCode);
                $result = !empty($checkOutput) && $checkOutput[0] === 'exists';
                Log::info("Checking directory {$path}: " . ($result ? 'exists' : 'not_exists') . " (output: " . json_encode($checkOutput) . ")");
                return $result;
            };

            // Nếu file ssl config đã tồn tại và cert cũng đã có thì bỏ qua
            if ($checkFile($sslConfPath) && $checkDir($certDir)) {
                Log::info("SSL config and certificate already exist for {$domain}");
                return;
            }

            // Đảm bảo cert đã tồn tại, nếu chưa thì mới chạy certbot
            $certExists = $checkDir($certDir)
                && $checkFile($certDir . '/fullchain.pem')
                && $checkFile($certDir . '/privkey.pem');

            if (!$certExists) {
                // Kiểm tra domain đã được public DNS resolve chưa
                $maxAttempts = 10;
                $delaySeconds = 10;
                $resolved = false;

                for ($i = 0; $i < $maxAttempts; $i++) {
                    $dns = dns_get_record($domain, DNS_A);
                    if (!empty($dns)) {
                        $resolved = true;
                        Log::info("DNS resolved for {$domain}: " . json_encode($dns));
                        break;
                    } else {
                        Log::info("Waiting for DNS to resolve for {$domain}... attempt " . ($i + 1));
                        sleep($delaySeconds);
                    }
                }

                if (!$resolved) {
                    Log::error("DNS not resolved for {$domain} after {$maxAttempts} attempts");
                    return;
                }

                // Thử chạy certbot để cấp SSL (chỉ lo phần cert, không phụ thuộc vào việc nó tạo vhost hay không)
                $certbotCmd = escapeshellcmd("sudo certbot --apache -d {$domain} --non-interactive --agree-tos -m admin@{$domain} --redirect");

                exec($certbotCmd . " 2>&1", $output, $code);
                Log::info("Certbot output for {$domain}:", $output);

                if ($code !== 0) {
                    // Một số trường hợp certbot vẫn cấp SSL thành công nhưng trả về mã lỗi
                    Log::warning("Certbot exited with non-zero code {$code} for {$domain}");
                }

                // Đợi và retry để certbot hoàn tất việc tạo file (có thể mất vài giây)
                $maxRetries = 10;
                $retryDelay = 2;
                $certExists = false;

                for ($retry = 0; $retry < $maxRetries; $retry++) {
                    sleep($retryDelay);

                    // Cập nhật lại trạng thái cert (dùng sudo để check vì file được tạo bởi root)
                    $certExists = $checkDir($certDir)
                        && $checkFile($certDir . '/fullchain.pem')
                        && $checkFile($certDir . '/privkey.pem');

                    if ($certExists) {
                        Log::info("Certificate files confirmed for {$domain} after " . (($retry + 1) * $retryDelay) . " seconds");
                        break;
                    } else {
                        Log::info("Waiting for certificate files for {$domain}... retry " . ($retry + 1) . "/{$maxRetries}");
                    }
                }

                // Nếu sau khi chờ vẫn không detect được file cert (do hạn chế sudo, quyền, v.v.)
                // thì chỉ log cảnh báo, KHÔNG return; vẫn tiếp tục tạo ssl.conf và reload Apache.
                if (!$certExists) {
                    Log::warning("Certificate files COULD NOT be verified for {$domain} after running certbot and waiting " . ($maxRetries * $retryDelay) . " seconds, continuing to create SSL vhost anyway.");
                }
            }

            // Đến đây chắc chắn cert đã tồn tại, nếu chưa có ssl conf thì tự tạo
            if (!$checkFile($sslConfPath)) {
                // Lấy portProject từ file http vhost ({$domain}.conf)
                if ($checkFile($httpConfPath)) {
                // Thêm sudo vào lệnh cat
                exec("sudo /usr/bin/cat " . escapeshellarg($httpConfPath), $confLines, $catCode);
                
                if ($catCode === 0) {
                    $confContent = implode("\n", $confLines);
                    // Regex nên linh hoạt hơn (phòng trường hợp khoảng trắng khác nhau)
                    if (preg_match('/ProxyPass\s+\/\s+http:\/\/localhost:(\d+)\/?/', $confContent, $matches)) {
                        $portProject = $matches[1];
                        Log::info("Found port for {$domain}: {$portProject}");
                    }
                } else {
                    Log::error("Failed to cat config file for {$domain}. Code: {$catCode}");
                }
            }

                $sslVhostConfig = <<<EOL
                <IfModule mod_ssl.c>
                    <VirtualHost *:443>
                        ServerName {$domain}
                        ErrorLog \${APACHE_LOG_DIR}/{$domain}_ssl_error.log
                        CustomLog \${APACHE_LOG_DIR}/{$domain}_ssl_access.log combined
                        SSLEngine on
                        SSLCertificateFile /etc/letsencrypt/live/{$domain}/fullchain.pem
                        SSLCertificateKeyFile /etc/letsencrypt/live/{$domain}/privkey.pem
                        ProxyPreserveHost On
                        ProxyRequests Off
                        ProxyPass / http://localhost:{$portProject}/
                        ProxyPassReverse / http://localhost:{$portProject}/
                        RewriteEngine on
                        RewriteCond %{HTTP:Upgrade} =websocket [NC]
                        RewriteRule /(.*) ws://localhost:{$portProject}/\$1 [P,L]
                    </VirtualHost>
                </IfModule>
                EOL;

                $createSslCmd = "echo " . escapeshellarg($sslVhostConfig) . " | sudo tee {$sslConfPath} > /dev/null";
                exec($createSslCmd, $sslOut, $sslCode);
                if ($sslCode !== 0) {
                    Log::error("Failed to create SSL VirtualHost config for {$domain}");
                    return;
                }

                // Enable SSL site
                exec("sudo a2ensite {$domain}-le-ssl.conf", $enableOut, $enableCode);
                if ($enableCode !== 0) {
                    Log::error("Failed to enable SSL site for {$domain}");
                    return;
                }
            }
            
            // Nếu file ssl config đã tồn tại, reload Apache để áp dụng
            if ($checkFile($sslConfPath)) {
                exec($reloadCmd, $reloadOutput, $reloadCode);
                Log::info("Apache reloaded for {$domain} with SSL config");
            } else {
                Log::error("SSL config not found for {$domain} after processing");
            }

        } catch (\Exception $e) {
            Log::error("Exception when creating SSL for {$domain}: " . $e->getMessage());
        }
    }

    //delete domain
    public function deleteDomain($request)
    {
        try {
            $apiToken = getenv('CLOUD_FLARE_API_TOKEN');
            $fullDomain = $request['full_domain'];
            [$sub, $domain] = $this->extractSubdomainAndDomain($fullDomain, DOMAIN_BASE);
            $zoneId = $this->getZoneIdByDomain($apiToken, $domain);
            $result = $this->deleteApacheVirtualHostAndCloudflare($fullDomain, $apiToken, $zoneId);
            return response()->json([
                'status' => true,
                'message' => 'Delete domain successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error("Error when delete domain: " . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function deleteApacheVirtualHostAndCloudflare($domain, $cloudflareToken, $zoneId)
    {
        $confFile = "{$domain}.conf";
        $confFileSSL = "{$domain}.-le-ssl.conf";
        $confPath = "/etc/apache2/sites-available/{$confFile}";
        $enabledPath = "/etc/apache2/sites-enabled/{$confFile}";
        $confPathSSL = "/etc/apache2/sites-available/{$confFileSSL}";
        $enabledPathSSL = "/etc/apache2/sites-enabled/{$confFileSSL}";
        // Disable the Apache site
        exec("sudo a2dissite {$confFile}", $out1, $code1);
        exec("sudo a2dissite {$confFileSSL}", $out11, $code11);
        // Remove symlink if exists
        if (file_exists($enabledPath)) {
            exec("sudo rm -f {$enabledPath}", $outSymlink, $codeSymlink);
        } else {
            $codeSymlink = 0;
        }
        if (file_exists($enabledPathSSL)) {
            exec("sudo rm -f {$enabledPathSSL}", $outSymlink1, $codeSymlink1);
        } else {
            $codeSymlink1 = 0;
        }
      
        // Remove the actual config file if it exists
        if (file_exists($confPath)) {
            exec("sudo rm -f {$confPath}", $out2, $code2);
        } else {
            $code2 = 0;
        }

        if (file_exists($confPathSSL)) {
            exec("sudo rm -f {$confPathSSL}", $out22, $code22);
        } else {
            $code22 = 0;
        }
        // Delete SSL certificate using certbot
        $command = "sudo certbot delete --cert-name " . escapeshellarg($domain) . " 2>&1";
        exec($command, $output, $returnCode);

        // Remove auto-generated SSL config file
        $commandRemoveSSL = "sudo rm -f {$domain}-le-ssl.conf";
        exec($commandRemoveSSL, $outputSSL, $returnCodeSSL);

        // Reload Apache
        exec("sudo systemctl reload apache2", $outReload, $codeReload);

        // Get DNS record ID from Cloudflare
        $recordId = $this->getCloudflareDNSRecordId($domain, $cloudflareToken, $zoneId);
        $deleted = false;

        // Attempt to delete DNS record
        if ($recordId) {
            $deleted = $this->deleteCloudflareDNSRecord($recordId, $cloudflareToken, $zoneId);
        }

        return [
            'apache' => $code1 === 0 && $code2 === 0 && $codeSymlink === 0,
            'cloudflare_deleted' => $deleted,
            'messages' => [
                'apache' => ($code1 === 0 && $code2 === 0 && $codeSymlink === 0)
                    ? "Successfully deleted VirtualHost for {$domain}."
                    : "Failed to delete VirtualHost.",
                'cloudflare' => $deleted
                    ? "DNS record deleted from Cloudflare."
                    : "Could not find or delete DNS record from Cloudflare."
            ]
        ];
    }

    private function getCloudflareDNSRecordId($domain, $token, $zoneId)
    {
        $url = "https://api.cloudflare.com/client/v4/zones/{$zoneId}/dns_records?type=A&name={$domain}";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$token}",
                "Content-Type: application/json"
            ]
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        return $data['success'] && !empty($data['result']) ? $data['result'][0]['id'] : null;
    }

    private function deleteCloudflareDNSRecord($recordId, $token, $zoneId)
    {
        $url = "https://api.cloudflare.com/client/v4/zones/{$zoneId}/dns_records/{$recordId}";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$token}",
                "Content-Type: application/json"
            ]
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        return $data['success'] ?? false;
    }

    public function createDatabase($databaseName)
    {
        try {
            // Escape the database name to avoid injection
            $safeDatabaseName = escapeshellarg($databaseName);
            $dbUser = escapeshellarg(env('DB_USERNAME'));
            $dbPassword = escapeshellarg(env('DB_PASSWORD'));

            // Command to create the database
            $command = "mysql -u {$dbUser} -p{$dbPassword} -e \"CREATE DATABASE IF NOT EXISTS {$databaseName};\"";

            exec($command, $output, $returnVar);
            if ($returnVar !== 0) {
                throw new Exception("Error creating database: " . implode("\n", $output));
            }

            return response()->json([
                'status' => true,
                'message' => "Database {$safeDatabaseName} has been created successfully."
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function getDataFromSheet($tempId)
    {
        $temp = Temp::findOrFail($tempId);

        try {
            // Extract spreadsheet ID from URL
            preg_match('/\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $temp->google_sheet, $matches);
            if (!$matches) {
                return response()->json(['error' => 'Invalid Google Sheets URL'], 400);
            }

            $spreadsheetId = $matches[1];
            $apiKey = env('GOOGLE_SHEETS_API_KEY');

            // Step 1: Get list of sheets
            $metadataResponse = Http::get("https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}", [
                'key' => $apiKey,
            ]);

            if (!$metadataResponse->successful()) {
                return response()->json(['error' => 'Failed to retrieve sheet metadata'], 400);
            }

            $sheetData = [];
            $sheets = $metadataResponse->json()['sheets'] ?? [];

            foreach ($sheets as $sheet) {
                $title = $sheet['properties']['title'];

                // Step 2: Get values from each sheet
                $valueResponse = Http::get("https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/" . urlencode($title), [
                    'key' => $apiKey,
                ]);

                if ($valueResponse->successful()) {
                    $values = $valueResponse->json()['values'] ?? [];
                    $sheetData[$title] = $values;
                } else {
                    $sheetData[$title] = ['error' => 'Failed to retrieve data'];
                }
            }

            return response()->json([
                'connected' => true,
                'spreadsheet_id' => $spreadsheetId,
                'data' => $sheetData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error connecting to Google Sheets: ' . $e->getMessage()
            ], 500);
        }
    }
}
