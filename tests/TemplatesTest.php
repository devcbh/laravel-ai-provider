<?php

namespace Devcbh\LaravelAiProvider\Tests;

use PHPUnit\Framework\TestCase;
use Devcbh\LaravelAiProvider\Templates\AnalyticsTemplate;
use Devcbh\LaravelAiProvider\Templates\CodeReviewTemplate;
use Devcbh\LaravelAiProvider\Templates\ComplianceAuditTemplate;
use Devcbh\LaravelAiProvider\Templates\CustomerSupportTriageTemplate;
use Devcbh\LaravelAiProvider\Templates\DataCleaningTemplate;
use Devcbh\LaravelAiProvider\Templates\FraudDetectionTemplate;
use Devcbh\LaravelAiProvider\Templates\GrowthTemplate;
use Devcbh\LaravelAiProvider\Templates\KeywordExtractionTemplate;
use Devcbh\LaravelAiProvider\Templates\PredictionTemplate;
use Devcbh\LaravelAiProvider\Templates\RecommendationTemplate;
use Devcbh\LaravelAiProvider\Templates\SentimentTemplate;
use Devcbh\LaravelAiProvider\Templates\SeoOptimizerTemplate;
use Devcbh\LaravelAiProvider\Templates\SummarizationTemplate;
use Devcbh\LaravelAiProvider\Templates\TranslationTemplate;

class TemplatesTest extends TestCase
{
    /** @test */
    public function analytics_template()
    {
        $template = new AnalyticsTemplate();
        $this->assertNotEmpty($template->systemPrompt());
        $userPrompt = $template->userPrompt(['data' => ['sales' => 100], 'context' => 'monthly sales']);
        $this->assertStringContainsString('monthly sales', $userPrompt);
        $this->assertStringContainsString('{"sales":100}', $userPrompt);
    }

    /** @test */
    public function code_review_template()
    {
        $template = new CodeReviewTemplate();
        $this->assertNotEmpty($template->systemPrompt());
        $userPrompt = $template->userPrompt(['code' => 'echo "hello";', 'language' => 'php']);
        $this->assertStringContainsString('echo "hello";', $userPrompt);
        $this->assertStringContainsString('php', $userPrompt);
    }

    /** @test */
    public function compliance_audit_template()
    {
        $template = new ComplianceAuditTemplate();
        $this->assertNotEmpty($template->systemPrompt());
        $userPrompt = $template->userPrompt(['text' => 'some content', 'regulation' => 'GDPR']);
        $this->assertStringContainsString('some content', $userPrompt);
    }

    /** @test */
    public function customer_support_triage_template()
    {
        $template = new CustomerSupportTriageTemplate();
        $this->assertNotEmpty($template->systemPrompt());
        $userPrompt = $template->userPrompt(['ticket' => 'Problem with login']);
        $this->assertStringContainsString('Problem with login', $userPrompt);
    }

    /** @test */
    public function data_cleaning_template()
    {
        $template = new DataCleaningTemplate();
        $this->assertNotEmpty($template->systemPrompt());
        $userPrompt = $template->userPrompt(['input' => 'raw data']);
        $this->assertStringContainsString('raw data', $userPrompt);
    }

    /** @test */
    public function fraud_detection_template()
    {
        $template = new FraudDetectionTemplate();
        $this->assertNotEmpty($template->systemPrompt());
        $userPrompt = $template->userPrompt(['data' => 'trans123']);
        $this->assertStringContainsString('trans123', $userPrompt);
    }

    /** @test */
    public function growth_template()
    {
        $template = new GrowthTemplate();
        $this->assertNotEmpty($template->systemPrompt());
        $userPrompt = $template->userPrompt(['metrics' => 'CPC: $1']);
        $this->assertStringContainsString('CPC: $1', $userPrompt);
    }

    /** @test */
    public function keyword_extraction_template()
    {
        $template = new KeywordExtractionTemplate();
        $this->assertNotEmpty($template->systemPrompt());
        $userPrompt = $template->userPrompt(['text' => 'sample text', 'limit' => 5]);
        $this->assertStringContainsString('sample text', $userPrompt);
        $this->assertStringContainsString('5', $userPrompt);
    }

    /** @test */
    public function prediction_template()
    {
        $template = new PredictionTemplate();
        $this->assertNotEmpty($template->systemPrompt());
        $userPrompt = $template->userPrompt(['data' => 'market crash']);
        $this->assertStringContainsString('market crash', $userPrompt);
    }

    /** @test */
    public function recommendation_template()
    {
        $template = new RecommendationTemplate();
        $this->assertNotEmpty($template->systemPrompt());
        $userPrompt = $template->userPrompt(['preferences' => 'likes sports', 'options' => 'football, tennis']);
        $this->assertStringContainsString('likes sports', $userPrompt);
        $this->assertStringContainsString('football, tennis', $userPrompt);
    }

    /** @test */
    public function sentiment_template()
    {
        $template = new SentimentTemplate();
        $this->assertNotEmpty($template->systemPrompt());
        $userPrompt = $template->userPrompt(['text' => 'happy']);
        $this->assertStringContainsString('happy', $userPrompt);
    }

    /** @test */
    public function seo_optimizer_template()
    {
        $template = new SeoOptimizerTemplate();
        $this->assertNotEmpty($template->systemPrompt());
        $userPrompt = $template->userPrompt(['content' => 'blog post', 'keywords' => 'ai, laravel']);
        $this->assertStringContainsString('blog post', $userPrompt);
        $this->assertStringContainsString('ai, laravel', $userPrompt);
    }

    /** @test */
    public function summarization_template()
    {
        $template = new SummarizationTemplate();
        $this->assertNotEmpty($template->systemPrompt());
        $userPrompt = $template->userPrompt(['content' => 'long text', 'max_length' => 'short']);
        $this->assertStringContainsString('long text', $userPrompt);
        $this->assertStringContainsString('short', $userPrompt);
    }

    /** @test */
    public function translation_template()
    {
        $template = new TranslationTemplate();
        $this->assertNotEmpty($template->systemPrompt());
        $userPrompt = $template->userPrompt(['text' => 'hello', 'target_language' => 'spanish']);
        $this->assertStringContainsString('hello', $userPrompt);
        $this->assertStringContainsString('spanish', $userPrompt);
    }
}
