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

            [$sub, $domain] = $this->extractSubdomainAndDomain($fullDomain, 'microgem.io.vn');
            $zoneId = $this->getZoneIdByDomain($apiToken, $domain);

            if (!$zoneId) {
                return response()->json([
                    'status' => false,
                    'message' => "Could not find Zone ID for {$domain}"
                ]);
            }

            $response = $this->createSubdomainOnCloudflare($apiToken, $zoneId, $sub, $domain, $ip);
            
            // Purge Cloudflare DNS cache to speed up DNS propagation
            $this->purgeCloudflareDNSCache($fullDomain);
            
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

    public function extractSubdomainAndDomain($fullDomain, $domainRoot = 'microgem.io.vn')
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

    /**
     * Purge Cloudflare DNS cache to speed up DNS propagation
     * This helps Let's Encrypt see the new DNS record immediately
     */
    private function purgeCloudflareDNSCache($domain)
    {
        try {
            $purgeUrl = "https://cloudflare-dns.com/purge?name={$domain}&type=A";
            $ch = curl_init($purgeUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            Log::info("Purged Cloudflare DNS cache", [
                'domain' => $domain,
                'http_code' => $httpCode,
                'response' => $response
            ]);
            
            return $httpCode === 200;
        } catch (\Exception $e) {
            Log::warning("Failed to purge Cloudflare DNS cache: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enable Cloudflare proxy (orange cloud) after SSL certificate is created
     */
    public function enableCloudflareProxy($fullDomain, $apiToken)
    {
        try {
            [$sub, $domain] = $this->extractSubdomainAndDomain($fullDomain, 'microgem.io.vn');
            $zoneId = $this->getZoneIdByDomain($apiToken, $domain);
            
            if (!$zoneId) {
                Log::error("Could not find Zone ID for enabling proxy", ['domain' => $domain]);
                return false;
            }
            
            // Get DNS record ID
            $recordId = $this->getCloudflareDNSRecordId($fullDomain, $apiToken, $zoneId);
            
            if (!$recordId) {
                Log::error("Could not find DNS record ID for enabling proxy", ['domain' => $fullDomain]);
                return false;
            }
            
            // Update DNS record to enable proxy
            $url = "https://api.cloudflare.com/client/v4/zones/{$zoneId}/dns_records/{$recordId}";
            
            $updateData = [
                "proxied" => true
            ];
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer {$apiToken}",
                "Content-Type: application/json"
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($updateData));
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $data = json_decode($response, true);
            
            if ($data['success'] ?? false) {
                Log::info("Enabled Cloudflare proxy for domain", [
                    'domain' => $fullDomain,
                    'http_code' => $httpCode
                ]);
                return true;
            } else {
                Log::error("Failed to enable Cloudflare proxy", [
                    'domain' => $fullDomain,
                    'response' => $data
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Exception when enabling Cloudflare proxy: " . $e->getMessage());
            return false;
        }
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
            $this->ensureSSLConfig($domain);

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

    public function ensureSSLConfig($domain)
    {
        try {
            $sslConfPath = "/etc/apache2/sites-available/{$domain}-le-ssl.conf";
            $domainEscaped = escapeshellarg($domain);
            $emailEscaped = escapeshellarg("admin@$domain");

            // Viết lệnh rõ ràng từng tham số
            $certbotCmd = "sudo certbot --apache -d $domainEscaped --non-interactive --agree-tos -m $emailEscaped --redirect 2>&1";

            exec($certbotCmd, $output, $returnCode);

            $reloadCmd = "sudo systemctl reload apache2";

            // Nếu SSL đã tồn tại, không cần tạo lại
            if (file_exists($sslConfPath)) {
                Log::info("SSL already exists for {$domain}");
                return;
            }

            // Kiểm tra domain đã được public DNS resolve chưa
            // Sử dụng Cloudflare DNS (1.1.1.1) để check vì Let's Encrypt thường dùng DNS này
            $maxAttempts = 8; // Giảm số lần thử vì đã purge cache
            $delaySeconds = 5; // Giảm delay vì DNS đã được purge
            $resolved = false;
            $cloudflareDnsServer = '1.1.1.1'; // Cloudflare DNS (Let's Encrypt thường dùng)

            for ($i = 0; $i < $maxAttempts; $i++) {
                // Check với Cloudflare DNS server (1.1.1.1) - Let's Encrypt thường dùng DNS này
                $digCommand = "dig @{$cloudflareDnsServer} +short {$domain} A 2>&1";
                exec($digCommand, $digOutput, $digCode);
                $cloudflareDnsResolved = !empty($digOutput) && !empty(trim(implode('', $digOutput)));
                
                // Also check local DNS
                $dns = dns_get_record($domain, DNS_A);
                
                if ($cloudflareDnsResolved && !empty($dns)) {
                    $resolved = true;
                    Log::info("DNS resolved for {$domain}", [
                        'cloudflare_dns' => trim(implode('', $digOutput)),
                        'local_dns' => $dns,
                        'attempt' => $i + 1
                    ]);
                    break;
                } else {
                    $status = [
                        'cloudflare_dns' => $cloudflareDnsResolved ? trim(implode('', $digOutput)) : 'not resolved',
                        'local_dns' => !empty($dns) ? 'resolved' : 'not resolved',
                        'attempt' => $i + 1
                    ];
                    Log::info("Waiting for DNS to resolve for {$domain}", $status);
                    sleep($delaySeconds);
                }
            }

            if (!$resolved) {
                Log::error("DNS not resolved for {$domain} after {$maxAttempts} attempts", [
                    'max_attempts' => $maxAttempts,
                    'delay_seconds' => $delaySeconds,
                    'dns_server' => $cloudflareDnsServer
                ]);
                return;
            }
            
            // Additional short wait to ensure DNS is fully propagated
            Log::info("DNS resolved on Cloudflare DNS, waiting 3 seconds for full propagation");
            sleep(3);

            // Thử chạy certbot để cấp SSL
            exec($certbotCmd . " 2>&1", $output, $code);
            Log::info("Certbot output for {$domain}", [
                'output' => $output,
                'exit_code' => $code
            ]);

            // Check if SSL config file was created (even if exit code is non-zero)
            // Exit code 120 often indicates BrokenPipeError but certificate might be created successfully
            $sslCreated = file_exists($sslConfPath);
            
            if ($code !== 0 && !$sslCreated) {
                // Only treat as failure if exit code is non-zero AND SSL config file doesn't exist
                Log::error("Certbot failed for {$domain}", [
                    'exit_code' => $code,
                    'output' => $output,
                    'ssl_file_exists' => false
                ]);
                return;
            }

            // If SSL config file exists, treat as success (even with non-zero exit code)
            // This handles cases where certbot succeeds but has broken pipe error (code 120)
            if ($sslCreated) {
                if ($code !== 0) {
                    Log::warning("Certbot completed but with exit code {$code} (possibly broken pipe)", [
                        'domain' => $domain,
                        'exit_code' => $code,
                        'ssl_file_exists' => true
                    ]);
                }
                
                exec($reloadCmd, $reloadOutput, $reloadCode);
                Log::info("Apache reloaded for {$domain}", [
                    'reload_exit_code' => $reloadCode,
                    'output' => $reloadOutput
                ]);
                
                // Enable Cloudflare proxy (orange cloud) after SSL is successfully created
                // $apiToken = getenv('CLOUD_FLARE_API_TOKEN');
                // if ($apiToken) {
                //     $this->enableCloudflareProxy($domain, $apiToken);
                // }
            } else {
                Log::warning("SSL config not found after certbot for {$domain}", [
                    'exit_code' => $code,
                    'output' => $output
                ]);
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
            [$sub, $domain] = $this->extractSubdomainAndDomain($fullDomain, 'microgem.io.vn');
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
