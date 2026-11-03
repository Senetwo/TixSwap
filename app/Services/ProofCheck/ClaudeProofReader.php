<?php

namespace App\Services\ProofCheck;

use Anthropic\Client;
use Anthropic\Core\Exceptions\AnthropicException;

/**
 * Reads proofs of purchase with Claude (vision + structured outputs).
 */
class ClaudeProofReader implements ProofReader
{
    private const SYSTEM = <<<'TXT'
        You help the review team of TixSwap, a ticket resale marketplace in Kenya, check that a seller really owns the ticket they are listing.
        You will see one proof of purchase: a ticket, e-ticket, or purchase receipt/confirmation (photo, screenshot or PDF).
        Report only what is actually visible. Use null for anything not shown; never guess or infer missing details.
        For edit_signs, list only concrete visual evidence of editing or fabrication. Ordinary photo blur, glare or cropping is not editing.
        TXT;

    public function __construct(
        private readonly Client $client,
        private readonly string $model,
    ) {}

    public function name(): string
    {
        return $this->model;
    }

    public function read(string $contents, string $mimeType): ProofReading
    {
        $data = base64_encode($contents);

        $file = $mimeType === 'application/pdf'
            ? ['type' => 'document', 'source' => ['type' => 'base64', 'mediaType' => 'application/pdf', 'data' => $data]]
            : ['type' => 'image', 'source' => ['type' => 'base64', 'mediaType' => $mimeType, 'data' => $data]];

        try {
            $message = $this->client->beta->messages->create(
                model: $this->model,
                maxTokens: 16000,
                system: self::SYSTEM,
                messages: [[
                    'role' => 'user',
                    'content' => [$file, ['type' => 'text', 'text' => 'Extract the details from this proof of purchase.']],
                ]],
                // Simple extraction: low effort keeps it fast and cheap.
                outputConfig: [
                    'effort' => 'low',
                    'format' => ['type' => 'json_schema', 'schema' => ProofReading::schema()],
                ],
                // If the model declines, retry on Anthropic's recommended fallback.
                fallbacks: 'default',
                betas: ['server-side-fallback-2026-07-01'],
            );
        } catch (AnthropicException $e) {
            throw new ProofReadException('Claude API error: '.$e->getMessage(), previous: $e);
        }

        if ($message->stopReason === 'refusal') {
            throw new ProofReadException('The model declined to read this file.');
        }

        if ($message->stopReason === 'max_tokens') {
            throw new ProofReadException('The reading was cut off.');
        }

        foreach ($message->content as $block) {
            if ($block->type === 'text') {
                $decoded = json_decode($block->text, true);
                if (! is_array($decoded)) {
                    throw new ProofReadException('The model returned an unexpected response.');
                }

                return ProofReading::fromArray($decoded);
            }
        }

        throw new ProofReadException('The model returned no result.');
    }
}
