<?php

namespace App\Service;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Site;
use Illuminate\Support\Facades\DB;

class PageService
{
    public function getData($connectionName)
    {
        $data = DB::connection($connectionName)->table('pages')->get();
        return $data;
    }

    public function storeData($connectionName, $data)
    {
        if ($this->isSlugExists($connectionName, $data['page_address'])) {
            throw new \Exception('Slug (page_address) already exists');
        }

        DB::connection($connectionName)->table('pages')->insertGetId([
            "title" => $data['title'],
            "content" => $data['content'],
            "page_address" => $data['page_address'],
            "page_width" => $data['page_width'],
            "menu_title" => $data['menu_title'],
            "menu_type" => $data['menu_type'],
            "target" => $data['target'],
            "show_in_header" => $data['show_in_header'],
            "meta_title" => $data['meta_title'],
            "meta_description" => $data['meta_description'],
            "image_share_url" => $data['image_share_url'],
            "show_in_search" => $data['show_in_search']
        ]);

        return true;
    }

    public function updateData($connectionName, $data, $id)
    {
        if ($this->isSlugExists($connectionName, $data['page_address'], $id)) {
            throw new \Exception('Slug (page_address) already exists');
        }

        DB::connection($connectionName)->table('pages')->where('id', $id)->update([
            "title" => $data['title'],
            "content" => $data['content'],
            "page_address" => $data['page_address'],
            "page_width" => $data['page_width'],
            "menu_title" => $data['menu_title'],
            "menu_type" => $data['menu_type'],
            "target" => $data['target'],
            "show_in_header" => $data['show_in_header'],
            "meta_title" => $data['meta_title'],
            "meta_description" => $data['meta_description'],
            "image_share_url" => $data['image_share_url'],
            "show_in_search" => $data['show_in_search']
        ]);

        return true;
    }

    public function deleteData($connectionName, $id)
    {
        DB::connection($connectionName)->table('pages')->where('id', $id)->delete();
        return true;
    }

    public function isSlugExists($connectionName, $slug, $excludeId = null)
    {
        $query = DB::connection($connectionName)->table('pages')->where('page_address', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId); // loại trừ chính bản thân khi update
        }

        return $query->exists();
    }
}
