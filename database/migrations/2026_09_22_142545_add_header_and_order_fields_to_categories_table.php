<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('show_in_header')->default(false)->after('description');
            $table->unsignedInteger('menu_order')->nullable()->after('show_in_header');
        });

        // Backfill existing categories to keep current header navigation intact
        $existingCategories = DB::table('categories')->orderBy('name')->get();
        $order = 1;
        foreach ($existingCategories as $cat) {
            DB::table('categories')->where('id', $cat->id)->update([
                'show_in_header' => true,
                'menu_order' => $order++,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['show_in_header', 'menu_order']);
        });
    }
};
