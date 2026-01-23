<?php

namespace Devcbh\LaravelAiProvider\Templates;

use Devcbh\LaravelAiProvider\Contracts\Template;

class SentimentTemplate implements Template
{
    public function systemPrompt(): string
    {
        return "You are an expert in Natural Language Processing. Analyze the sentiment of the provided text. Categorize it as Positive, Negative, or Neutral, and provide a sentiment score between -1 and 1.";
    }

    public function userPrompt(array $data): string
    {
        $text = $data['text'] ?? '';
        return "Analyze the sentiment of the following text: \"{$text}\"";
    }
}
