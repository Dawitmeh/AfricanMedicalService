<?php

use App\Models\BlogCategory;
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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(BlogCategory::class, 'category_id');
            $table->foreignIdFor(User::class, 'user_id');
            $table->string('Title');
            $table->string('url', 1000);
            $table->longText('description', 1000);
            $table->longText('blog', 1000);
            $table->string('image');
            $table->boolean('publish')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
