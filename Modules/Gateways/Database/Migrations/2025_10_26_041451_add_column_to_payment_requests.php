<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_requests', function (Blueprint $table) {
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_status')->nullable();
            $table->boolean('is_authorized')->default(false);
            $table->boolean('is_captured')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_requests', function (Blueprint $table) {
            $table->dropColumn('stripe_payment_intent_id');
            $table->dropColumn('stripe_status');
            $table->dropColumn('is_authorized');
            $table->dropColumn('is_captured');
        });
    }
};
