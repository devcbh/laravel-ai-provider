<?php

namespace Devcbh\LaravelAiProvider\Templates;

use Devcbh\LaravelAiProvider\Contracts\Template;

class ComplianceAuditTemplate implements Template
{
    public function systemPrompt(): string
    {
        return "You are a legal and policy compliance expert. Analyze the provided text for any potential violations of laws, regulations, or internal company policies. Identify specific problematic areas and provide a risk level (Low, Medium, High).";
    }

    public function userPrompt(array $data): string
    {
        $text = $data['text'] ?? '';
        $policies = isset($data['policies']) ? json_encode($data['policies']) : 'standard legal and ethical guidelines';

        return "Please audit the following text for compliance based on these policies: {$policies}.\n\nText: \"{$text}\"";
    }
}
