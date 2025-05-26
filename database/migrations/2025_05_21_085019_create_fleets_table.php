<?php

use App\Models\Country;
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
        Schema::create('fleets', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Country::class, 'country_id');
            $table->string('name');
            $table->string('capacity');
            $table->enum('classification', ['Ambulance', 'Helicopter', 'Jet']);
            $table->string('icon', 2048)->nullable();
            $table->boolean('Available')->default(0);
            $table->boolean('Active')->default(0);
            $table->longText('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fleets');
    }
};
