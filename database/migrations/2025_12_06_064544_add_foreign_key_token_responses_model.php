<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('token_responses', function (Blueprint $table) {
            $table->dropColumn("model");
            $table->foreignId("model_id")
                ->nullable()
                ->constrained("mobile_models")
                ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('token_responses', function (Blueprint $table) {
            $table->dropColumn("model_id");
            $table->string("model");
        });
    }
};
