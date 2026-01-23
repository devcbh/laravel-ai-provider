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

## Supported Drivers

- `openai`
- `gemini` (Google)
- `claude` or `anthropic`
- `mistral`
- `ollama` (Local AI)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
