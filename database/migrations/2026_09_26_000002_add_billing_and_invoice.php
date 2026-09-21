<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('billing_name', 150)->nullable()->after('email');
            $table->string('doc_type', 10)->nullable()->after('billing_name'); // DUI | NIT
            $table->string('doc_number', 20)->nullable()->after('doc_type');
            $table->string('nrc', 20)->nullable()->after('doc_number');
            $table->string('phone', 20)->nullable()->after('nrc');
            $table->string('billing_address', 500)->nullable()->after('phone');
            $table->string('city', 100)->nullable()->after('billing_address');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('invoice_uuid', 40)->nullable()->after('delivered_at');
            $table->string('invoice_control', 60)->nullable()->after('invoice_uuid');
            $table->string('invoice_seal', 80)->nullable()->after('invoice_control');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['invoice_uuid', 'invoice_control', 'invoice_seal']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['billing_name', 'doc_type', 'doc_number', 'nrc', 'phone', 'billing_address', 'city']);
        });
    }
};
