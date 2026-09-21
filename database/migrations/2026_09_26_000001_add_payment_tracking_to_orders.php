<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('card_brand', 20)->nullable()->after('payment_method');
            $table->string('card_last4', 4)->nullable()->after('card_brand');
            $table->string('receipt_image')->nullable()->after('card_last4');
            $table->string('courier', 60)->nullable()->after('receipt_image');
            $table->string('tracking_number', 40)->nullable()->after('courier');
            $table->dateTime('estimated_delivery')->nullable()->after('tracking_number');
            $table->dateTime('shipped_at')->nullable()->after('estimated_delivery');
            $table->dateTime('delivered_at')->nullable()->after('shipped_at');
        });

        Schema::create('tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('title', 120);
            $table->string('description', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_events');
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'card_brand', 'card_last4', 'receipt_image', 'courier',
                'tracking_number', 'estimated_delivery', 'shipped_at', 'delivered_at',
            ]);
        });
    }
};
