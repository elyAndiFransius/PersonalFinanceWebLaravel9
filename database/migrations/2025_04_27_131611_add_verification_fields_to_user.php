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
            Schema::table('users', function (Blueprint $table) {
                // kolom untuk menyimpan token
                $table->string('token_code');
                $table->boolean('verification')->default(false);
            });
        }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('token_code');
            $table->dropColumn('verification');
        });
    }
};
