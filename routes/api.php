<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuizResultController;
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/fetch-questions', [QuizController::class, 'fetchQuestions']);
    Route::post('/save-result', [QuizController::class, 'saveResult']);
    Route::get('/history', [QuizController::class, 'getUserResults']);
    Route::get('/result/{id}', [QuizResultController::class, 'getResult']);
});
