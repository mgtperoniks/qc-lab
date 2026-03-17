<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Illuminate\Support\Facades\Schema::table('spectro_results', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->decimal('c', 6, 4)->nullable()->change();
            $table->decimal('si', 6, 4)->nullable()->change();
            $table->decimal('mn', 6, 4)->nullable()->change();
            $table->decimal('p', 6, 4)->nullable()->change();
            $table->decimal('s', 6, 4)->nullable()->change();
            $table->decimal('cr', 6, 4)->nullable()->change();
            $table->decimal('ni', 6, 4)->nullable()->change();
            $table->decimal('mo', 6, 4)->nullable()->change();
            $table->decimal('cu', 6, 4)->nullable()->change();
            $table->decimal('n', 6, 4)->nullable()->change();
        });
    }

    public function down(): void
    {
        Illuminate\Support\Facades\Schema::table('spectro_results', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->decimal('c', 5, 2)->nullable()->change();
            $table->decimal('si', 5, 2)->nullable()->change();
            $table->decimal('mn', 5, 2)->nullable()->change();
            $table->decimal('p', 5, 2)->nullable()->change();
            $table->decimal('s', 5, 2)->nullable()->change();
            $table->decimal('cr', 5, 2)->nullable()->change();
            $table->decimal('ni', 5, 2)->nullable()->change();
            $table->decimal('mo', 5, 2)->nullable()->change();
            $table->decimal('cu', 5, 2)->nullable()->change();
            $table->decimal('n', 5, 2)->nullable()->change();
        });
    }
};
