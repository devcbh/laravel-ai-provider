<?php

namespace Devcbh\LaravelAiProvider\Templates;

use Devcbh\LaravelAiProvider\Contracts\Template;

class TranslationTemplate implements Template
{
    public function systemPrompt(): string
    {
        return "You are a professional translator. Translate the provided text accurately while maintaining the context, cultural nuances, and original formatting.";
    }

    public function userPrompt(array $data): string
    {
        $text = $data['text'] ?? '';
        $targetLanguage = $data['target_language'] ?? 'English';
        return "Translate the following text to {$targetLanguage}: \"{$text}\"";
    }
}
