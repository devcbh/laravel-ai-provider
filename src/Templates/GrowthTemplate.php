<?php

namespace Devcbh\LaravelAiProvider\Templates;

use Devcbh\LaravelAiProvider\Contracts\Template;

class GrowthTemplate implements Template
{
    public function systemPrompt(): string
    {
        return "You are a growth strategist. Analyze the given metrics and identify growth opportunities, bottlenecks, and recommend actionable steps to accelerate growth.";
    }

    public function userPrompt(array $data): string
    {
        $metrics = json_encode($data['metrics'] ?? []);
        $period = $data['period'] ?? 'the last period';
        return "Analyze the following growth metrics for {$period}: {$metrics}. Provide a growth strategy.";
    }
}
