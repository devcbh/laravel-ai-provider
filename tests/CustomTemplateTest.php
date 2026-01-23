<?php

namespace Devcbh\LaravelAiProvider\Tests;

use PHPUnit\Framework\TestCase;
use Devcbh\LaravelAiProvider\Templates\CustomTemplate;

class CustomTemplateTest extends TestCase
{
    /** @test */
    public function it_can_render_string_prompts()
    {
        $template = new CustomTemplate(
            'System Prompt',
            'User Prompt for {subject}'
        );

        $this->assertEquals('System Prompt', $template->systemPrompt());
        $this->assertEquals('User Prompt for Laravel', $template->userPrompt(['subject' => 'Laravel']));
    }

    /** @test */
    public function it_can_render_closure_prompts()
    {
        $template = new CustomTemplate(
            fn() => 'Dynamic System',
            fn($data) => "Dynamic User: " . ($data['value'] * 2)
        );

        $this->assertEquals('Dynamic System', $template->systemPrompt());
        $this->assertEquals('Dynamic User: 20', $template->userPrompt(['value' => 10]));
    }

    /** @test */
    public function it_can_handle_arrays_in_string_templates()
    {
        $template = new CustomTemplate(
            'System',
            'Data: {data}'
        );

        $data = ['a' => 1, 'b' => 2];
        $expected = 'Data: ' . json_encode($data);

        $this->assertEquals($expected, $template->userPrompt(['data' => $data]));
    }
}
