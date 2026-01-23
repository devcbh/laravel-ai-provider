<?php

namespace Devcbh\LaravelAiProvider\Contracts;

use Devcbh\LaravelAiProvider\DTOs\Message;

interface Driver
{
    /**
     * @param Message[] $messages
     * @param array $options
     * @return string
     */
    public function chat(array $messages, array $options = []): string;
}
