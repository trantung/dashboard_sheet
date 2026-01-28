<?php

namespace App\Http\Controllers;

use App\Models\Temp;
use App\Models\Site;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Service\SetupDefaultService;
use App\Service\DatabaseDefaultService;
use Illuminate\Support\Facades\DB;

class TempController extends Controller
{
    public function __construct(SetupDefaultService $setupDefaultService, DatabaseDefaultService $databaseDefaultService) 
    {
        $this->setupDefaultService = $setupDefaultService;
        $this->databaseDefaultService = $databaseDefaultService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'site_type' => 'required|integer|in:1,2',
        ]);

        try {
            DB::beginTransaction();

            // Get user's first workspace or create one
            $workspace = Auth::user()->workspaces()->first();
            if (!$workspace) {
                $workspace = Workspace::create([
                    'user_id' => Auth::id(),
                    'name' => 'Default Workspace',
                ]);
            }

            // $code = Auth::id() . str_pad(rand(0, 9999999999999999), 16, '0', STR_PAD_LEFT);
            $code = 'tenant_db_' . Auth::id() . '_' . str_pad(rand(0, 9999999999999999), 16, '0', STR_PAD_LEFT);;
            $temp = Temp::create([
                'workspace_id' => $workspace->id,
                'code' => $code,
                'site_type' => $request->site_type,
                'status' => 0,
            ]);

            DB::commit();

            return response()->json([
                'temp_id' => $temp->id,
                'code' => $temp->code,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to create temp: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateGoogleSheet(Request $request, $tempId)
    {
        $request->validate([
            'google_sheet' => 'required|url',
        ]);

        $temp = Temp::findOrFail($tempId);
        
        // Check if user owns this temp
        if ($temp->workspace->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $temp->update([
            'google_sheet' => $request->google_sheet,
        ]);

        return response()->json(['message' => 'Google Sheet updated successfully']);
    }

    public function getGoogleSheetData($tempId)
    {
        $temp = Temp::findOrFail($tempId);
        
        // Check if user owns this temp
        if ($temp->workspace->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!$temp->google_sheet) {
            return response()->json(['error' => 'No Google Sheet URL found'], 400);
        }

        try {
            // Extract spreadsheet ID from URL
            // preg_match('/\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $temp->google_sheet, $matches);
            // if (!$matches) {
            //     return response()->json(['error' => 'Invalid Google Sheets URL'], 400);
            // }

            // $spreadsheetId = $matches[1];

            $spreadsheetId = extractSheetIdFromUrl($temp->google_sheet);

            if (!$spreadsheetId) {
                return response()->json(['error' => 'Invalid Google Sheets URL'], 400);
            }

            $apiKey = env('GOOGLE_SHEETS_API_KEY');

            $response = Http::get("https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}", [
                'key' => $apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $sheets = collect($data['sheets'])->map(function ($sheet) {
                    return $sheet['properties']['title'];
                });

                return response()->json([
                    'sheets' => $sheets,
                    'connected' => true,
                ]);
            }

            return response()->json(['error' => 'Failed to connect to Google Sheets'], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error connecting to Google Sheets: ' . $e->getMessage()], 500);
        }
    }

    public function finish(Request $request, $tempId)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'site_domain' => 'required|string|max:255',
        ]);

        $temp = Temp::findOrFail($tempId);

        // Check if user owns this temp
        if ($temp->workspace->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            DB::beginTransaction();

            // Update temp with site info
            $temp->update([
                'site_name' => $request->site_name,
                'site_domain' => $request->site_domain,
                'status' => 1,
            ]);

            // Create site
            $domain_name = $temp->site_domain . '.' . domain_base();
            $site = Site::create([
                'workspace_id' => $temp->workspace_id,
                'type' => $temp->site_type,
                'name' => $temp->site_name,
                'code' => $temp->code,
                'domain_name' => $domain_name,
                'db_name' => $temp->code,
                'google_sheet' => $temp->google_sheet,
            ]);

            $site_type = BLOG; // defaul blog

            if($temp->site_type){
                $site_type = $temp->site_type;
            }

            if($site_type == BLOG){ // blog
                $projectPath = '/var/www/html/sheetany_blog/dist';
                $portProject = env('BLOG_PORT');
                $path = env('BLOG_DATABASE_MIGRATIONS');
            }

            if($site_type == ECOMERCE){ // ecommerce
                $projectPath = '/var/www/html/sheet_ecommerce/dist';
                $portProject = env('ECOMMERCE_PORT');
                $path = env('ECOMMERCE_DATABASE_MIGRATIONS');
            }

            // Run external service after DB commit
            $addDomainToDns = $this->setupDefaultService->addDomainToDns($domain_name, $projectPath, $portProject);
            $responseAddDomainToDns = $addDomainToDns->getData(true); // Convert JsonResponse to array
            if (!$responseAddDomainToDns['status']) {
                throw new \Exception($responseAddDomainToDns['message'] ?? 'Unknown error');
            }

            //create new database with name $domain_name
            $createDatabase = $this->setupDefaultService->createDatabase($temp->code);
            // $path = 'database/migrations/blog';
            migrateTenantDatabase($temp->code, $path);
            $responseCreateDatabase = $createDatabase->getData(true); // Convert JsonResponse to array
            if (!$responseCreateDatabase['status']) {
                throw new \Exception($responseCreateDatabase['message'] ?? 'Unknown error');
            }

            $spreadsheetId = extractSheetIdFromUrl($temp->google_sheet);
            $dataArrayInput = ['spreadsheet_id' => $spreadsheetId, 'full_domain' => $temp->site_domain, 'sheet_url' => $temp->google_sheet, 'db_name' => $temp->code];
            if($site_type == BLOG) {
                $importFromGoogleSheetByApiKey = $this->databaseDefaultService->importFromGoogleSheetByApiKey($dataArrayInput);
            }
            if($site_type == ECOMERCE) {
                $importFromGoogleSheetByApiKey = $this->databaseDefaultService->importFromGoogleSheetByApiKey($dataArrayInput, ECOMERCE);
            }
            
            
            $responseImportFromGoogleSheetByApiKey = $importFromGoogleSheetByApiKey->getData(true); // Convert JsonResponse to array
            if (!$responseImportFromGoogleSheetByApiKey['status']) {
                throw new \Exception($responseImportFromGoogleSheetByApiKey['message'] ?? 'Unknown error');
            }

            DB::commit(); // Commit before triggering external services
            
            return response()->json([
                'message' => 'Website created successfully',
                'site' => $site,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to create site: ' . $e->getMessage(),
            ], 500);
        }
    }
}
