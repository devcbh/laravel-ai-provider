<?php

namespace Devcbh\LaravelAiProvider\Templates;

use Devcbh\LaravelAiProvider\Contracts\Template;

class SeoOptimizerTemplate implements Template
{
    public function systemPrompt(): string
    {
        return "You are an SEO specialist. Analyze the content of a blog post and generate optimized meta tags, a URL slug, and descriptive alt text for any images mentioned or described.";
    }

    public function userPrompt(array $data): string
    {
        $content = $data['content'] ?? '';
        $keywords = isset($data['keywords']) ? (is_array($data['keywords']) ? implode(', ', $data['keywords']) : $data['keywords']) : 'relevant SEO keywords';

        return "Analyze this blog post content and generate SEO meta tags, a slug, and image alt text, focusing on these keywords: {$keywords}.\n\nContent: \"{$content}\"";
    }
}
