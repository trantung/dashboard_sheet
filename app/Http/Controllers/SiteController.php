<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log; // Để ghi log lỗi
use Illuminate\Support\Facades\Http; // Để gửi HTTP requests đến Google Sheets API
use Carbon\Carbon; // Để làm việc với thời gian
use App\Service\SiteService;
use App\Service\SetupDefaultService;
use App\Service\DatabaseDefaultService;
use Illuminate\Support\Facades\DB;

class SiteController extends Controller
{

    public function __construct(SetupDefaultService $setupDefaultService, SiteService $siteService, DatabaseDefaultService $databaseDefaultService) 
    {
        $this->setupDefaultService = $setupDefaultService;
        $this->siteService = $siteService;
        $this->databaseDefaultService = $databaseDefaultService;
    }

    public function index()
    {
        $user = Auth::user();
        $workspaces = $user->workspaces()->with('sites')->get();
        
        $sites = $workspaces->flatMap(function ($workspace) {
            return $workspace->sites;
        });

        return response()->json($sites);
    }

    public function show(Site $site)
    {
        // Check if user owns this site through workspace
        if ($site->workspace->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $domain_name = $site->domain_name;
        $db_name = str_replace('.' . domain_base(), '', $domain_name);
        if(!empty($site->db_name)){
            $db_name = $site->db_name;
        }
        $connectionName = setupTenantConnection($db_name);
        $siteType = $site->type ?? BLOG;
        $sheetInformationData = $this->siteService->getInformationData($connectionName);
        $sheetContentData = $this->siteService->getContentData($connectionName, $siteType);

        $contentHeaders = [
            "Title",
            "Slug",
            "Excerpt",
            "Thumbnail",
            "Categories",
            "Author",
            "Content",
            "Published Date",
            "Status"
        ];

        if ($siteType == ECOMERCE) {
            $contentHeaders = [
                "Name",
                "SKU",
                "Inventory",
                "Price",
                "Old Price",
                "Link",
                "Size",
                "Color",
                "Material",
                "Description",
                "Rating",
                "Best Selling",
                "New Arrival",
                "Images",
                "Categories"
            ];
        }

        // Mock data for sheets
        $mockSheetsData = [
            [
                "sheet_id" => BLOG_SHEET_INFORMATION_TAB_ID,
                "sheet_data" => $sheetInformationData,
                "sheet_headers" => [
                    "Property",
                    "Value"
                ],
                "sheet_name" => "Information",
            ],
            [
                "sheet_id" => BLOG_SHEET_CONTENT_TAB_ID,
                "sheet_data" => $sheetContentData,
                "sheet_headers" => $contentHeaders,
                "sheet_name" => "Content",
            ]
        ];

        // Convert the Site model to an array
        $siteData = $site->toArray();

        $siteData['information'] = [
            "sheet_name" => "Information",
            "sheet_id" => BLOG_SHEET_INFORMATION_TAB_ID
        ];

        $siteData['content'] = [
            "sheet_name" => "Content",
            "sheet_id" => BLOG_SHEET_CONTENT_TAB_ID
        ];

        // Tab custom code
        $siteData['custom_js_position'] = 'bodyOpen';
        $siteData['custom_js'] = "console.log('hello')";
        $siteData['custom_css'] = ".bg-primary\\/10 {\n    background-color: rgba(15, 157, 96, .1);\n}";

        // Tab General Settings
        $siteData = $this->siteService->formatSite($connectionName, $siteData, $siteType);
        // $siteData['dark_mode'] = true; // Show dark mode
        // $siteData['hide_header'] = true; // Hide header
        // $siteData['hide_footer'] = true; // Hide footer
        // $siteData['disable_hero'] = false; // Hide hero
        // $siteData['collect_email'] = false; // Show collected emails
        // $siteData['about_us'] = false; // Show the About Us page
        // $siteData['disable_auto_sync'] = false; // Disable auto-sync
        // $siteData['feedback_form'] = false; // Show feedback form
        // $siteData['text_center'] = false; // Text center
        // $siteData['small_hero'] = true; // Font size
        // $siteData['grid_content'] = 2; // Grid content
        // $siteData['pagination_size'] = 3; // Pagination Size
        // $siteData['font_family'] = "Poppins"; // Font Family
        // $siteData['published'] = true; // Publish website

        // Tab Information and Content Sheets
        $siteData['sheets'] = $mockSheetsData;

        return response()->json($siteData);
    }

    public function update(Request $request, Site $site)
    {
        // Check if user owns this site through workspace
        if ($site->workspace->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        dd($request->all(), $site);
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'domain_name' => 'sometimes|string|max:255',
            'google_sheet' => 'sometimes|url',
        ]);

        $site->update($request->only(['name', 'domain_name', 'google_sheet']));

        return response()->json(['success' => true, 'data' => $site]);
    }

    public function destroy(Site $site)
    {
        // Check if user owns this site through workspace
        if ($site->workspace->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $check = $this->setupDefaultService->deleteDomain(['full_domain' => $site->domain_name]);
        //xóa database dựa vào $site->db_name
        $db_name = $site->db_name;
        if($db_name){
            $this->databaseDefaultService->deleteDatabase($db_name);
        }
        Site::destroy($site->id);
        return response()->json(['success' => true, 'message' => 'Site deleted successfully']);
    }

    /**
     * Cập nhật subdomain cho một trang web cụ thể.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Site  $site
     * @return \Illuminate\Http\JsonResponse
     */
    // public function updateSubdomain(Request $request, Site $site)
    // {
    //     try {
    //         // 1. Validate dữ liệu đầu vào
    //         $validatedData = $request->validate([
    //             'subdomain' => [
    //                 'required',
    //                 'string',
    //                 'min:3',
    //                 'max:60',
    //                 'regex:/^[a-zA-Z0-9-]+$/', // Chỉ cho phép chữ, số và dấu gạch ngang
    //                 // Custom validation rule: Kiểm tra xem subdomain đã tồn tại chưa
    //                 // Ví dụ: tránh trùng lặp subdomain trên nền tảng của bạn
    //                 function ($attribute, $value, $fail) use ($site) {
    //                     $fullDomain = $value . '.' . domain_base();
    //                     // Giả định 'domain_name' là cột trong bảng 'sites'
    //                     if (Site::where('domain_name', $fullDomain)->where('id', '!=', $site->id)->exists()) {
    //                         $fail("The subdomain '{$value}' is already taken. Please choose another one.");
    //                     }
    //                 },
    //             ],
    //         ]);

    //         $newSubdomain = $validatedData['subdomain'];
    //         $baseDomain = '.' . domain_base(); // Đảm bảo base domain là cố định
    //         $old_domain = $site->domain_name;
    //         // 2. Tạo domain_name đầy đủ mới
    //         $newFullDomain = $newSubdomain . $baseDomain;

    //         // 3. Cập nhật bản ghi Site trong database
    //         $site->domain_name = $newFullDomain;
    //         $site->save();

    //         //Xóa DNS + apache
    //         $this->setupDefaultService->deleteDomain(['full_domain' => $old_domain]);
    //         //create new dns+apache
    //         $addDomainToDns = $this->setupDefaultService->addDomainToDns($newFullDomain);
    //         // dd($addDomainToDns);
    //         // 4. Trả về phản hồi thành công
    //         return response()->json([
    //             'message' => 'Subdomain updated successfully.',
    //             'domain_name' => $site->domain_name, // Trả về domain_name đã cập nhật
    //             // Bạn có thể trả về các thông tin site khác nếu cần
    //         ], 200);

    //     } catch (ValidationException $e) {
    //         // Xử lý lỗi validate
    //         Log::error('Subdomain validation failed:', ['errors' => $e->errors(), 'site_id' => $site->id]);
    //         return response()->json([
    //             'message' => 'The given data was invalid.',
    //             'errors' => $e->errors(),
    //         ], 422); // Unprocessable Entity
    //     } catch (\Exception $e) {
    //         // Xử lý các lỗi khác
    //         Log::error('Failed to update subdomain:', ['error' => $e->getMessage(), 'site_id' => $site->id]);
    //         return response()->json([
    //             'message' => 'An error occurred while updating the subdomain. Please try again later.',
    //             'error' => $e->getMessage(),
    //         ], 500); // Internal Server Error
    //     }
    // }

    public function updateSubdomain(Request $request, Site $site)
    {
        DB::beginTransaction();

        try {
            // 1. Validate dữ liệu đầu vào
            $validatedData = $request->validate([
                'subdomain' => [
                    'required',
                    'string',
                    'min:3',
                    'max:60',
                    'regex:/^[a-zA-Z0-9-]+$/',
                    function ($attribute, $value, $fail) use ($site) {
                        $fullDomain = $value . '.' . domain_base();
                        if (Site::where('domain_name', $fullDomain)->where('id', '!=', $site->id)->exists()) {
                            $fail("The subdomain '{$value}' is already taken. Please choose another one.");
                        }
                    },
                ],
            ]);

            $newSubdomain = $validatedData['subdomain'];
            $baseDomain = '.' . domain_base();
            $oldDomain = $site->domain_name;
            $newFullDomain = $newSubdomain . $baseDomain;

            // 2. Cập nhật domain mới vào DB
            $site->domain_name = $newFullDomain;
            $site->save();

            // 3. Xóa domain cũ khỏi Cloudflare + Apache
            $deleteResult = $this->setupDefaultService->deleteDomain(['full_domain' => $oldDomain]);
            $responseDeleteDomain = $deleteResult->getData(true);

            if (!($responseDeleteDomain['status'] ?? false)) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to delete domain.',
                    'details' => $responseDeleteDomain['message'] ?? null
                ], 500);
            }

            // 4. Thêm domain mới vào Cloudflare + Apache
            $addResult = $this->setupDefaultService->addDomainToDns($newFullDomain);
            $addData = $addResult->getData(true); // Lấy mảng từ JsonResponse

            if (!($addData['status'] ?? false)) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to create new domain.',
                    'details' => $addData['message'] ?? null
                ], 500);
            }

            DB::commit();

            // 5. Trả về kết quả thành công
            return response()->json([
                'status' => true,
                'message' => 'Subdomain updated successfully.',
                'domain_name' => $site->domain_name,
            ], 200);

        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error('Subdomain validation failed:', ['errors' => $e->errors(), 'site_id' => $site->id]);
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update subdomain:', ['error' => $e->getMessage(), 'site_id' => $site->id]);
            return response()->json([
                'message' => 'An error occurred while updating the subdomain. Please try again later.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Đồng bộ dữ liệu từ Google Sheet của một trang web.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Site  $site
     * @return \Illuminate\Http\JsonResponse
     */
    // public function syncSheets(Request $request, Site $site)
    // {
    //     try {
    //         // Kiểm tra xem site có liên kết Google Sheet không
    //         if (empty($site->google_sheet)) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'This website does not have a Google Sheet linked.'
    //             ], 400); // Bad Request
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Google Sheet synced successfully!',
    //             'synced_at' => $site->updated_at->toDateTimeString(), // Trả về thời gian đồng bộ
    //         ], 200);

    //     } catch (\Exception $e) {
    //         // Xử lý các lỗi khác
    //         Log::error('Error syncing Google Sheet:', ['error' => $e->getMessage(), 'site_id' => $site->id, 'trace' => $e->getTraceAsString()]);
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'An error occurred during sync. Please try again later.',
    //             'error' => $e->getMessage(),
    //         ], 500); // Internal Server Error
    //     }
    // }

    /**
     * Sync data from Google Sheets to database
     * Truncates and re-inserts data for specific tables: product_category, products, categories, site_informations
     */
    public function syncSheets(Request $request, Site $site)
    {
        try {
            // Check if user owns this site through workspace
            if ($site->workspace->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Check if site has Google Sheet linked
            if (empty($site->google_sheet)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This website does not have a Google Sheet linked.'
                ], 400);
            }

            // $domain_name = $site->domain_name;
            // $db_name = str_replace('.' . domain_base(), '', $domain_name);
            // if(!empty($site->db_name)){
            //     $db_name = $site->db_name;
            // }

            $db_name = $site->db_name;

            if(!$db_name){
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid DB name for Google Sheets sync'
                ], 400);
            }

            $spreadsheetId = extractSheetIdFromUrl($site->google_sheet);

            if (!$spreadsheetId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Google Sheets URL'
                ], 400);
            }

            $siteType = $site->type ?? BLOG;

            // Truncate specific tables before syncing
            $this->truncateTables($db_name);

            // Use DatabaseDefaultService to import data from Google Sheets
            $importResult = $this->databaseDefaultService->importFromGoogleSheetByApiKey([
                'spreadsheet_id' => $spreadsheetId,
                'full_domain' => $site->domain_name,
                'sheet_url' => $site->google_sheet,
                'db_name' => $db_name
            ], $siteType);

            $responseImport = $importResult->getData(true);
            
            if (!$responseImport['status']) {
                return response()->json([
                    'success' => false,
                    'message' => $responseImport['message'] ?? 'Failed to import data from Google Sheets'
                ], 500);
            }

            // Update site's updated_at timestamp
            $site->touch();

            return response()->json([
                'success' => true,
                'message' => 'Data synced from Google Sheet to database successfully!',
                'synced_at' => $site->updated_at->toDateTimeString(),
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error syncing from Google Sheet to database:', [
                'error' => $e->getMessage(), 
                'site_id' => $site->id, 
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during sync. Please try again later.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Truncate specific tables before syncing
     */
    private function truncateTables($db_name)
    {
        $connectionName = setupTenantConnection($db_name);
        
        // Truncate tables in correct order (considering foreign key constraints)
        DB::connection($connectionName)->table('product_category')->truncate();
        DB::connection($connectionName)->table('products')->truncate();
        DB::connection($connectionName)->table('categories')->truncate();
        DB::connection($connectionName)->table('site_informations')->truncate();
        
        Log::info('Truncated tables for sync', ['db_name' => $db_name]);
    }

    public function generateTOCFromHtml($html)
    {
        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

        $xpath = new \DOMXPath($doc);
        $headings = $xpath->query('//h1 | //h2 | //h3');

        $toc = '<div class="table-of-contents"><strong>TABLE OF CONTENTS</strong><ul>';
        $count = 0;

        foreach ($headings as $heading) {
            $id = 'toc_' . $count++;
            $heading->setAttribute('id', $id);
            $text = $heading->textContent;
            $toc .= "<li><a href='#$id'>{$text}</a></li>";
        }

        $toc .= '</ul></div>';

        $body = $doc->getElementsByTagName('body')->item(0);
        $newHtml = '';
        foreach ($body->childNodes as $child) {
            $newHtml .= $doc->saveHTML($child);
        }

        return [
            'toc' => $toc,
            'html' => $newHtml,
        ];
    }

    public function showGoogleDocHtml(Request $request, Site $site)
    {
        // dd(11);
        $documentId = '1d3y2NDIjW2FSMUyijLIZII2ijVsXSnAo_liZfP8XOVY';
        $exportUrl = "https://docs.google.com/feeds/download/documents/export/Export?id={$documentId}&exportFormat=html";

        $client = new \GuzzleHttp\Client();

        // Nếu file là public thì không cần auth
        $response = $client->get($exportUrl);

        $html = (string) $response->getBody();
        $result = $this->generateTOCFromHtml($html);

        return view('test', [
            'toc' => $result['toc'],
            'html' => $result['html'],
        ]);
        // dd($html);
        // return view('test', compact('html'));
    }

    public function settingUpdate(Request $request, Site $site)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'domain_name' => 'sometimes|string|max:255',
            'google_sheet' => 'sometimes|url',
        ]);
        if ($site->workspace->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $domain_name = $site->domain_name;
        $db_name = str_replace('.' . domain_base(), '', $domain_name);
        if(!empty($site->db_name)){
            $db_name = $site->db_name;
        }
        
        $connectionName = setupTenantConnection($db_name);
        $siteType = $site->type ?? BLOG;
        $res = $this->siteService->settingUpdate($connectionName, $request->all(), $siteType);
        return response()->json(['success' => true, 'data' => $site]);
    }

}
