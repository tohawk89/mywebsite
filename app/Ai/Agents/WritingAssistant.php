<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Rewrites a piece of the admin's writing (grammar fixes, clearer wording, tone changes) or
 * proposes titles. The text to work on is the prompt; the reply is the new text only.
 */
class WritingAssistant implements Agent
{
    use Promptable;

    /**
     * Action key => [menu label, instruction].
     *
     * @var array<string, array{0: string, 1: string}>
     */
    public const ACTIONS = [
        'fix' => ['Fix grammar and spelling', 'Correct grammar, spelling and punctuation. Change as little as possible and keep the wording, meaning and voice.'],
        'improve' => ['Improve writing', 'Make the text clearer and smoother to read. Keep the meaning, facts and the author\'s voice.'],
        'shorter' => ['Make shorter', 'Make the text noticeably shorter while keeping the key points.'],
        'longer' => ['Make longer', 'Expand the text with a little more detail in the same voice. Do not invent facts, names, numbers or links.'],
        'casual' => ['Make casual', 'Rewrite the text in a relaxed, friendly, conversational tone.'],
        'professional' => ['Make professional', 'Rewrite the text in a clear, polished, professional tone.'],
    ];

    public function __construct(public string $action, public string $style = '') {}

    public function instructions(): Stringable|string
    {
        $task = $this->action === 'titles'
            ? 'Suggest 3 short, engaging titles for the text. Reply with exactly 3 lines, one title per line, with no numbering, quotes or extra text.'
            : self::ACTIONS[$this->action][1].' Reply with the rewritten text only, with no introduction, explanation or quotes.';

        return implode("\n\n", array_filter([
            'You help the owner of a personal website edit their posts. '.$task,
            'Write in the same language as the text (for example, keep Malay as Malay and English as English); never translate it. '
                .'Keep Markdown formatting, links, code, names and emoji as they are.',
            $this->style !== '' ? 'The owner\'s preferred writing style: '.$this->style : null,
        ]));
    }
}
