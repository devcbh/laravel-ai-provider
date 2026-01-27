<?php

namespace Devcbh\LaravelAiProvider\Contracts;

interface PiiMasker
{
    /**
     * Mask PII in the given text.
     *
     * @param string $text
     * @return string
     */
    public function mask(string $text): string;

    /**
     * Unmask PII in the given text.
     *
     * @param string $text
     * @return string
     */
    public function unmask(string $text): string;

    /**
     * Add or override PII patterns.
     *
     * @param array $patterns
     * @return self
     */
    public function extend(array $patterns): self;
}
