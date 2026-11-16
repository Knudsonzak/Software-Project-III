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
        Schema::create('leave_forms', function (Blueprint $table) {
            $table->id('form_id');
            $table->unsignedBigInteger('emp_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('leave_type_id');
            $table->timestamps();

            $table->foreign('emp_id')->references('emp_id')->on('employees')->onDelete('cascade');
            $table->foreign('leave_type_id')->references('leave_type_id')->on('leave_types')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leave_forms');
    }
};
