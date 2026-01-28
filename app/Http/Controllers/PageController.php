<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Site;
use Illuminate\Support\Facades\Auth;
use App\Service\PageService;

class PageController extends Controller
{
    public function __construct(PageService $pageService) 
    {
        $this->pageService = $pageService;
    }

    public function index(Request $request, Site $site)
    {
        // Mock data for pages
        if ($site->workspace->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $domain_name = $site->domain_name;
        $db_name = str_replace('.' . domain_base(), '', $domain_name);
        if(!empty($site->db_name)){
            $db_name = $site->db_name;
        }

        $connectionName = setupTenantConnection($db_name);
        $pages = $this->pageService->getData($connectionName);

        // $pages = [
        //     [
        //         'id' => 1,
        //         'title' => 'Home Page',
        //         'slug' => 'home',
        //         'content_type' => 'text',
        //         'content' => '<h1>Welcome to our website</h1><p>This is the home page content.</p>',
        //         'page_address' => 'home',
        //         'page_width' => '2XL',
        //         'menu_title' => 'Home',
        //         'menu_type' => 'link',
        //         'open_page_in' => 'same_tab',
        //         'show_in_header' => true,
        //         'meta_title' => 'Home - My Website',
        //         'meta_description' => 'Welcome to our amazing website',
        //         'social_image' => 'https://example.com/home-image.jpg',
        //         'show_in_search' => true,
        //         'created_at' => now(),
        //         'updated_at' => now()
        //     ],
        //     [
        //         'id' => 2,
        //         'title' => 'About Us',
        //         'slug' => 'about',
        //         'content_type' => 'google_doc',
        //         'content' => 'https://docs.google.com/document/d/1234567890/edit',
        //         'page_address' => 'about',
        //         'page_width' => 'XL',
        //         'menu_title' => 'About',
        //         'menu_type' => 'link',
        //         'open_page_in' => 'same_tab',
        //         'show_in_header' => true,
        //         'meta_title' => 'About Us - My Website',
        //         'meta_description' => 'Learn more about our company and mission',
        //         'social_image' => 'https://example.com/about-image.jpg',
        //         'show_in_search' => true,
        //         'created_at' => now(),
        //         'updated_at' => now()
        //     ]
        // ];

        return response()->json($pages);
    }

    public function store(Request $request, Site $site)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'page_address' => 'required|string|max:255',
            'page_width' => 'nullable|string',
            'menu_title' => 'nullable|string|max:255',
            'menu_type' => 'nullable|string',
            'target' => 'nullable|string',
            'show_in_header' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'social_image' => 'nullable|url',
            'show_in_search' => 'boolean'
        ]);

        if ($site->workspace->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $domain_name = $site->domain_name;
            $db_name = str_replace('.' . domain_base(), '', $domain_name);
            if (!empty($site->db_name)) {
                $db_name = $site->db_name;
            }

            $page = [
                'title' => $request->title,
                'content_type' => $request->content_type,
                'content' => $request->content,
                'page_address' => $request->page_address,
                'page_width' => $request->page_width,
                'menu_title' => $request->menu_title,
                'menu_type' => $request->menu_type,
                'target' => $request->target,
                'show_in_header' => $request->boolean('show_in_header') ? 1 : 2,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'image_share_url' => $request->image_share_url,
                'show_in_search' => $request->boolean('show_in_search') ? 1 : 2
            ];

            $connectionName = setupTenantConnection($db_name);

            // Check nếu slug đã tồn tại
            if ($this->pageService->isSlugExists($connectionName, $page['page_address'])) {
                return response()->json(['status' => false, 'message' => 'Page address (slug) already exists.']);
            }

            $this->pageService->storeData($connectionName, $page);

            return response()->json(['status' => true, 'data' => $page, 'message' => 'Page created successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Something went wrong: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, Site $site, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'page_address' => 'required|string|max:255',
            'page_width' => 'nullable|string',
            'menu_title' => 'nullable|string|max:255',
            'menu_type' => 'nullable',
            'target' => 'nullable',
            'show_in_header' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'social_image' => 'nullable|url',
            'show_in_search' => 'boolean'
        ]);

        if ($site->workspace->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Mock response for updating a page
        try {
            $domain_name = $site->domain_name;
            $db_name = str_replace('.' . domain_base(), '', $domain_name);
            if (!empty($site->db_name)) {
                $db_name = $site->db_name;
            }

            $page = [
                'id' => $id,
                'title' => $request->title,
                'content_type' => $request->content_type,
                'content' => $request->content,
                'page_address' => $request->page_address,
                'page_width' => $request->page_width,
                'menu_title' => $request->menu_title,
                'menu_type' => $request->menu_type,
                'target' => $request->target,
                'show_in_header' => $request->boolean('show_in_header') ? 1 : 2,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'image_share_url' => $request->image_share_url,
                'show_in_search' => $request->boolean('show_in_search') ? 1 : 2
            ];

            $connectionName = setupTenantConnection($db_name);

            // Check nếu slug đã tồn tại
            if ($this->pageService->isSlugExists($connectionName, $page['page_address'], $id)) {
                return response()->json(['status' => false, 'message' => 'Page address (slug) already exists.']);
            }

            $this->pageService->updateData($connectionName, $page, $id);

            return response()->json(['status' => true, 'data' => $page, 'message' => 'Page updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Something went wrong: ' . $e->getMessage()]);
        }

        return response()->json($page);
    }

    public function destroy(Site $site, $id)
    {
        if ($site->workspace->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $domain_name = $site->domain_name;
        $db_name = str_replace('.' . domain_base(), '', $domain_name);
        if(!empty($site->db_name)){
            $db_name = $site->db_name;
        }

        $connectionName = setupTenantConnection($db_name);

        $res = $this->pageService->deleteData($connectionName, $id);

        // Mock response for deleting a page
        return response()->json(['message' => 'Page deleted successfully']);
    }
}
