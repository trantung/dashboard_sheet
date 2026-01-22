<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Models\Temp;
use Illuminate\Support\Facades\Http;
use App\Service\DatabaseDefaultService;
use App\Service\SetupDefaultService;

class ApiTestController extends Controller
{
    public function __construct(DatabaseDefaultService $databaseDefaultService, SetupDefaultService $setupDefaultService) 
    {
        $this->databaseDefaultService = $databaseDefaultService;
        $this->setupDefaultService = $setupDefaultService;
    }

    public function index(Request $request)
    {
        try {
            $apiToken = getenv('CLOUD_FLARE_API_TOKEN');
            $ip = getenv('SERVER_IP_ADDRESS');
            $fullDomain = $request->full_domain;
            [$sub, $domain] = $this->extractSubdomainAndDomain($fullDomain, 'microgem.io.vn');
            $zoneId = $this->getZoneIdByDomain($apiToken, $domain);

            if (!$zoneId) {
                return response()->json([
                    'status' => false,
                    'message' => "Không tìm thấy Zone ID cho {$domain}"
                ]);
            }

            $response = $this->createSubdomainOnCloudflare($apiToken, $zoneId, $sub, $domain, $ip);
            $projectPath = '/var/www/html/sheetany_blog/dist';
            $vhostResult = $this->addApacheVirtualHost($fullDomain, $projectPath);
            $path = 'database/migrations/blog';
            $this->createDatabase($sub);
            migrateTenantDatabase($sub, $path);
            return response()->json([
                'status' => true,
                'message' => 'Tạo subdomain và VirtualHost thành công.',
                'data' => [
                    'cloudflare_response' => json_decode($response, true),
                    'vhost_result' => $vhostResult
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Lỗi khi tạo subdomain: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ]);
        }
    }

    public function extractSubdomainAndDomain($fullDomain, $domainRoot = 'microgem.io.vn') {
        if (!str_ends_with($fullDomain, $domainRoot)) {
            throw new Exception("Tên miền không khớp với domain chính ({$domainRoot})");
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

    public function addApacheVirtualHost($domain, $projectPath)
    {
        $confPath = "/etc/apache2/sites-available/{$domain}.conf";

        // $vhostConfig = <<<EOL
        //     <VirtualHost *:80>
        //         ServerName {$domain}
        //         DocumentRoot {$projectPath}

        //         <Directory {$projectPath}>
        //             Options Indexes FollowSymLinks
        //             AllowOverride All
        //             Require all granted
        //         </Directory>

        //         ErrorLog \${APACHE_LOG_DIR}/{$domain}_error.log
        //         CustomLog \${APACHE_LOG_DIR}/{$domain}_access.log combined
        //     </VirtualHost>
        //     EOL;

        $vhostConfig = <<<EOL
            <VirtualHost *:80>
                ServerName {$domain}

                ErrorLog \${APACHE_LOG_DIR}/{$domain}_error.log
                CustomLog \${APACHE_LOG_DIR}/{$domain}_access.log combined

                ProxyPreserveHost On
                ProxyRequests Off

                # Proxy for HTTP
                ProxyPass / http://localhost:3001/
                ProxyPassReverse / http://localhost:3001/

                # Proxy for WebSocket
                RewriteEngine on
                RewriteCond %{HTTP:Upgrade} =websocket [NC]
                RewriteRule /(.*) ws://localhost:3001/\$1 [P,L]
            </VirtualHost>
            EOL;

        $command = "echo " . escapeshellarg($vhostConfig) . " | sudo tee {$confPath} > /dev/null";
        exec($command, $output1, $code1);

        if ($code1 !== 0) {
            return [
                'status' => false,
                'message' => "Không thể tạo file cấu hình (có thể do thiếu quyền sudo)."
            ];
        }

        exec("sudo a2ensite {$domain}.conf", $out1, $c1);
        // exec("sudo a2enmod ssl", $out2);
        exec("sudo systemctl reload apache2", $out3, $c2);

        if ($c1 === 0 && $c2 === 0) {
            return [
                'status' => true,
                'message' => "Đã tạo và kích hoạt VirtualHost cho {$domain}.",
                'output' => [$out1, $out3]
            ];
        }

        return [
            'status' => false,
            'message' => 'Có lỗi xảy ra khi kích hoạt VirtualHost.',
            'output' => [$out1 ?? '', $out3 ?? '']
        ];
    }

    public function enableSSL($domain)
    {
        try {
            $cmd = "sudo certbot --apache --non-interactive --agree-tos -m your-email@example.com -d {$domain}";
            exec($cmd, $output, $return_var);

            return [
                'status' => $return_var === 0,
                'message' => $return_var === 0
                    ? "SSL đã được cài đặt thành công cho {$domain}"
                    : "Cài đặt SSL thất bại. Vui lòng kiểm tra log Certbot.",
                'output' => $output
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => 'Lỗi khi cài SSL: ' . $e->getMessage()
            ];
        }
    }

    //delete domain
    public function deleteDomain(Request $request)
    {
        $apiToken = getenv('CLOUD_FLARE_API_TOKEN');
        $ip = getenv('SERVER_IP_ADDRESS');
        $fullDomain = $request->full_domain;
        [$sub, $domain] = $this->extractSubdomainAndDomain($fullDomain, 'microgem.io.vn');
        $zoneId = $this->getZoneIdByDomain($apiToken, $domain);
        $check = $this->deleteApacheVirtualHostAndCloudflare($fullDomain, $apiToken, $zoneId);
        dd($check);
    }

    public function deleteApacheVirtualHostAndCloudflare($domain, $cloudflareToken, $zoneId)
    {
        $confFile = "{$domain}.conf";
        $confPath = "/etc/apache2/sites-available/{$confFile}";
        $enabledPath = "/etc/apache2/sites-enabled/{$confFile}";
        exec("sudo a2dissite {$confFile}", $out1, $code1);
        if (file_exists($enabledPath)) {
            exec("sudo rm -f {$enabledPath}", $outSymlink, $codeSymlink);
        } else {
            $codeSymlink = 0;
        }
        if (file_exists($confPath)) {
            exec("sudo rm -f {$confPath}", $out2, $code2);
        } else {
            $code2 = 0;
        }
        exec("sudo systemctl reload apache2", $outReload, $codeReload);
        $recordId = $this->getCloudflareDNSRecordId($domain, $cloudflareToken, $zoneId);
        $deleted = false;
        if ($recordId) {
            $deleted = $this->deleteCloudflareDNSRecord($recordId, $cloudflareToken, $zoneId);
        }
        return [
            'apache' => $code1 === 0 && $code2 === 0 && $codeSymlink === 0,
            'cloudflare_deleted' => $deleted,
            'messages' => [
                'apache' => ($code1 === 0 && $code2 === 0 && $codeSymlink === 0) ? "Đã xóa VirtualHost cho {$domain}." : "Lỗi khi xóa VirtualHost.",
                'cloudflare' => $deleted ? "Đã xóa DNS trên Cloudflare." : "Không thể tìm hoặc xóa DNS record trên Cloudflare."
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
            $command = "mysql -u root -p'".env('DB_PASSWORD')."' -e 'CREATE DATABASE IF NOT EXISTS {$databaseName};'";
            exec($command, $output, $returnVar);
            if ($returnVar !== 0) {
                throw new Exception("Lỗi khi tạo cơ sở dữ liệu: " . implode("\n", $output));
            }

            return response()->json([
                'status' => true,
                'message' => "Cơ sở dữ liệu {$databaseName} đã được tạo thành công."
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ]);
        }
    }

    public function importData(Request $request)
    {
        // $tempId = $request->temp_id;
        return $this->databaseDefaultService->importFromGoogleSheetByApiKey($request);
    }

    /**
     * Test API to verify sudo password
     * POST /api/domain/test/sudo-password
     */
    public function testSudoPassword(Request $request)
    {
        $rootPassword = getenv('SERVER_PASSWORD') ?: '0SVXGJBdQjPilduP5mwi';
        $fileName = "my-site.conf";
        $fullPath = "/etc/apache2/sites-available/" . $fileName;
    
        $configContent = "<VirtualHost *:80>\n    ServerName test.com\n    DocumentRoot /var/www/html\n</VirtualHost>";
    
        // Chú ý: escapeshellarg sẽ bao bọc chuỗi, nhưng ta cần cẩn thận với mật khẩu
        $escapedPath = escapeshellarg($fullPath);
        $escapedContent = escapeshellarg($configContent);
    
        // Sử dụng cơ chế Pipe đơn giản nhất: echo "password" | sudo -S ...
        // Thêm \n vào cuối password là bắt buộc đối với sudo -S
        $command = "echo " . escapeshellarg($rootPassword) . " | sudo -S tee $escapedPath <<EOF
    $configContent
    EOF
    2>&1";
    
        exec($command, $output, $returnCode);
    
        if ($returnCode !== 0) {
            echo "Thất bại! Mã lỗi: $returnCode <br>";
            echo "Chi tiết lỗi từ hệ thống: <pre>";
            print_r($output);
            echo "</pre>";
            
            // Dòng này để debug xem user thực sự đang chạy script là ai
            echo "User thực tế: " . shell_exec('whoami');
        } else {
            echo "Thành công! Đã tạo file: " . $fullPath;
        }
    }
    /**
     * Test API to create Apache VirtualHost config file
     * POST /api/domain/test/vhost
     * Body: { "domain": "test.microgem.io.vn", "project_path": "/var/www/html/test", "port_project": 3001 }
     */
    public function testVirtualHost(Request $request)
    {
        try {
            $request->validate([
                'domain' => 'required|string',
                'project_path' => 'required|string',
                'port_project' => 'integer|min:1|max:65535'
            ]);

            $domain = $request->input('domain');
            $projectPath = $request->input('project_path');
            $portProject = $request->input('port_project', 3001);

            Log::info("Testing VirtualHost creation", [
                'domain' => $domain,
                'project_path' => $projectPath,
                'port_project' => $portProject
            ]);

            // Call the service method
            $result = $this->setupDefaultService->addDomainToDns($domain, $projectPath, $portProject);

            // Check if file exists
            $confPath = "/etc/apache2/sites-available/{$domain}.conf";
            $fileExists = file_exists($confPath);
            $fileContent = $fileExists ? file_get_contents($confPath) : null;
            dd($result);
            return response()->json([
                'status' => $result['status'] ?? false,
                'message' => $result['message'] ?? 'Unknown result',
                'data' => [
                    'result' => $result,
                    'file_exists' => $fileExists,
                    'file_path' => $confPath,
                    'file_content' => $fileContent,
                    'file_size' => $fileExists ? filesize($confPath) : 0,
                    'file_permissions' => $fileExists ? substr(sprintf('%o', fileperms($confPath)), -4) : null,
                ],
                'debug' => [
                    'password_set' => !empty(getenv('SERVER_PASSWORD')),
                    'password_length' => strlen(getenv('SERVER_PASSWORD') ?: ''),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("Error in testVirtualHost: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'error' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            ], 500);
        }
    }

}
