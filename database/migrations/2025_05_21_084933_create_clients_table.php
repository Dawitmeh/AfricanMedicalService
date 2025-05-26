<?php

use App\Models\ClientType;
use App\Models\Country;
use App\Models\User;
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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'user_id');
            $table->foreignIdFor(ClientType::class, 'type_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('age');
            $table->string('country_code');
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->string('image', 2048)->nullable();
            $table->string('password');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
