<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuizAttemptDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('quiz_attempt_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_result_id')->constrained('quiz_results')->onDelete('cascade'); // Liên kết với quiz_results
            $table->integer('question_id'); // ID của câu hỏi
            $table->text('question_text'); // Nội dung câu hỏi
            $table->json('answers'); // Các đáp án (lưu dạng JSON)
            $table->string('selected_answer')->nullable(); // Đáp án người dùng chọn
            $table->string('correct_answer'); // Đáp án đúng
            $table->boolean('is_correct')->default(false); // Đáp án có đúng không
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('quiz_attempt_details');
    }
}