# Laravel AI Provider

A simple and intuitive Laravel wrapper for multiple AI providers (OpenAI, Gemini, Claude/Anthropic, Mistral, Ollama).

## Installation

You can install the package via composer:

```bash
composer require devcbh/laravel-ai-provider
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="ai-config"
```

## Configuration

Add your API keys to your `.env` file:

```env
AI_DRIVER=openai

OPENAI_API_KEY=your-api-key
GEMINI_API_KEY=your-api-key
CLAUDE_API_KEY=your-api-key
MISTRAL_API_KEY=your-api-key
OLLAMA_BASE_URL=http://localhost:11434
```

## Usage

### Simple Question

```php
use Devcbh\LaravelAiProvider\Facades\Ai;

$response = Ai::ask('What is the capital of France?');
```

### Using a Specific Driver

```php
$response = Ai::driver('gemini')->ask('Hello Gemini!');
```

### Fluent Configuration

```php
$response = Ai::model('gpt-4')
    ->temperature(0.9)
    ->role('You are a helpful assistant.')
    ->ask('Tell me a joke.');
```

### With Context (Last Messages)

```php
use Devcbh\LaravelAiProvider\DTOs\Message;

$response = Ai::lastContext([
    Message::user('My name is Junie.'),
    Message::assistant('Hello Junie! How can I help you today?'),
])->ask('What is my name?');
```

### Using Templates

Templates allow you to use predefined prompts for common tasks.

```php
use Devcbh\LaravelAiProvider\Facades\Ai;
use Devcbh\LaravelAiProvider\Templates\PredictionTemplate;

$response = Ai::template(new PredictionTemplate(), [
    'data' => [10, 20, 30, 40],
    'target' => 'the next number in the sequence'
])->ask('Analyze and predict.');
```

#### Available Templates

Below is a list of available templates and the data they expect:

1.  **`PredictionTemplate`**: For data predictions.
    *   `data`: (array) Historical data points.
    *   `target`: (string) What to predict (e.g., "next month sales").
2.  **`GrowthTemplate`**: For growth strategies and metrics analysis.
    *   `metrics`: (array) Current growth metrics.
    *   `period`: (string) Timeframe (e.g., "Q4 2023").
3.  **`AnalyticsTemplate`**: For deep-dive data analysis and insights.
    *   `data`: (array) Raw data for analysis.
    *   `context`: (string) Context of the data (e.g., "user behavior").
4.  **`SentimentTemplate`**: For analyzing text sentiment.
    *   `text`: (string) The text to analyze.
5.  **`SummarizationTemplate`**: For content summarization.
    *   `content`: (string) The content to summarize.
    *   `max_length`: (string) Desired length (e.g., "3 paragraphs").
6.  **`TranslationTemplate`**: For multi-language translation.
    *   `text`: (string) The text to translate.
    *   `target_language`: (string) The language to translate into.
7.  **`CodeReviewTemplate`**: For reviewing code snippets.
    *   `code`: (string) The code snippet.
    *   `language`: (string) Programming language (e.g., "PHP").
8.  **`KeywordExtractionTemplate`**: For extracting SEO keywords.
    *   `text`: (string) The text to process.
    *   `limit`: (int) Maximum number of keywords to extract.
9.  **`RecommendationTemplate`**: For personalized recommendations.
    *   `preferences`: (array) User preferences or history.
    *   `options`: (array) Available items to recommend from.
10. **`FraudDetectionTemplate`**: For identifying suspicious patterns.
    *   `data`: (array) Transaction or activity data.

### Creating Your Own Templates

You can create custom templates in two ways:

#### 1. implementing the `Template` Interface

Create a class that implements `Devcbh\LaravelAiProvider\Contracts\Template`:

```php
namespace App\AiTemplates;

use Devcbh\LaravelAiProvider\Contracts\Template;

class MyCustomTemplate implements Template
{
    public function systemPrompt(): string
    {
        return "You are a helpful assistant that speaks like a pirate.";
    }

    public function userPrompt(array $data): string
    {
        return "Tell me about {$data['subject']}.";
    }
}
```

Usage:
```php
Ai::template(new MyCustomTemplate(), ['subject' => 'the ocean'])->ask('Go!');
```

#### 2. Using the `CustomTemplate` Class

For quick, dynamic templates, use the `CustomTemplate` class:

```php
use Devcbh\LaravelAiProvider\Templates\CustomTemplate;

$template = new CustomTemplate(
    'You are a math tutor.',
    'Explain the concept of {concept} to a {level} level student.'
);

$response = Ai::template($template, [
    'concept' => 'calculus',
    'level' => 'beginner'
])->ask('Start explaining.');
```

You can also use closures for more complex logic:

```php
$template = new CustomTemplate(
    fn() => 'System role logic here',
    fn($data) => "User prompt with " . count($data) . " items."
);
```

## Supported Drivers

- `openai`
- `gemini` (Google)
- `claude` or `anthropic`
- `mistral`
- `ollama` (Local AI)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
