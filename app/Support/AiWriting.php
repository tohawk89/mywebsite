<?php

namespace App\Support;

use App\Ai\Agents\TagSuggester;
use App\Ai\Agents\WritingAssistant;
use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Laravel\Ai\AiManager;
use Laravel\Ai\Exceptions\InsufficientCreditsException;
use Laravel\Ai\Exceptions\ProviderConnectionException;
use Laravel\Ai\Exceptions\ProviderOverloadedException;
use Laravel\Ai\Exceptions\RateLimitedException;
use Laravel\Ai\Responses\AgentResponse;
use Throwable;

/**
 * AI writing help for the post editor (Site settings > AI writing), through the Laravel AI SDK.
 * The provider, model and encrypted API key come from settings and are applied per request.
 */
class AiWriting
{
    public const PROVIDERS = [
        'anthropic' => 'Claude (Anthropic)',
        'gemini' => 'Gemini (Google)',
        'openai' => 'GPT (OpenAI)',
    ];

    public const MODEL_TIERS = [
        'smartest' => 'Most capable',
        'default' => 'Balanced',
        'cheapest' => 'Fastest and cheapest',
    ];

    /** Longest text sent in one request, in characters. */
    public const MAX_INPUT = 12000;

    private const TIMEOUT = 60;

    public static function provider(): ?string
    {
        $provider = SiteOptions::get('ai_provider');

        return array_key_exists($provider, self::PROVIDERS) ? $provider : null;
    }

    public static function enabled(): bool
    {
        $provider = self::provider();

        return $provider !== null && self::apiKey($provider) !== null;
    }

    public static function apiKey(string $provider): ?string
    {
        $encrypted = (string) Setting::get("ai_key_{$provider}", '');

        if ($encrypted === '') {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * The model for the chosen provider: the custom model if set, otherwise the SDK's model for the tier.
     */
    public static function model(string $provider, ?string $tier = null, ?string $custom = null): string
    {
        $custom ??= SiteOptions::get('ai_model');

        if ($custom !== '') {
            return $custom;
        }

        $textProvider = app(AiManager::class)->textProvider($provider);

        return match ($tier ?? SiteOptions::get('ai_model_tier')) {
            'cheapest' => $textProvider->cheapestTextModel(),
            'default' => $textProvider->defaultTextModel(),
            default => $textProvider->smartestTextModel(),
        };
    }

    /**
     * Rewrite text with one of WritingAssistant::ACTIONS.
     */
    public static function rewrite(string $action, string $text): string
    {
        if (! array_key_exists($action, WritingAssistant::ACTIONS)) {
            throw new AiWritingException('Unknown writing action.');
        }

        return trim(self::run(new WritingAssistant($action, SiteOptions::get('ai_style')), self::limit($text))->text);
    }

    /**
     * @return array<int, string> Up to three title ideas.
     */
    public static function suggestTitles(string $text): array
    {
        $reply = self::run(new WritingAssistant('titles', SiteOptions::get('ai_style')), self::limit($text))->text;

        return collect(preg_split('/\R/', $reply))
            ->map(fn (string $line) => trim($line, " \t-*•\"'0123456789."))
            ->filter()
            ->take(3)
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $existingTags
     * @return array<int, string>
     */
    public static function suggestTags(string $title, string $content, array $existingTags): array
    {
        $response = self::run(new TagSuggester($existingTags), self::limit(trim("Title: {$title}\n\n{$content}")));

        return collect($response['tags'] ?? [])
            ->map(fn ($tag) => Str::limit(trim((string) $tag, " #\t"), 40, ''))
            ->filter()
            ->unique(fn (string $tag) => mb_strtolower($tag))
            ->take(5)
            ->values()
            ->all();
    }

    /**
     * Send a tiny request to check the key and model. Uses the given values, falling back to saved settings.
     */
    public static function testConnection(string $provider, ?string $key, string $tier, string $custom): string
    {
        $key ??= self::apiKey($provider);

        if ($key === null) {
            throw new AiWritingException('Add an API key first.');
        }

        $model = self::model($provider, $tier, $custom);
        self::configure($provider, $key);
        self::run(new WritingAssistant('fix'), 'Helo world.', $provider, $model);

        return $model;
    }

    /**
     * A message for the admin that explains a failed request without exposing details.
     */
    public static function errorMessage(Throwable $exception): string
    {
        return match (true) {
            $exception instanceof RateLimitedException => 'The AI provider is rate limiting requests. Try again in a minute.',
            $exception instanceof InsufficientCreditsException => 'Your AI provider account is out of credits.',
            $exception instanceof ProviderOverloadedException => 'The AI provider is busy right now. Try again shortly.',
            $exception instanceof ProviderConnectionException => 'Couldn\'t reach the AI provider. Check your connection and try again.',
            $exception instanceof AiWritingException => $exception->getMessage(),
            default => 'The AI request failed. Check your API key and model in Site settings > AI writing.',
        };
    }

    /**
     * @return AgentResponse
     */
    private static function run(object $agent, string $prompt, ?string $provider = null, ?string $model = null)
    {
        $provider ??= self::provider();

        if ($provider === null || ($provider === self::provider() && ! self::enabled())) {
            throw new AiWritingException('AI writing is turned off. Set it up in Site settings > AI writing.');
        }

        if ($model === null) {
            self::configure($provider, self::apiKey($provider));
            $model = self::model($provider);
        }

        return $agent->prompt($prompt, provider: $provider, model: $model, timeout: self::TIMEOUT);
    }

    /**
     * Point the SDK's provider at the saved (or given) key for this request.
     */
    private static function configure(string $provider, ?string $key): void
    {
        config(["ai.providers.{$provider}.key" => $key]);
        app(AiManager::class)->forgetInstance($provider);
    }

    private static function limit(string $text): string
    {
        if (mb_strlen($text) > self::MAX_INPUT) {
            throw new AiWritingException('That text is too long for one request. Select a shorter part and try again.');
        }

        return $text;
    }
}
