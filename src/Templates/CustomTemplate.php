<?php

namespace Devcbh\LaravelAiProvider\Templates;

use Devcbh\LaravelAiProvider\Contracts\Template;
use Closure;

class CustomTemplate implements Template
{
    /**
     * @param string|Closure $systemPrompt
     * @param string|Closure $userPrompt
     */
    public function __construct(
        protected string|Closure $systemPrompt,
        protected string|Closure $userPrompt
    ) {}

    public function systemPrompt(): string
    {
        if ($this->systemPrompt instanceof Closure) {
            return ($this->systemPrompt)();
        }

        return $this->systemPrompt;
    }

    public function userPrompt(array $data): string
    {
        if ($this->userPrompt instanceof Closure) {
            return ($this->userPrompt)($data);
        }

        $prompt = $this->userPrompt;

        foreach ($data as $key => $value) {
            $value = is_array($value) ? json_encode($value) : $value;
            $prompt = str_replace("{{$key}}", $value, $prompt);
        }

        return $prompt;
    }
}
