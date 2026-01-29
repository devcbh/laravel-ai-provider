<?php

namespace Devcbh\LaravelAiProvider\Templates;

use Devcbh\LaravelAiProvider\Contracts\Template;

class CustomerSupportTriageTemplate implements Template
{
    public function systemPrompt(): string
    {
        return "You are a customer support triage specialist. Your goal is to categorize incoming support tickets, determine their priority, and suggest relevant internal documentation links to help resolve the issue quickly.";
    }

    public function userPrompt(array $data): string
    {
        $ticket = $data['ticket'] ?? '';
        $docs = isset($data['documentation']) ? json_encode($data['documentation']) : 'available internal resources';

        return "Categorize this support ticket and suggest helpful documentation from: {$docs}.\n\nTicket: \"{$ticket}\"";
    }
}
