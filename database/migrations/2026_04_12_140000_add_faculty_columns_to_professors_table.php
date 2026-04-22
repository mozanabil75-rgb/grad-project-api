<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('professors')) {
            return;
        }

        Schema::table('professors', function (Blueprint $table): void {
            if (! Schema::hasColumn('professors', 'name')) {
                $table->string('name')->after('id');
            }
            if (! Schema::hasColumn('professors', 'email')) {
                $table->string('email')->unique()->after('name');
            }
            if (! Schema::hasColumn('professors', 'title')) {
                $table->string('title')->nullable()->after('email');
            }
            if (! Schema::hasColumn('professors', 'department_id')) {
                $table->foreignId('department_id')->after('title')->constrained()->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('professors')) {
            return;
        }

        Schema::table('professors', function (Blueprint $table): void {
            if (Schema::hasColumn('professors', 'department_id')) {
                $table->dropForeign(['department_id']);
            }
            $columns = ['name', 'email', 'title', 'department_id'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('professors', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
