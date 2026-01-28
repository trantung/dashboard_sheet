<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

define('BLOG', 1);
define('ECOMERCE', 2);

define('CATEGORY_SHEET_NAME', 'Categories');
define('PRODUCT_CONTENT', 'Content');
define('PRODUCT_PUBLISHED_DATE', 'Published Date');
// define('DOMAIN_BASE', 'microgem.io.vn');

define('BLOG_SHEET_INFORMATION_TAB_ID', 0);
define('BLOG_SHEET_CONTENT_TAB_ID', 1);


if (!function_exists('format_date')) {
    function format_date($date)
    {
        return \Carbon\Carbon::parse($date)->format('d/m/Y H:i:s');
    }
}

if (!function_exists('current_user_name')) {
    function current_user_name()
    {
        return Auth::check() ? Auth::user()->name : 'Guest';
    }
}

/**
 * Run migration for a tenant with given db name and migration path.
 *
 * @param string $dbName name database tenant
 * @param string $path   Relative path from base_path(), example: 'database/migrations/tenant/type_1'
 * @return void
 */
if (!function_exists('migrateTenantDatabase')) {
    function migrateTenantDatabase(string $dbName, string $path)
    {
        $connectionName = setupTenantConnection($dbName);
        Artisan::call('migrate', [
            '--database' => $connectionName,
            '--path'     => $path,
            '--force'    => true,
        ]);
    }
}

/**
 * Set up a tenant database connection based on the domain (which maps to the database name).
 *
 * @param string $domainSite The domain name or database name (e.g., tenant_abc)
 * @return string The connection name (default is 'tenant')
 */
if (!function_exists('setupTenantConnection')) {
    function setupTenantConnection(string $dbName)
    {
        $connectionName = 'tenant';

         // Set the configuration for the tenant connection
        Config::set("database.connections.$connectionName", [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', '127.0.0.1'),
            'port'      => env('DB_PORT', 3306),
            'database'  => $dbName,
            'username'  => env('DB_USERNAME'),
            'password'  => env('DB_PASSWORD'),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
        ]);

        // Clear the previous connection (if any) and reconnect
        DB::purge($connectionName);
        DB::reconnect($connectionName);

        return $connectionName;
    }
}

/**
 * Helper function to extract Google Sheet ID from URL.
 * @param string $url
 * @return string|null
 */
if (!function_exists('extractSheetIdFromUrl')) {
    function extractSheetIdFromUrl($url)
    {
        $pattern = '/spreadsheets\/d\/([a-zA-Z0-9_-]+)/';
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
        return null;
    }
}

if (!function_exists('domain_base')) {
    function domain_base() {
        return ltrim(config('app.domain_base'), '.');
    }
}