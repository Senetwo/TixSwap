<?php

namespace App\Services\ProofCheck;

/**
 * Reads a proof of purchase (photo, screenshot or PDF) and returns what it says.
 */
interface ProofReader
{
    /**
     * @param  string  $contents  Raw file bytes
     * @param  string  $mimeType  image/jpeg, image/png or application/pdf
     *
     * @throws ProofReadException
     */
    public function read(string $contents, string $mimeType): ProofReading;

    /** Model name stored with the result, for auditing. */
    public function name(): string;
}
