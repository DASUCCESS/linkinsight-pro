<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\AiInsightsService;
use App\Services\LinkedinAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AiAssistantController extends Controller
{
    public function __construct(
        protected AiInsightsService $aiInsightsService,
        protected LinkedinAnalyticsService $analyticsService
    ) {
    }

    public function index(Request $request)
    {
        $summary = $this->analyticsService->getSummaryForUser($request->user(), null);
        $aiRecommendations = $this->aiInsightsService->forSummary($summary);

        return view('user.ai-assistant.index', compact('summary', 'aiRecommendations'));
    }

    public function run(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in([
                'weekly_insights',
                'post_ideas',
                'write_comment',
                'reply_comment',
                'connection_message',
                'improve_post',
            ])],
            'input_text' => 'nullable|string|max:5000',
        ]);

        $summary = $this->analyticsService->getSummaryForUser($request->user(), null);

        $payload = $this->aiInsightsService->runAssistantAction(
            $validated['action'],
            $summary,
            ['input_text' => $validated['input_text'] ?? null]
        );

        return response()->json([
            'status' => 'ok',
            'data' => $payload,
        ]);
    }
}
