<?php

namespace Devcbh\LaravelAiProvider\Templates;

use Devcbh\LaravelAiProvider\Contracts\Template;

class KeywordExtractionTemplate implements Template
{
    public function systemPrompt(): string
    {
        return "You are an SEO and content specialist. Extract the most important keywords and phrases from the provided text. Categorize them by relevance and provide a brief explanation for each.";
    }

    public function userPrompt(array $data): string
    {
        $text = $data['text'] ?? '';
        $limit = $data['limit'] ?? 10;
        return "Extract up to {$limit} keywords from the following text: \"{$text}\"";
    }
}
