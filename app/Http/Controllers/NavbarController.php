<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Site;
use Illuminate\Support\Facades\Auth;
use App\Service\NavbarService;

class NavbarController extends Controller
{
    public function __construct(NavbarService $navbarService) 
    {
        $this->navbarService = $navbarService;
    }

    public function index(Site $site)
    {
        // Mock data - replace with actual database query
        if ($site->workspace->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $domain_name = $site->domain_name;
        $db_name = str_replace('.' . DOMAIN_BASE, '', $domain_name);
        if(!empty($site->db_name)){
            $db_name = $site->db_name;
        }

        $connectionName = setupTenantConnection($db_name);
        $navbarItems = $this->navbarService->getData($connectionName);

        // $navbarItems = [
        //     [
        //         'id' => 1,
        //         'title' => 'Home',
        //         'url' => 'https://example.com',
        //         'position' => 'header',
        //         'target' => 'same',
        //         'order' => 1,
        //         'created_at' => now(),
        //         'updated_at' => now()
        //     ],
        //     [
        //         'id' => 2,
        //         'title' => 'About',
        //         'url' => 'https://example.com/about',
        //         'position' => 'header',
        //         'target' => 'same',
        //         'order' => 2,
        //         'created_at' => now(),
        //         'updated_at' => now()
        //     ]
        // ];

        return response()->json($navbarItems);
    }

    public function store(Request $request, Site $site)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'link' => 'nullable|url',
            'position' => 'required|in:1,2',
            'target' => 'required|in:1,2'
        ]);

        if ($site->workspace->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $domain_name = $site->domain_name;
        $db_name = str_replace('.' . DOMAIN_BASE, '', $domain_name);
        if(!empty($site->db_name)){
            $db_name = $site->db_name;
        }

        // Mock creation - replace with actual database insertion
        $navbarItem = [
            'title' => $validated['title'],
            'link' => $validated['link'] ?? null,
            'position' => $validated['position'],
            'target' => $validated['target'],
            'order' => 999,
            'created_at' => now(),
            'updated_at' => now()
        ];

        $connectionName = setupTenantConnection($db_name);

        $res = $this->navbarService->storeData($connectionName, $navbarItem);

        return response()->json($navbarItem, 201);
    }

    public function update(Request $request, Site $site, $id)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'link' => 'nullable|url',
            'position' => 'required|in:1,2',
            'target' => 'required|in:1,2'
        ]);

        if ($site->workspace->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $domain_name = $site->domain_name;
        $db_name = str_replace('.' . DOMAIN_BASE, '', $domain_name);
        if(!empty($site->db_name)){
            $db_name = $site->db_name;
        }

        // Mock update - replace with actual database update
        $navbarItem = [
            'id' => $id,
            'title' => $validated['title'],
            'link' => $validated['link'] ?? null,
            'position' => $validated['position'],
            'target' => $validated['target'],
            'order' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ];

        $connectionName = setupTenantConnection($db_name);

        $res = $this->navbarService->updateData($connectionName, $navbarItem, $id);

        return response()->json($navbarItem);
    }

    public function destroy(Site $site, $id)
    {
        if ($site->workspace->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $domain_name = $site->domain_name;
        $db_name = str_replace('.' . DOMAIN_BASE, '', $domain_name);
        if(!empty($site->db_name)){
            $db_name = $site->db_name;
        }

        $connectionName = setupTenantConnection($db_name);

        $res = $this->navbarService->deleteData($connectionName, $id);

        // Mock deletion - replace with actual database deletion
        return response()->json(['message' => 'Navbar item deleted successfully']);
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.order' => 'required|integer'
        ]);

        // Mock reorder - replace with actual database update
        foreach ($validated['items'] as $item) {
            // Update order in database
            // NavbarItem::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['message' => 'Items reordered successfully']);
    }
}
