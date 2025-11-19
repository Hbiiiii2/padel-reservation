<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('membership_code')->unique()->nullable()->after('address');
            $table->string('membership_level')->default('regular')->after('membership_code');
            $table->date('membership_starts_at')->nullable()->after('membership_level');
            $table->date('membership_expires_at')->nullable()->after('membership_starts_at');
            $table->text('membership_notes')->nullable()->after('membership_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'membership_code',
                'membership_level',
                'membership_starts_at',
                'membership_expires_at',
                'membership_notes',
            ]);
        });
    }
};

