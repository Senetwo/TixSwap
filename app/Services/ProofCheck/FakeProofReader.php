<?php

namespace App\Services\ProofCheck;

/**
 * Development stand-in: reports an unreadable file unless a test sets a reading.
 * Never calls an external service.
 */
class FakeProofReader implements ProofReader
{
    public static ?ProofReading $next = null;

    public function name(): string
    {
        return 'fake';
    }

    public function read(string $contents, string $mimeType): ProofReading
    {
        return self::$next ?? new ProofReading(isTicketOrReceipt: true, readable: false, notes: 'Simulated check (AI_PROOF_DRIVER=fake).');
    }
}
