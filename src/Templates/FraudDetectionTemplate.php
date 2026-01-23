<?php

namespace Devcbh\LaravelAiProvider\Templates;

use Devcbh\LaravelAiProvider\Contracts\Template;

class FraudDetectionTemplate implements Template
{
    public function systemPrompt(): string
    {
        return "You are a fraud detection specialist. Analyze the provided transaction or activity data for suspicious patterns, anomalies, and potential fraud. Provide a risk score and detailed reasoning.";
    }

    public function userPrompt(array $data): string
    {
        $activityData = json_encode($data['data'] ?? []);
        return "Analyze the following activity for potential fraud: {$activityData}.";
    }
}
