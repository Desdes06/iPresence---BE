<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {

        if (!Schema::hasTable('activities')){
            Schema::create('activities', function (Blueprint $table) {
                $table->id();
                $table->string('nama_aktivitas');
                $table->string('uraian');
                $table->date('tanggal');
                $table->enum('status', ['PROSES', 'SELESAI'])->default('PROSES');
                $table->unsignedBigInteger('users_id');
                $table->timestamps();

                $table->foreign('users_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activities');
    }
};
