<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_informations', function (Blueprint $table) {
            $table->id();
            $table->string('property');
            $table->text('value');
            $table->string('code');
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('tên của sản phẩm');
            $table->string('sku')->comment('sku là duy nhất');
            $table->integer('inventory')->default(0)->comment('số lượng sản phẩm còn trong kho. Nếu input text thì default');
            $table->string('price');
            $table->string('old_price');
            $table->string('link');
            $table->string('size');
            $table->string('color');
            $table->string('material');
            $table->text('description');
            $table->text('rating');
            $table->tinyInteger('best_selling')->default(1)->comment('best selling. 1: active, 2: inactive');
            $table->tinyInteger('new_arrival')->default(1)->comment('new arrival. 1: active, 2: inactive');
            $table->text('images')->comment('json encode của các link ảnh');
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->timestamps();
        });

        Schema::create('product_category', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('category_id');
            $table->timestamps();
        });

        Schema::create('configs', function (Blueprint $table) {
            $table->id();
            $table->string('site_name');
            $table->tinyInteger('dark_mode')->default(1)->comment('1: dard mode(show icon for change background-color) 2: not show');
            $table->tinyInteger('footer_is_show')->default(1)->comment('1: show footer 2: not show');
            $table->tinyInteger('header_is_show');
            $table->tinyInteger('hero_section_is_show')->default(1)->comment('1: show hero section 2: not show');
            $table->tinyInteger('disable_banner')->default(2)->comment('1: disable banner 2: show');
            $table->tinyInteger('disable_detail_page')->default(2)->comment('1: disable detail page 2: enable');
            $table->tinyInteger('disable_index')->default(2)->comment('1: disable indexing 2: enable');
            $table->tinyInteger('disable_auto_sync')->default(2)->comment('1: disable auto sync 2: enable');
            $table->tinyInteger('remove_icon_build_on');
            $table->tinyInteger('email_subscribed')->default(1)->comment('1: show subscribed email 2: not show');
            $table->tinyInteger('about_us_is_show')->default(1)->comment('1: show button about us on header 2: not show');
            $table->tinyInteger('feedback_is_show')->default(1)->comment('1: show feedback button on header 2: not show');
            $table->tinyInteger('text_center')->default(1)->comment('1: display title and information at the center page 2: not center');
            $table->tinyInteger('font_size')->default(1)->comment('1: display small text title 2: not small');
            $table->integer('paginate')->default(10)->comment('paginate from 10-50');
            $table->tinyInteger('grid_content')->default(3)->comment('Display the number of content columns');
            $table->tinyInteger('disable_toc')->default(2)->comment('1: disable TOC 2: enable');
            $table->string('font_family')->default('Roboto')->comment('Display font for website using google font');
            $table->tinyInteger('site_publish')->default(1)->comment('1: Publish your website to the internet 2: not publish');
            $table->timestamps();
        });

        Schema::create('nav_bars', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('link');
            $table->tinyInteger('position')->default(1)->comment('1: header 2: footer');
            $table->tinyInteger('target')->default(1)->comment('1: same tab 2: new tab');
            // $table->string('meta_title');
            // $table->text('meta_description');
            // $table->string('image_share_url');
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('page_address');
            $table->string('menu_title');
            $table->string('google_doc');
            $table->tinyInteger('menu_type')->default(1)->comment('1: kiểu link 2: kiểu button');
            $table->tinyInteger('target')->default(1)->comment('1: same tab 2: new tab');
            $table->tinyInteger('show_in_search')->default(1)->comment('1: show page from search results 2: not show');
            $table->timestamps();
        });

        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->text('content');
            $table->tinyInteger('status')->default(1)->comment('1: success 2: fail');
            $table->tinyInteger('type')->default(1)->comment('1: email feedback 2: email subscribed');
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->unique()->comment('mã đơn hàng');
            $table->string('name')->comment('tên khách hàng');
            $table->string('email')->comment('email khách hàng');
            $table->string('phone')->nullable()->comment('số điện thoại');
            $table->text('note')->nullable()->comment('ghi chú');
            $table->text('address')->comment('địa chỉ giao hàng');
            $table->string('discount_coupon')->nullable()->comment('mã giảm giá');
            $table->string('currency')->default('$')->comment('đơn vị tiền tệ');
            $table->decimal('discount', 10, 2)->default(0)->comment('giảm giá');
            $table->decimal('subtotal', 10, 2)->default(0)->comment('tổng tiền sản phẩm');
            $table->decimal('shipping', 10, 2)->default(0)->comment('phí vận chuyển');
            $table->decimal('total', 10, 2)->default(0)->comment('tổng tiền');
            $table->string('method')->default('COD')->comment('phương thức thanh toán');
            $table->tinyInteger('status')->default(0)->comment('trạng thái đơn hàng: 0: pending, 1: processing, 2: completed, 3: cancelled');
            $table->timestamps();
        });

        Schema::create('order_product', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('order_id');
            $table->string('sku')->comment('sku sản phẩm');
            $table->string('name')->comment('tên sản phẩm');
            $table->decimal('price', 10, 2)->comment('giá sản phẩm');
            $table->integer('quantity')->default(1)->comment('số lượng');
            $table->timestamps();
            
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_product');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('emails');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('nav_bars');
        Schema::dropIfExists('configs');
        Schema::dropIfExists('product_category');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('products');
        Schema::dropIfExists('site_informations');
    }
};
