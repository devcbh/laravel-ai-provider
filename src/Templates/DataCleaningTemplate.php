<?php

namespace Devcbh\LaravelAiProvider\Templates;

use Devcbh\LaravelAiProvider\Contracts\Template;

class DataCleaningTemplate implements Template
{
    public function systemPrompt(): string
    {
        return "You are a data cleaning expert. Your task is to take messy user input and convert it into a clean, normalized format based on a provided schema. Ensure data types are correct and missing values are handled appropriately.";
    }

    public function userPrompt(array $data): string
    {
        $input = json_encode($data['input'] ?? '');
        $schema = json_encode($data['schema'] ?? []);

        return "Clean and normalize the following input data according to this schema: {$schema}.\n\nInput: {$input}";
    }
}
