<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class AiInsightsService
{
    public function forSummary(array $summary): array
    {
        if (($summary['status'] ?? 'empty') !== 'ok') {
            return $this->fallback('No synced analytics data found yet. Sync your LinkedIn profile to generate AI insights.');
        }

        $context = [
            'profile' => Arr::only($summary['profile'] ?? [], [
                'name', 'headline', 'followers', 'connections', 'followers_change', 'connections_change', 'views_total', 'search_total',
            ]),
            'filter' => $summary['filter'] ?? [],
            'posts_overview' => $summary['posts_overview'] ?? [],
            'audience_demographics' => $summary['audience_demographics'] ?? [],
            'audience_insights' => $summary['audience_insights'] ?? [],
            'creator_audience' => $summary['creator_audience'] ?? [],
        ];

        return $this->resolveInsights($context);
    }

    public function forRecommendationsPayload(array $payload): array
    {
        return $this->resolveInsights($payload);
    }

    protected function resolveInsights(array $context): array
    {
        $enabled = (bool) Setting::getValue('ai', 'enabled', false);
        $apiKey = (string) (Setting::getValue('ai', 'groq_api_key') ?: env('GROQ_API_KEY', ''));
        $model = (string) (Setting::getValue('ai', 'groq_model', 'llama-3.3-70b-versatile'));
        $temperature = (float) Setting::getValue('ai', 'temperature', 0.3);
        $maxTokens = (int) Setting::getValue('ai', 'max_tokens', 900);

        if (! $enabled || $apiKey === '') {
            return $this->fallback('AI recommendations are currently disabled by the admin.');
        }

        $response = Http::withToken($apiKey)
            ->timeout(20)
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => $model,
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a LinkedIn analytics strategist. Return JSON with keys: summary (string), recommendations (array of short strings), insights (array of short strings), risks (array of short strings). Keep suggestions specific and practical.',
                    ],
                    [
                        'role' => 'user',
                        'content' => 'Analyze this analytics context and produce recommendations: ' . json_encode($context),
                    ],
                ],
            ]);

        if (! $response->successful()) {
            return $this->fallback('Groq request failed. Showing baseline insights until connection is stable.');
        }

        $content = data_get($response->json(), 'choices.0.message.content');
        $decoded = is_string($content) ? json_decode($content, true) : null;

        if (! is_array($decoded)) {
            return $this->fallback('Could not parse AI response. Showing baseline recommendations.');
        }

        return [
            'source' => 'groq',
            'model' => $model,
            'summary' => (string) ($decoded['summary'] ?? 'AI insights generated from your latest analytics.'),
            'recommendations' => array_values(array_slice((array) ($decoded['recommendations'] ?? []), 0, 6)),
            'insights' => array_values(array_slice((array) ($decoded['insights'] ?? []), 0, 6)),
            'risks' => array_values(array_slice((array) ($decoded['risks'] ?? []), 0, 4)),
        ];
    }

    public function runAssistantAction(string $action, array $summary, array $input = []): array
    {
        $snapshot = $this->buildPerformanceSnapshot($summary);

        $prompts = [
            'weekly_insights' => 'Generate a short weekly analytics brief. Explain: what is happening, why it happened, and what to do next. Keep output concise and practical.',
            'post_ideas' => 'Suggest 5 post ideas and simple outlines aligned to this audience and recent performance trends.',
            'write_comment' => 'Generate 5 thoughtful comments the user can leave on other LinkedIn posts. Keep professional and engagement-oriented.',
            'reply_comment' => 'Generate 4 smart replies to comments on the user\'s post. Keep tone helpful and professional.',
            'connection_message' => 'Draft 3 concise connection messages for networking. Make them personal, value-driven, and professional.',
            'improve_post' => 'Improve the provided post draft with a stronger hook, clearer structure, and engagement-optimized wording. Return improved draft plus 3 hook variants.',
        ];

        $instruction = $prompts[$action] ?? $prompts['weekly_insights'];

        $context = [
            'action' => $action,
            'instruction' => $instruction,
            'snapshot' => $snapshot,
            'input' => $input,
        ];

        $resolved = $this->resolveAssistant($context);

        return [
            'action' => $action,
            'title' => $this->titleForAction($action),
            'items' => $resolved,
            'source' => $this->isAiEnabled() ? 'groq' : 'local',
        ];
    }

    protected function resolveAssistant(array $context): array
    {
        if (! $this->isAiEnabled()) {
            return $this->fallbackAssistant($context['action'] ?? 'weekly_insights');
        }

        $apiKey = (string) (Setting::getValue('ai', 'groq_api_key') ?: env('GROQ_API_KEY', ''));
        $model = (string) (Setting::getValue('ai', 'groq_model', 'llama-3.3-70b-versatile'));
        $temperature = (float) Setting::getValue('ai', 'temperature', 0.3);
        $maxTokens = (int) Setting::getValue('ai', 'max_tokens', 900);

        $response = Http::withToken($apiKey)
            ->timeout(20)
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => $model,
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a LinkedIn growth assistant. Return JSON with key "items" (array of short strings). Keep each item concise, practical, and copy-ready.',
                    ],
                    [
                        'role' => 'user',
                        'content' => json_encode($context),
                    ],
                ],
            ]);

        if (! $response->successful()) {
            return $this->fallbackAssistant($context['action'] ?? 'weekly_insights');
        }

        $content = data_get($response->json(), 'choices.0.message.content');
        $decoded = is_string($content) ? json_decode($content, true) : null;
        $items = is_array($decoded) ? (array) ($decoded['items'] ?? []) : [];

        if (empty($items)) {
            return $this->fallbackAssistant($context['action'] ?? 'weekly_insights');
        }

        return array_values(array_slice(array_map(fn ($v) => (string) $v, $items), 0, 8));
    }

    protected function buildPerformanceSnapshot(array $summary): array
    {
        $profile = $summary['profile'] ?? [];
        $postsOverview = $summary['posts_overview'] ?? [];

        return [
            'profile_name' => $profile['name'] ?? 'LinkedIn profile',
            'followers' => (int) ($profile['followers'] ?? 0),
            'connections' => (int) ($profile['connections'] ?? 0),
            'followers_change' => (int) ($profile['followers_change'] ?? 0),
            'connections_change' => (int) ($profile['connections_change'] ?? 0),
            'profile_views_total' => (int) ($profile['views_total'] ?? 0),
            'search_appearances_total' => (int) ($profile['search_total'] ?? 0),
            'post_count' => (int) ($postsOverview['posts_count'] ?? 0),
            'impressions' => (int) ($postsOverview['impressions_sum'] ?? 0),
            'reactions' => (int) ($postsOverview['reactions_sum'] ?? 0),
            'comments' => (int) ($postsOverview['comments_sum'] ?? 0),
            'reposts' => (int) ($postsOverview['reposts_sum'] ?? 0),
            'avg_engagement_rate' => (float) ($postsOverview['avg_engagement_rate'] ?? 0),
        ];
    }

    protected function isAiEnabled(): bool
    {
        $enabled = (bool) Setting::getValue('ai', 'enabled', false);
        $apiKey = (string) (Setting::getValue('ai', 'groq_api_key') ?: env('GROQ_API_KEY', ''));

        return $enabled && $apiKey !== '';
    }

    protected function titleForAction(string $action): string
    {
        return match ($action) {
            'weekly_insights' => 'Weekly Insights',
            'post_ideas' => 'Post Ideas',
            'write_comment' => 'Suggested Comments',
            'reply_comment' => 'Comment Replies',
            'connection_message' => 'Connection Messages',
            'improve_post' => 'Improved Post Draft',
            default => 'AI Assistant Output',
        };
    }

    protected function fallbackAssistant(string $action): array
    {
        return match ($action) {
            'post_ideas' => [
                'Share a quick lesson from a recent challenge and what changed after applying it.',
                'Post a 3-step framework your audience can apply this week.',
                'Publish a short case-style update: problem, action, measurable result.',
            ],
            'write_comment' => [
                'Great perspective—your point on execution speed is especially useful. What metric do you track first?',
                'This is practical and clear. I like how you tied strategy to daily habits.',
                'Strong insight. Have you seen this work better for early-stage or mature teams?',
            ],
            'reply_comment' => [
                'Thanks for the thoughtful comment—great point. We saw similar results after simplifying the CTA.',
                'Appreciate this. We are testing that angle next and will share outcomes.',
                'Excellent question. Short answer: consistency + clearer positioning made the biggest difference.',
            ],
            'connection_message' => [
                'Hi [Name], I appreciated your insights on [topic]. I\'d value connecting and exchanging ideas.',
                'Hi [Name], your recent post on [topic] was excellent. Open to connecting?',
                'Hello [Name], I work on [area] and found your perspective helpful. Would love to connect.',
            ],
            'improve_post' => [
                'Hook: Most LinkedIn posts fail before the second line—here\'s how to fix that.',
                'Structure: Problem → 3 practical points → clear CTA asking a specific question.',
                'Close with: “Want the checklist? Comment CHECKLIST and I\'ll send it.”',
            ],
            default => [
                'What is happening: engagement is concentrated in specific content formats and topics.',
                'Why: recent posts with clearer value and stronger hooks performed better.',
                'What next: post 3–5 times this week, repeat winning format, and use one clear CTA per post.',
            ],
        };
    }

    protected function fallback(string $summary): array
    {
        return [
            'source' => 'local',
            'model' => null,
            'summary' => $summary,
            'recommendations' => [
                'Post 3–5 times weekly and keep formats consistent for at least 4 weeks.',
                'Prioritize topics with above-average engagement rate and impressions.',
                'Use stronger CTAs on posts with high reach but low comments.',
            ],
            'insights' => [
                'Recent profile metrics and post performance are used as the baseline.',
                'Audience demographics and creator metrics improve precision after each sync.',
            ],
            'risks' => [
                'Sparse data windows can reduce recommendation confidence.',
            ],
        ];
    }
}
