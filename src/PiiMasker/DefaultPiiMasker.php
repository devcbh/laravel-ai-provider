<?php

namespace Devcbh\LaravelAiProvider\PiiMasker;

use Devcbh\LaravelAiProvider\Contracts\PiiMasker;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class DefaultPiiMasker implements PiiMasker
{
    protected array $maskedValues = [];
    protected array $patterns = [];

    public function __construct(protected array $config = [])
    {
        $this->patterns = $this->config['patterns'] ?? [];

        if (isset($this->config['custom_patterns'])) {
            $this->patterns = array_merge($this->patterns, $this->config['custom_patterns']);
        }
    }

    public function mask(string $text): string
    {
        foreach ($this->patterns as $type => $pattern) {
            $text = preg_replace_callback($pattern, function ($matches) use ($type) {
                $originalValue = $matches[0];
                
                if (! ($this->config['unmasking']['enabled'] ?? true)) {
                    return $this->config['replacements'][$type] ?? "[MASKED " . strtoupper($type) . "]";
                }

                $id = Str::random(16);
                $placeholder = "[MASKED_" . strtoupper($type) . "_" . $id . "]";
                
                $this->maskedValues[$placeholder] = Crypt::encryptString($originalValue);
                
                return $placeholder;
            }, $text);
        }

        return $text;
    }

    public function unmask(string $text): string
    {
        foreach ($this->maskedValues as $placeholder => $encryptedValue) {
            $text = str_replace($placeholder, Crypt::decryptString($encryptedValue), $text);
        }

        return $text;
    }

    public function scrub(string $text): string
    {
        foreach ($this->patterns as $type => $pattern) {
            $text = preg_replace_callback($pattern, function ($matches) use ($type) {
                return $this->config['replacements'][$type] ?? "[REDACTED " . strtoupper($type) . "]";
            }, $text);
        }

        return $text;
    }

    public function extend(array $patterns): self
    {
        $this->patterns = array_merge($this->patterns, $patterns);

        return $this;
    }
}
