<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.

    General Query / Purchase
    Consumer Name
    Contact Number (OTP validation required)
    Email ID (optional)
    Model
     */
    public function up(): void
    {
        Schema::create('token_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId("token_id")->constrained()->onDelete(
                'cascade'
            );
            $table->string("consumer_name");
            $table->string("contact_number");
            $table->string("email");
            $table->string("model")->nullable();
            $table->string("query")->nullable();
            $table->string("type")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('token_responses');
    }
};
