<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Site;
use Illuminate\Support\Facades\Auth;
use App\Service\EmailService;

class EmailController extends Controller
{
    protected $emailService;

    public function __construct(EmailService $emailService) 
    {
        $this->emailService = $emailService;
    }

    protected function getTenantConnection(Site $site)
    {
        if ($site->workspace->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $domain_name = $site->domain_name;
        $db_name = str_replace('.' . domain_base(), '', $domain_name);

        if (!empty($site->db_name)) {
            $db_name = $site->db_name;
        }

        return setupTenantConnection($db_name);
    }

    public function index(Request $request, Site $site): JsonResponse
    {
        try {
            $connection = $this->getTenantConnection($site);
            $emails = $this->emailService->getData($connection, $request);

            return response()->json([
                'success' => true,
                'data' => $emails->items(),
                'pagination' => [
                    'current_page' => $emails->currentPage(),
                    'last_page' => $emails->lastPage(),
                    'per_page' => $emails->perPage(),
                    'total' => $emails->total(),
                    'from' => $emails->firstItem(),
                    'to' => $emails->lastItem(),
                    'has_more_pages' => $emails->hasMorePages(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch emails',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function stats(Site $site): JsonResponse
    {
        try {
            $connection = $this->getTenantConnection($site);
            $stats = $this->emailService->getStats($connection);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
