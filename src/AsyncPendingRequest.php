<?php

namespace Devcbh\LaravelAiProvider;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Pool;

class AsyncPendingRequest
{
    /** @var PendingRequest[] */
    protected array $requests = [];

    public function __construct(protected AiManager $manager) {}

    public function add(string $key, PendingRequest $request): self
    {
        $this->requests[$key] = $request;
        return $this;
    }

    public function ask(array $prompts): array
    {
        foreach ($prompts as $key => $prompt) {
            $request = $this->manager->driver();
            $request->addMessage('user', $prompt);
            $this->add($key, $request);
        }

        return $this->execute();
    }

    public function execute(): array
    {
        $responses = Http::pool(function (Pool $pool) {
            $promises = [];
            foreach ($this->requests as $key => $request) {
                $driver = $request->getDriver();
                $prepared = $driver->prepareRequest($request->getMessages(), $request->getOptions());
                
                $httpRequest = $pool->as($key);

                if (isset($prepared['token'])) {
                    $httpRequest->withToken($prepared['token']);
                }

                if (isset($prepared['headers'])) {
                    $httpRequest->withHeaders($prepared['headers']);
                }

                $promises[$key] = $httpRequest->post($prepared['url'], $prepared['payload']);
            }
            return $promises;
        });

        $results = [];
        foreach ($this->requests as $key => $request) {
            $response = $responses[$key];
            if ($response->failed()) {
                $results[$key] = "Error: " . $response->body();
            } else {
                $results[$key] = $request->getDriver()->parseResponse($response);
            }
        }

        return $results;
    }
}
