<?php

namespace App\Http\Controllers;

use App\Models\QuizResult;
use App\Models\QuizAttemptDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuizResultController extends Controller
{
    public function saveResult(Request $request)
    {
        // Xác thực user từ token
        $user = Auth::user();
        if (!$user) {
            \Log::error('User not authenticated');
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Log dữ liệu nhận được
        \Log::info('Received payload:', $request->all());

        // Validate dữ liệu
        try {
            $validated = $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'score' => 'required|integer',
                'total_questions' => 'required|integer',
                'quiz' => 'required|string',
                'difficulty' => 'required|string|in:easy,medium,hard',
                'attempt_details' => 'required|array',
                'attempt_details.*.question_id' => 'required|integer',
                'attempt_details.*.question_text' => 'required|string',
                'attempt_details.*.answers' => 'required',
                'attempt_details.*.selected_answer' => 'nullable|string',
                'attempt_details.*.correct_answer' => 'nullable|string',
                'attempt_details.*.is_correct' => 'required|boolean',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed: ' . json_encode($e->errors()));
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        }

        // Log dữ liệu đã validate
        \Log::info('Validated data:', $validated);
        \Log::info('Number of attempt details: ' . (isset($validated['attempt_details']) ? count($validated['attempt_details']) : 0));

        // Sử dụng transaction để đảm bảo tính toàn vẹn dữ liệu
        try {
            $result = DB::transaction(function () use ($validated, $user) {
                // Lưu vào bảng quiz_results
                $quizResult = QuizResult::create([
                    'user_id' => $validated['user_id'],
                    'score' => $validated['score'],
                    'total_questions' => $validated['total_questions'],
                    'quiz' => $validated['quiz'],
                    'difficulty' => $validated['difficulty'],
                ]);

                \Log::info('QuizResult created with ID: ' . $quizResult->id);

                // Lưu chi tiết câu hỏi và câu trả lời vào bảng quiz_attempt_details
                $detailsSaved = 0;
                if (!empty($validated['attempt_details'])) {
                    \Log::info('Attempt details is valid, proceeding to save.');
                    foreach ($validated['attempt_details'] as $index => $detail) {
                        \Log::info("Attempting to save detail #$index:", $detail);
                        try {
                            $attemptDetail = QuizAttemptDetail::create([
                                'quiz_result_id' => $quizResult->id,
                                'question_id' => $detail['question_id'],
                                'question_text' => $detail['question_text'],
                                'answers' => is_array($detail['answers']) ? json_encode($detail['answers']) : (string) $detail['answers'],
                                'selected_answer' => $detail['selected_answer'] ?? null,
                                'correct_answer' => $detail['correct_answer'] ?? 'unknown',
                                'is_correct' => (bool) $detail['is_correct'],
                            ]);
                            \Log::info("Saved detail #$index with ID: " . $attemptDetail->id);
                            $detailsSaved++;
                        } catch (\Exception $e) {
                            \Log::error("Failed to save detail #$index: " . $e->getMessage());
                            throw $e;
                        }
                    }
                } else {
                    \Log::warning('Attempt details is empty or invalid:', $validated['attempt_details'] ?? 'Not set');
                }

                \Log::info('Total details saved: ' . $detailsSaved);

                return $quizResult;
            });

            \Log::info('Transaction completed successfully');
            return response()->json([
                'message' => 'Kết quả đã được lưu thành công',
                'result_id' => $result->id
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error saving quiz result: ' . $e->getMessage());
            return response()->json(['error' => 'Không thể lưu kết quả: ' . $e->getMessage()], 500);
        }
    }

    public function getHistory(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $history = QuizResult::where('user_id', $user->id)
            ->select('id', 'score', 'total_questions', 'quiz', 'difficulty', 'created_at')
            ->get();

        return response()->json($history, 200);
    }

    public function getResult($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $result = QuizResult::with('details')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$result) {
            return response()->json(['message' => 'Result not found'], 404);
        }

        return response()->json($result, 200);
    }
}