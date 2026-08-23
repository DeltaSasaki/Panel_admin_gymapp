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
        if (!Schema::hasTable('cashiers')) {
            Schema::create('cashiers', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->nullable();
                $table->integer('gym_id');
                $table->string('first_name', 100);
                $table->string('last_name', 100);
                $table->string('email', 150)->nullable();
                $table->string('phone', 30)->nullable();
                $table->string('shift', 100)->nullable()->default('Mañana (06:00 - 14:00)');
                $table->date('hire_date')->nullable();
                $table->decimal('salary', 10, 2)->nullable()->default(0.00);
                $table->string('photo_url', 500)->nullable();
                $table->text('notes')->nullable();
                $table->boolean('is_active')->default(true);
                $table->dateTime('createdAt');
                $table->dateTime('updatedAt');

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
                $table->foreign('gym_id')->references('id')->on('gyms')->onDelete('cascade')->onUpdate('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashiers');
    }
};
