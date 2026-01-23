<?php

namespace Devcbh\LaravelAiProvider\Templates;

use Devcbh\LaravelAiProvider\Contracts\Template;

class RecommendationTemplate implements Template
{
    public function systemPrompt(): string
    {
        return "You are a recommendation engine expert. Based on the user's preferences, history, and available options, provide a personalized list of recommendations with a brief reasoning for each.";
    }

    public function userPrompt(array $data): string
    {
        $preferences = json_encode($data['preferences'] ?? []);
        $options = json_encode($data['options'] ?? []);
        return "Based on these preferences: {$preferences}, recommend from the following options: {$options}.";
    }
}
