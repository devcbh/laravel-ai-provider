<?php

namespace Devcbh\LaravelAiProvider\Contracts;

interface Template
{
    /**
     * Get the system role/instruction for the template.
     *
     * @return string
     */
    public function systemPrompt(): string;

    /**
     * Format the user prompt with the given data.
     *
     * @param array $data
     * @return string
     */
    public function userPrompt(array $data): string;
}
