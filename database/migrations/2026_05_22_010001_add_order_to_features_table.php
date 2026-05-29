<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('features', function (Blueprint $table) {
            if (!Schema::hasColumn('features', 'order')) {
                $table->integer('order')->default(0)->after('icon');
            }
        });

        Schema::table('sliders', function (Blueprint $table) {
            if (!Schema::hasColumn('sliders', 'order')) {
                $table->integer('order')->default(0)->after('status');
            }
        });

        Schema::table('banners', function (Blueprint $table) {
            if (!Schema::hasColumn('banners', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (!Schema::hasColumn('banners', 'order')) {
                $table->integer('order')->default(0)->after('status');
            }
        });

        Schema::table('testimonials', function (Blueprint $table) {
            if (!Schema::hasColumn('testimonials', 'status')) {
                $table->boolean('status')->default(true)->after('client_image');
            }
            if (!Schema::hasColumn('testimonials', 'order')) {
                $table->integer('order')->default(0)->after('status');
            }
        });

        Schema::table('media', function (Blueprint $table) {
            if (!Schema::hasColumn('media', 'file_name')) {
                $table->string('file_name')->nullable()->after('id');
            }
            if (!Schema::hasColumn('media', 'file_path')) {
                $table->string('file_path')->nullable()->after('file_name');
            }
            if (!Schema::hasColumn('media', 'file_size')) {
                $table->unsignedBigInteger('file_size')->default(0)->after('file_path');
            }
            if (!Schema::hasColumn('media', 'file_type')) {
                $table->string('file_type')->nullable()->after('file_size');
            }
            if (!Schema::hasColumn('media', 'folder')) {
                $table->string('folder')->default('general')->after('file_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('features', function (Blueprint $table) {
            $table->dropColumn(['order']);
        });
        Schema::table('sliders', function (Blueprint $table) {
            $table->dropColumn(['order']);
        });
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn(['description', 'order']);
        });
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropColumn(['status', 'order']);
        });
    }
};
