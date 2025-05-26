<?php

use App\Models\Hospital;
use App\Models\ProductPackage;
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
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'user_id');
            $table->foreignIdFor(Hospital::class, 'hospital_id')->nullable();
            $table->foreignIdFor(ProductPackage::class, 'package_id')->nullable();
            $table->enum('inquiry_type', ['Emergency', 'Feedback', 'General', 'Payment', 'Registration'])->default('General');
            $table->string('location')->nullable();
            $table->longText('message');
            $table->boolean('active')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
