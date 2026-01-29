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

    /**
     * @param Message[] $messages
     * @param array $options
     * @return iterable
     */
    public function stream(array $messages, array $options = []): iterable;

    /**
     * @param Message[] $messages
     * @param array $options
     * @return array
     */
    public function prepareRequest(array $messages, array $options = []): array;

    /**
     * @param mixed $response
     * @return string
     */
    public function parseResponse(mixed $response): string;
}
