<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Suggests tags for a post, preferring tags the site already uses.
 */
class TagSuggester implements Agent, HasStructuredOutput
{
    use Promptable;

    /**
     * @param  array<int, string>  $existingTags
     */
    public function __construct(public array $existingTags = []) {}

    public function instructions(): Stringable|string
    {
        $existing = $this->existingTags === []
            ? 'The site has no tags yet.'
            : 'Tags the site already uses (prefer these when they fit): '.implode(', ', $this->existingTags).'.';

        return 'Suggest up to 5 short tags (one or two words each) that describe the post. '
            .$existing.' Only suggest a new tag when none of the existing ones fit. '
            .'Use the same language as the post and the same capitalisation style as the existing tags.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'tags' => $schema->array()->items($schema->string())->required(),
        ];
    }
}
