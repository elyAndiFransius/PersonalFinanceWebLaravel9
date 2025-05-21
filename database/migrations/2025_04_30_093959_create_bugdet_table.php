<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // Buat tabel budgets terlebih dahulu
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->integer('pemasukkan');
            $table->enum('priode', ['harian', 'mingguan', 'bulanan', 'tahunan']);
            $table->timestamps();
        });

        // Buat tabel categories yang tergantung pada budgets
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->integer('jumlah');
            $table->foreignId('budget_id')->constrained('budgets')->onDelete('cascade');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Penting: urutan drop kebalik dari create
        Schema::dropIfExists('budget');
        Schema::dropIfExists('categories');
    }
};
