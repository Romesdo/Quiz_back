<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizResult extends Model
{
    use HasFactory;

    protected $table = 'quiz_results';

    protected $fillable = ['user_id', 'score', 'total_questions', 'quiz', 'difficulty'];

    // Quan hệ với User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Quan hệ với QuizAttemptDetail
    public function details(): HasMany
    {
        return $this->hasMany(QuizAttemptDetail::class, 'quiz_result_id');
    }
}