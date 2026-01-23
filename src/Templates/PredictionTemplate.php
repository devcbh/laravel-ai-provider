<?php

namespace Devcbh\LaravelAiProvider\Templates;

use Devcbh\LaravelAiProvider\Contracts\Template;

class PredictionTemplate implements Template
{
    public function systemPrompt(): string
    {
        return "You are an expert data scientist specializing in predictive modeling. Analyze the provided historical data and provide a logical prediction for future outcomes. Include a confidence level and key factors influencing the prediction.";
    }

    public function userPrompt(array $data): string
    {
        $historicalData = json_encode($data['data'] ?? []);
        $target = $data['target'] ?? 'the next value';
        return "Based on this historical data: {$historicalData}, what is your prediction for {$target}?";
    }
}
