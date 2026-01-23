<?php

namespace Devcbh\LaravelAiProvider\Templates;

use Devcbh\LaravelAiProvider\Contracts\Template;

class CodeReviewTemplate implements Template
{
    public function systemPrompt(): string
    {
        return "You are an expert software engineer. Review the provided code snippet for best practices, security vulnerabilities, performance improvements, and readability. Provide specific suggestions for improvement.";
    }

    public function userPrompt(array $data): string
    {
        $code = $data['code'] ?? '';
        $language = $data['language'] ?? 'PHP';
        return "Review the following {$language} code:\n```{$language}\n{$code}\n```";
    }
}
