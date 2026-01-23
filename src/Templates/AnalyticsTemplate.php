<?php

namespace Devcbh\LaravelAiProvider\Templates;

use Devcbh\LaravelAiProvider\Contracts\Template;

class AnalyticsTemplate implements Template
{
    public function systemPrompt(): string
    {
        return "You are a senior data analyst. Your task is to extract meaningful insights from raw data, identify trends, and provide a summary of findings that can drive business decisions.";
    }

    public function userPrompt(array $data): string
    {
        $rawData = json_encode($data['data'] ?? []);
        $context = $data['context'] ?? 'general business operations';
        return "Perform a deep-dive analysis on this data related to {$context}: {$rawData}. What are the key takeaways?";
    }
}
