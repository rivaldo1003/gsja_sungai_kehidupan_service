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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id")->nullable();
            $table->string('address', 100)->nullable(false);
            $table->string('phone_number', 20)->nullable(false);
            $table->string('gander', 20)->nullable(false);
            $table->string('age', 20)->nullable(false);
            $table->string('birth_place', 100)->nullable(false);
            $table->string('birth_date', 20)->nullable(false);
            $table->timestamps();

            $table->foreign("user_id")->references("id")->on("users");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
