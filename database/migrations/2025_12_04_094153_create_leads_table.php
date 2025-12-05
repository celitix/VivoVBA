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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId("token_responses_id")->constrained()->onDelete(
                'cascade'
            );
            $table->boolean("is_converted")->default(false);
            $table->string("imei")->nullable();
            $table->string("remarks")->nullable();
            $table->timestamps();
        });
    }
    //   $table->foreign('response_id')
    //             ->references('id')
    //             ->on('token_responses')
    //             ->onDelete('cascade')

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
