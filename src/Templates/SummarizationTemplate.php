<?php

namespace Devcbh\LaravelAiProvider\Templates;

use Devcbh\LaravelAiProvider\Contracts\Template;

class SummarizationTemplate implements Template
{
    public function systemPrompt(): string
    {
        return "You are a professional editor. Summarize the provided content into a concise version while retaining all key information and the original tone.";
    }

    public function userPrompt(array $data): string
    {
        $content = $data['content'] ?? '';
        $maxLength = $data['max_length'] ?? '3 sentences';
        return "Summarize the following content in about {$maxLength}: \"{$content}\"";
    }
}
