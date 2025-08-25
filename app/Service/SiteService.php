<?php

namespace App\Service;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Site;
use Illuminate\Support\Facades\DB;

class SiteService
{
    public function getInformationData($connection)
    {
        $data = DB::connection($connection)->table('site_informations')->get();
        $res = [];
        foreach($data as $value)
        {
            $res[] = [
                "Property" => $value->property,
                "Value" => $value->value,
            ];
        }
        return $res;
    }

    public function getNameCategory($connection, $productId)
    {
        $categoryIds = DB::connection($connection)->table('product_category')->where('product_id', $productId)->pluck('category_id')->toArray();
        $names = DB::connection($connection)
            ->table('categories')
            ->whereIn('id', $categoryIds)
            ->pluck('name') // chỉ lấy cột name
            ->implode(', ');
        return $names;
    }

    public function getContentData($connection)
    {
        $data = DB::connection($connection)->table('products')->get();
        $res = [];
        foreach($data as $value)
        {
            $res[] = [
                "Title" => $value->title,
                "Slug" => $value->slug,
                "Excerpt" => $value->excerpt,
                "Thumbnail" => $value->thumbnail,
                "Categories" => $this->getNameCategory($connection, $value->id),
                "Author" => $value->author,
                "Content" => $value->content,
                "Published Date" => $value->published_date,
                "Status" => $value->status,
            ];
        }
        return $res;
    }
    
    public function formatSite($connectionName, $siteData)
    {
        $siteData['dark_mode'] = 1; // Show dark mode
        $siteData['hide_header'] = 1; // Hide header
        $siteData['hide_footer'] = 1; // Hide footer
        $siteData['disable_hero'] = 2; // Hide hero
        $siteData['collect_email'] = 2; // Show collected emails
        $siteData['about_us'] = 2; // Show the About Us page
        $siteData['disable_auto_sync'] = 2; // Disable auto-sync
        $siteData['feedback_form'] = 2; // Show feedback form
        $siteData['text_center'] = 2; // Text center
        $siteData['small_hero'] = 1; // Font size
        $siteData['grid_content'] = 2; // Grid content
        $siteData['pagination_size'] = 3; // Pagination Size
        $siteData['font_family'] = "Poppins"; // Font Family
        $siteData['published'] = 1; // Publish website
        $check = DB::connection($connectionName)->table('configs')->first();
        if($check)
        {
            $siteData['dark_mode'] = $check->dark_mode; // Show dark mode
            $siteData['hide_header'] = $check->header_is_show; // Hide header
            $siteData['hide_footer'] = $check->footer_is_show; // Hide footer
            $siteData['disable_hero'] = $check->hero_section_is_show; // Hide hero
            $siteData['collect_email'] = $check->email_subscribed; // Show collected emails
            $siteData['about_us'] = $check->about_us_is_show; // Show the About Us page
            $siteData['disable_auto_sync'] = $check->disable_auto_sync; // Disable auto-sync
            $siteData['feedback_form'] = $check->feedback_is_show; // Show feedback form
            $siteData['text_center'] = $check->text_center; // Text center
            $siteData['small_hero'] = $check->font_size; // Font size
            $siteData['grid_content'] = $check->grid_content; // Grid content
            $siteData['pagination_size'] = $check->paginate; // Pagination Size
            $siteData['font_family'] = $check->font_family; // Font Family
            $siteData['published'] = $check->site_publish; // Publish website
        }
        return $siteData;
    }
    public function settingUpdate($connectionName, $data)
    {
        $check = DB::connection($connectionName)->table('configs')->first();
        if(!$check) {
            DB::connection($connectionName)->table('configs')->insertGetId([
                "site_name" => $data['name'],
                "dark_mode" => $data['dark_mode'],
                "footer_is_show" => $data['hide_footer'],
                "header_is_show" => $data['hide_header'],
                "hero_section_is_show" => $data['disable_hero'],
                "email_subscribed" => $data['collect_email'],
                "text_center" => $data['text_center'],
                "about_us_is_show" => $data['about_us'],
                "disable_auto_sync" => $data['disable_auto_sync'],
                "feedback_is_show" => $data['feedback_form'],
                "font_size" => $data['small_hero'],
                "font_family" => $data['font_family'],
                "paginate" => $data['pagination_size'],
                "site_publish" => $data['published'],
                "remove_icon_build_on" => $data['build_on_sheetany'] ? 1 : 0,
                "grid_content" => $data['grid_content']
            ]);
        } else {
            DB::connection($connectionName)->table('configs')->where('id', 1)->update([
                "site_name" => $data['name'],
                "dark_mode" => $data['dark_mode'],
                "footer_is_show" => $data['hide_footer'],
                "header_is_show" => $data['hide_header'],
                "hero_section_is_show" => $data['disable_hero'],
                "email_subscribed" => $data['collect_email'],
                "text_center" => $data['text_center'],
                "about_us_is_show" => $data['about_us'],
                "disable_auto_sync" => $data['disable_auto_sync'],
                "feedback_is_show" => $data['feedback_form'],
                "font_size" => $data['small_hero'],
                "font_family" => $data['font_family'],
                "paginate" => $data['pagination_size'],
                "site_publish" => $data['published'],
                "remove_icon_build_on" => $data['build_on_sheetany'] ? 1 : 0,
                "grid_content" => $data['grid_content']
            ]);
        }
        return true;
    }

}
