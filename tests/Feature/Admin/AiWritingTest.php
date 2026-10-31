<?php

namespace Tests\Feature\Admin;

use App\Ai\Agents\TagSuggester;
use App\Ai\Agents\WritingAssistant;
use App\Livewire\Admin\PostForm;
use App\Livewire\Admin\SiteSettings;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\User;
use App\Support\AiWriting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Ai\Exceptions\RateLimitedException;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class AiWritingTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function enableAi(string $provider = 'anthropic'): void
    {
        Setting::set('ai_provider', $provider);
        Setting::set("ai_key_{$provider}", Crypt::encryptString('sk-test-key'));
    }

    private function editor(array $fields = []): Testable
    {
        $component = Livewire::actingAs($this->admin())->test(PostForm::class);

        foreach ($fields as $field => $value) {
            $component->set($field, $value);
        }

        return $component;
    }

    public function test_ai_settings_save_the_key_encrypted(): void
    {
        Livewire::actingAs($this->admin())->test(SiteSettings::class, ['section' => 'ai'])
            ->set('ai_provider', 'anthropic')
            ->set('ai_key_input', 'sk-ant-secret')
            ->set('ai_style', 'Casual and short.')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('ai_key_input', '')
            ->assertSet('ai_keys_saved.anthropic', true)
            ->assertDontSee('sk-ant-secret');

        $this->assertNotSame('sk-ant-secret', Setting::get('ai_key_anthropic'));
        $this->assertSame('sk-ant-secret', AiWriting::apiKey('anthropic'));
        $this->assertTrue(AiWriting::enabled());
    }

    public function test_saved_key_is_kept_when_the_field_is_blank_and_can_be_removed(): void
    {
        $this->enableAi();

        $component = Livewire::actingAs($this->admin())->test(SiteSettings::class, ['section' => 'ai'])
            ->call('save');
        $this->assertSame('sk-test-key', AiWriting::apiKey('anthropic'));

        $component->call('removeAiKey')->assertSet('ai_keys_saved.anthropic', false);
        $this->assertNull(AiWriting::apiKey('anthropic'));
        $this->assertFalse(AiWriting::enabled());
    }

    public function test_ai_settings_are_validated(): void
    {
        Livewire::actingAs($this->admin())->test(SiteSettings::class, ['section' => 'ai'])
            ->set('ai_provider', 'skynet')
            ->set('ai_model', 'bad model id!')
            ->call('save')
            ->assertHasErrors(['ai_provider', 'ai_model']);
    }

    public function test_model_comes_from_tier_or_custom_id(): void
    {
        $this->assertSame('claude-opus-5-5', AiWriting::model('anthropic', 'smartest', ''));
        $this->assertSame('claude-haiku-5-5', AiWriting::model('anthropic', 'cheapest', ''));
        $this->assertSame('my-custom-model', AiWriting::model('openai', 'smartest', 'my-custom-model'));
    }

    public function test_connection_test_reports_success_and_missing_keys(): void
    {
        WritingAssistant::fake(['Hello world.']);

        Livewire::actingAs($this->admin())->test(SiteSettings::class, ['section' => 'ai'])
            ->set('ai_provider', 'anthropic')
            ->call('testAi')
            ->assertSet('ai_test_result.ok', false)
            ->assertSet('ai_test_result.message', 'Add an API key first.')
            ->set('ai_key_input', 'sk-ant-new')
            ->call('testAi')
            ->assertSet('ai_test_result.ok', true)
            ->assertSet('ai_test_result.message', 'Connected. Requests will use claude-opus-5-5.');
    }

    public function test_ai_menu_only_shows_when_ai_is_set_up(): void
    {
        $this->editor()->assertDontSee('Fix grammar and spelling');

        $this->enableAi();

        $this->editor()
            ->assertSee('Fix grammar and spelling')
            ->assertSee('Suggest titles from content')
            ->assertSee('Suggest tags');
    }

    public function test_rewrite_is_reviewed_before_it_replaces_the_field(): void
    {
        $this->enableAi();
        WritingAssistant::fake(['I went to the market yesterday.']);

        $component = $this->editor(['content' => 'i goes to market yesterday'])
            ->call('aiRewrite', 'content', 'fix')
            ->assertSet('aiSuggestion.original', 'i goes to market yesterday')
            ->assertSet('aiSuggestion.result', 'I went to the market yesterday.')
            ->assertSet('content', 'i goes to market yesterday');

        $component->call('acceptAi')
            ->assertSet('content', 'I went to the market yesterday.')
            ->assertSet('aiSuggestion', null)
            ->assertDispatched('ai-applied', field: 'content', value: 'I went to the market yesterday.');

        WritingAssistant::assertPrompted('i goes to market yesterday');
    }

    public function test_rewrite_works_on_the_selected_text_only(): void
    {
        $this->enableAi();
        WritingAssistant::fake(['world']);

        $this->editor(['content' => 'Hello wrld. Second sentence stays.'])
            ->call('aiRewrite', 'content', 'fix', 6, 'wrld')
            ->assertSet('aiSuggestion.original', 'wrld')
            ->call('acceptAi')
            ->assertSet('content', 'Hello world. Second sentence stays.');
    }

    public function test_discard_and_retry(): void
    {
        $this->enableAi();
        WritingAssistant::fake(['First try.', 'Second try.']);

        $this->editor(['title' => 'my titel'])
            ->call('aiRewrite', 'title', 'fix')
            ->assertSet('aiSuggestion.result', 'First try.')
            ->call('retryAi')
            ->assertSet('aiSuggestion.result', 'Second try.')
            ->call('discardAi')
            ->assertSet('aiSuggestion', null)
            ->assertSet('title', 'my titel');
    }

    public function test_friendly_errors_when_ai_cannot_run(): void
    {
        $this->editor(['content' => 'Some text'])
            ->call('aiRewrite', 'content', 'fix')
            ->assertSet('aiError', 'AI writing is turned off. Set it up in Site settings > AI writing.');

        $this->enableAi();

        $this->editor()
            ->call('aiRewrite', 'content', 'fix')
            ->assertSet('aiError', 'Write something first, then ask AI to work on it.');

        WritingAssistant::fake(fn () => throw RateLimitedException::forProvider('anthropic'));

        $this->editor(['content' => 'Some text'])
            ->call('aiRewrite', 'content', 'fix')
            ->assertSet('aiError', 'The AI provider is rate limiting requests. Try again in a minute.')
            ->assertSet('content', 'Some text');
    }

    public function test_requests_are_rate_limited_per_admin(): void
    {
        $this->enableAi();
        WritingAssistant::fake(['ok']);
        $admin = $this->admin();
        RateLimiter::increment('ai-writing:'.$admin->id, 60, 20);

        Livewire::actingAs($admin)->test(PostForm::class)
            ->set('content', 'Some text')
            ->call('aiRewrite', 'content', 'fix')
            ->assertSet('aiSuggestion', null);

        $this->assertStringStartsWith('That\'s a lot of AI requests.', Livewire::actingAs($admin)->test(PostForm::class)
            ->set('content', 'Some text')->call('aiRewrite', 'content', 'fix')->get('aiError'));
        WritingAssistant::assertNeverPrompted();
    }

    public function test_title_ideas_can_be_picked(): void
    {
        $this->enableAi();
        WritingAssistant::fake(["1. A Day at the Zoo\n- Meeting the Goats\n\"Zoo Diaries\"\nA fourth one"]);

        $this->editor(['content' => 'We visited the mini zoo and fed the goats.'])
            ->call('suggestTitles')
            ->assertSet('aiTitles', ['A Day at the Zoo', 'Meeting the Goats', 'Zoo Diaries'])
            ->call('useTitle', 1)
            ->assertSet('title', 'Meeting the Goats')
            ->assertSet('aiTitles', []);
    }

    public function test_tag_suggestions_prefer_existing_tags_and_skip_ones_already_added(): void
    {
        $this->enableAi();
        Tag::create(['name' => 'Laravel', 'slug' => 'laravel']);
        TagSuggester::fake([['tags' => ['Laravel', 'PHP', '#Tips', 'php']]]);

        $this->editor(['title' => 'Testing in Laravel', 'tags' => 'PHP'])
            ->call('suggestTags')
            ->assertSet('aiTags', ['Laravel', 'Tips'])
            ->call('addSuggestedTag', 'Laravel')
            ->assertSet('tags', 'PHP, Laravel')
            ->assertSet('aiTags', ['Tips']);

        TagSuggester::assertPrompted(fn ($prompt) => str_contains($prompt->prompt, 'Testing in Laravel'));
    }

    public function test_writing_instructions_keep_the_language_and_style(): void
    {
        $instructions = (string) (new WritingAssistant('fix', 'Casual, first person.'))->instructions();

        $this->assertStringContainsString('never translate', $instructions);
        $this->assertStringContainsString('Casual, first person.', $instructions);
        $this->assertStringContainsString('Correct grammar', $instructions);
    }

    public function test_post_saves_normally_after_using_ai(): void
    {
        $this->enableAi();
        WritingAssistant::fake(['Polished content.']);

        $this->editor(['type' => 'blog', 'title' => 'Hello', 'content' => 'rough content'])
            ->call('aiRewrite', 'content', 'improve')
            ->call('acceptAi')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Polished content.', Post::first()->content);
    }
}
