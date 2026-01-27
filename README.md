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
LARAVEL_AURA_AI_DRIVER=openai

LARAVEL_AURA_OPENAI_API_KEY=your-api-key
LARAVEL_AURA_GEMINI_API_KEY=your-api-key
LARAVEL_AURA_CLAUDE_API_KEY=your-api-key
LARAVEL_AURA_MISTRAL_API_KEY=your-api-key
LARAVEL_AURA_OLLAMA_BASE_URL=http://localhost:11434
```

## Usage

### Simple Question

```php
use Devcbh\LaravelAiProvider\Facades\Ai;

$response = Ai::ask('What is the capital of France?');
```

### JSON Response

You can get the AI response as a modifiable JSON object (PHP array). This is useful for structured data.

```php
$data = Ai::asJson('Return a list of 3 fruits in JSON format with "name" and "color" keys.');

// Returns: ['fruits' => [['name' => 'Apple', 'color' => 'Red'], ...]]
```

### Structured Output with Schema

You can define a custom JSON schema for the AI response. **Currently supported by OpenAI, Gemini, Mistral, Ollama and Claude drivers.**

```php
$schema = [
    'type' => 'object',
    'properties' => [
        'name' => ['type' => 'string'],
        'age' => ['type' => 'integer'],
        'hobbies' => [
            'type' => 'array',
            'items' => ['type' => 'string']
        ]
    ],
    'required' => ['name', 'age', 'hobbies']
];

// Basic usage (default name: 'response_schema')
$data = Ai::schema($schema)->asJson('Tell me about a person named John.');

// With custom schema name (used by OpenAI and Mistral for strict mode)
$data = Ai::schema($schema, 'person_info')->asJson('Tell me about a person named John.');

// Returns: ['name' => 'John', 'age' => 30, 'hobbies' => ['Reading', 'Cycling']]
```

### Using a Specific Driver

You can switch drivers fluently:

```php
$response = Ai::driver('gemini')->ask('Hello Gemini!');

// You can also chain other methods
$data = Ai::driver('mistral')
    ->schema($schema)
    ->asJson('Extract information.');
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

### PII Masking

The package provides built-in PII (Personally Identifiable Information) masking to ensure sensitive data is not sent to AI providers.

#### Basic Usage

You can enable PII masking per request:

```php
$response = Ai::withPiiMasking()->ask('My email is john.doe@example.com');
```

Or enable it globally in `config/ai.php`:

```php
'pii_masking' => [
    'enabled' => true,
    // ...
],
```

#### Customizing Patterns

The package comes with several default patterns (email, phone, ssn, ipv4, ipv6, mac_address, iban, jwt, aws_access_key, aws_secret_key, api_key, passport_number, credit_card, private_key). You can modify these or add your own in `config/ai.php`:

```php
'pii_masking' => [
    'patterns' => [
        'order_id' => '/ORD-\d{5}/',
    ],
],
```

Or dynamically at runtime:

```php
use Devcbh\LaravelAiProvider\Contracts\PiiMasker;

app(PiiMasker::class)->extend([
    'custom_id' => '/ID-[A-Z]{3}-\d{4}/',
]);
```

#### Custom Replacements

If unmasking is disabled, you can define custom replacement strings for specific PII types in `config/ai.php`:

```php
'pii_masking' => [
    'unmasking' => [
        'enabled' => false,
    ],
    'replacements' => [
        'email' => '[REDACTED EMAIL]',
        'phone' => '[REDACTED PHONE]',
    ],
],
```

## Zero Liability & AI Data Handling

This package is designed with a **Zero Liability** philosophy. We provide tools to ensure that sensitive data never leaves your infrastructure and that your AI interactions are secure and compliant.

### PII Masking & Redaction

By default, PII masking is enabled. This automatically detects and masks sensitive information (like emails, credit cards, API keys) before sending them to any AI provider.

```php
// Automatically masks PII and unmasks it in the response
$response = Ai::ask('My email is john@example.com');
```

#### Strict Mode (Irreversible Redaction)

For maximum security, you can enable **Strict Mode**. This irrevertibly redacts (scrubs) PII from your prompts, ensuring that even if the AI provider logs the data, no sensitive information is present.

```php
// Irreversibly scrubs PII from the prompt
$response = Ai::scrubPii()->ask('My secret key is sk_1234567890');
```

You can also enable this globally in `config/ai.php`:

```php
'pii_masking' => [
    'enabled' => true,
    'strict' => true, // Enforce irreversible redaction
],
```

### Open Source & Zero Liability

This project is open-source under the MIT License and includes a specific [AI Disclaimer](DISCLAIMER.md). We believe in transparency and security. By using this package, you maintain full control over your data handling logic.

- **Local Processing**: PII detection and masking happen entirely on your server.
- **No Data Retention**: This package does not store or log your prompts or AI responses.
- **Provider Agnostic**: Easily switch to local providers like Ollama for 100% data sovereignty.

### AI Aware Data Handling

The package is "AI Aware", meaning it understands the risks associated with sending data to LLMs. It provides:
- **Context Management**: Tools to manage conversation history safely.
- **Structured Output Enforcement**: Ensures AI responses adhere to strict JSON schemas, reducing the risk of "prompt injection" or unexpected data formats in your application.
- **Smart PII Patterns**: Pre-configured patterns for common sensitive data types, which are processed before the AI ever sees the prompt.

## Supported Drivers

| Driver | JSON Response | Custom Schema | PII Masking |
| :--- | :---: | :---: | :---: |
| `openai` | ✅ | ✅ | ✅ |
| `gemini` | ✅ | ✅ | ✅ |
| `claude` | ✅ | ✅ | ✅ |
| `mistral` | ✅ | ✅ | ✅ |
| `ollama` | ✅ | ✅ | ✅ |

## License

The MIT License (MIT). Please see [License File](LICENSE.md) and [Disclaimer](DISCLAIMER.md) for more information.
