<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class ApiTestController extends Controller
{
    public function index(Request $request)
    {
        $apiToken = getenv('CLOUD_FLARE_API_TOKEN');
        $ip = getenv('SERVER_IP_ADDRESS');                            // <-- IP mà subdomain trỏ đến
        // ✅ Người dùng nhập vào tên đầy đủ subdomain và IP muốn trỏ đến
        // $fullDomain = 'tenant123.ieltscheckmate.edu.vn';
        $fullDomain = $request->full_domain;
        [$sub, $domain] = $this->extractSubdomainAndDomain($fullDomain, 'ieltscheckmate.edu.vn');
        $zoneId = $this->getZoneIdByDomain($apiToken, $domain);

        if (!$zoneId) {
            throw new Exception("Không tìm thấy Zone ID cho $domain");
        }

        $response = $this->createSubdomainOnCloudflare($apiToken, $zoneId, $sub, $domain, $ip);
        $projectPath = '/var/www/html/sheetany_blog/dist';
        $this->addApacheVirtualHost($fullDomain, $projectPath);
        // $this->enableSSL($fullDomain);
        dd($response);
        return response()->json($response);
    }


    // B1: Tách subdomain và domain chính
    public function extractSubdomainAndDomain($fullDomain, $domainRoot = 'ieltscheckmate.edu.vn') {
        if (!str_ends_with($fullDomain, $domainRoot)) {
            throw new Exception("Tên miền không khớp với domain chính ($domainRoot)");
        }

        $subdomain = str_replace(".$domainRoot", '', $fullDomain);
        return [$subdomain, $domainRoot];
    }

    // B2: Tìm Zone ID cho domain gốc
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
        if (isset($data['success']) && $data['success'] === true && count($data['result']) > 0) {
            return $data['result'][0]['id'];
        }

        return null;
    }

    // B3: Tạo subdomain bằng DNS record
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
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $response;
    }

    public function addApacheVirtualHost($domain, $projectPath)
    {
        $confPath = "/etc/apache2/sites-available/{$domain}.conf";

        $vhostConfig = <<<EOL
            <VirtualHost *:80>
                ServerName {$domain}
                DocumentRoot {$projectPath}

                <Directory {$projectPath}>
                    Options Indexes FollowSymLinks
                    AllowOverride All
                    Require all granted
                </Directory>

                ErrorLog \${APACHE_LOG_DIR}/{$domain}_error.log
                CustomLog \${APACHE_LOG_DIR}/{$domain}_access.log combined
            </VirtualHost>
            EOL;

        // Ghi file với quyền sudo
        $command = "echo " . escapeshellarg($vhostConfig) . " | sudo tee {$confPath} > /dev/null";
        exec($command, $output, $return_var);

        if ($return_var !== 0) {
            echo "❌ Không thể tạo file cấu hình (có thể do thiếu quyền sudo).\n";
            return;
        }

        echo "✅ Đã tạo file cấu hình: $confPath\n";

        // Enable site + SSL module
        exec("sudo a2ensite {$domain}.conf", $output1, $code1);
        exec("sudo a2enmod ssl", $sslOut);
        exec("sudo systemctl reload apache2", $output2, $code2);

        echo implode("\n", $output1) . "\n";
        echo implode("\n", $output2) . "\n";

        if ($code1 === 0 && $code2 === 0) {
            echo "✅ Domain {$domain} đã được kích hoạt và trỏ đến {$projectPath}\n";
        } else {
            echo "❌ Có lỗi xảy ra khi kích hoạt VirtualHost.\n";
        }
    }

    public function enableSSL($domain)
    {
        $cmd = "sudo certbot --apache --non-interactive --agree-tos -m your-email@example.com -d {$domain}";
        exec($cmd, $output, $return_var);

        if ($return_var === 0) {
            echo "✅ SSL đã được cài đặt thành công cho {$domain}\n";
        } else {
            echo "❌ Cài đặt SSL thất bại. Vui lòng kiểm tra log Certbot.\n";
            echo implode("\n", $output) . "\n";
        }
    }

}
