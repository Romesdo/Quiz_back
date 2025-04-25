<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttemptDetail extends Model
{
    use HasFactory;
    protected $table = 'quiz_attempt_details';
    protected $fillable = [
        'quiz_result_id',
        'question_id',
        'question_text',
        'answers',
        'selected_answer',
        'correct_answer',
        'is_correct',
    ];

    protected $casts = [
        'answers' => 'array',
        'is_correct' => 'boolean',
    ];

    public function quizResult()
    {
        return $this->belongsTo(QuizResult::class, 'quiz_result_id');
    }
}