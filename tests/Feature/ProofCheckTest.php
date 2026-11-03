<?php

namespace Tests\Feature;

use Anthropic\Client;
use App\Models\Event;
use App\Models\ProofCheck;
use App\Models\Ticket;
use App\Models\User;
use App\Services\ProofCheck\ClaudeProofReader;
use App\Services\ProofCheck\FakeProofReader;
use App\Services\ProofCheck\ProofCheckService;
use App\Services\ProofCheck\ProofReadException;
use App\Services\ProofCheck\ProofReading;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProofCheckTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('local');
        Storage::fake('public');
        FakeProofReader::$next = null;
    }

    private function listing(): Ticket
    {
        $event = Event::factory()->create(['name' => 'Blankets & Wine', 'date' => '2026-12-06 14:00:00']);
        Storage::disk('local')->put('purchase_proofs/p.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='));

        return Ticket::factory()->pending()->create([
            'event_id' => $event->id, 'seat_details' => 'VIP', 'face_value' => 4500, 'price' => 5000,
            'ticket_reference' => 'TKT48213AB', 'purchase_proof' => 'purchase_proofs/p.png',
        ]);
    }

    private function reading(array $overrides = []): ProofReading
    {
        return ProofReading::fromArray($overrides + [
            'is_ticket_or_receipt' => true, 'readable' => true, 'event_name' => 'BLANKETS AND WINE KENYA',
            'event_date' => '2026-12-06', 'venue' => 'Carnivore', 'seat_or_ticket_type' => 'VIP Ticket',
            'price_paid' => 4500, 'currency' => 'KES', 'order_or_ticket_number' => 'tkt-48213-ab',
            'vendor' => 'Ticketsasa', 'buyer_name' => 'S. Otieno', 'edit_signs' => [], 'notes' => null,
        ]);
    }

    public function test_matching_proof_looks_good(): void
    {
        [$verdict, $findings] = (new ProofCheckService(null))->compare($this->listing(), $this->reading());

        $this->assertSame(ProofCheck::VERDICT_LOOKS_GOOD, $verdict);
        $this->assertFalse(collect($findings)->contains('level', 'warning'));
    }

    public function test_mismatches_and_edit_signs_are_flagged(): void
    {
        [$verdict, $findings] = (new ProofCheckService(null))->compare($this->listing(), $this->reading([
            'event_name' => 'Sol Fest', 'event_date' => '2026-12-07', 'price_paid' => 1500,
            'order_or_ticket_number' => 'ZZZ999', 'seat_or_ticket_type' => 'Regular',
            'edit_signs' => ['The total uses a different font from the rest of the receipt'],
        ]));

        $text = collect($findings)->where('level', 'warning')->pluck('text')->implode("\n");
        $this->assertSame(ProofCheck::VERDICT_NEEDS_ATTENTION, $verdict);
        foreach (['Event name on proof is "Sol Fest"', 'Date on proof is', 'Price on proof is KES 1,500', 'Number on proof is "ZZZ999"', 'Proof shows "Regular"', 'Possible editing: The total uses a different font'] as $expected) {
            $this->assertStringContainsString($expected, $text);
        }
    }

    public function test_unreadable_proof(): void
    {
        [$verdict] = (new ProofCheckService(null))->compare($this->listing(), $this->reading(['readable' => false]));
        $this->assertSame(ProofCheck::VERDICT_UNREADABLE, $verdict);
    }

    public function test_new_listing_is_checked_after_the_response_when_enabled(): void
    {
        config(['services.ai_proof.driver' => 'fake']);
        FakeProofReader::$next = ProofReading::fromArray(['is_ticket_or_receipt' => true, 'readable' => true, 'event_name' => 'Sol Fest']);

        $this->actingAs(User::factory()->seller()->create())->post(route('seller.tickets.store'), [
            'event_name' => 'Sol Fest', 'location' => 'Nairobi', 'date' => now()->addWeek()->toDateTimeString(),
            'price' => 3000, 'face_value' => 3000, 'seat_details' => 'VIP',
            'event_image' => UploadedFile::fake()->image('e.jpg'),
            'purchase_proof' => UploadedFile::fake()->image('receipt.jpg'),
        ])->assertRedirect(route('seller.dashboard'));

        $check = ProofCheck::sole();
        $this->assertSame(ProofCheck::STATUS_DONE, $check->status);
        $this->assertSame(ProofCheck::VERDICT_LOOKS_GOOD, $check->verdict);
        $this->assertSame('fake', $check->model);
    }

    public function test_nothing_runs_when_switched_off(): void
    {
        config(['services.ai_proof.driver' => 'none']);
        $ticket = $this->listing();

        $this->actingAs(User::factory()->admin()->create())->get(route('admin.tickets.pending'))->assertDontSee('AI check of the proof');
        $this->actingAs(User::factory()->admin()->create())->post(route('admin.tickets.proof-check', $ticket))->assertSessionHas('error');
        $this->assertSame(0, ProofCheck::count());
    }

    public function test_admin_can_run_a_check_and_see_findings(): void
    {
        config(['services.ai_proof.driver' => 'fake']);
        FakeProofReader::$next = $this->reading(['price_paid' => 1500]);
        $ticket = $this->listing();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.tickets.proof-check', $ticket))->assertSessionHas('success');
        $this->actingAs($admin)->get(route('admin.tickets.pending'))
            ->assertSee('Needs a closer look')
            ->assertSee('Price on proof is KES 1,500');
    }

    public function test_claude_reader_sends_the_proof_with_structured_output_and_fallbacks(): void
    {
        $history = [];
        $stack = HandlerStack::create(new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'id' => 'msg_1', 'type' => 'message', 'role' => 'assistant', 'model' => 'claude-opus-5-5',
                'content' => [['type' => 'text', 'text' => json_encode($this->reading()->toArray())]],
                'stop_reason' => 'end_turn', 'stop_sequence' => null,
                'usage' => ['input_tokens' => 1200, 'output_tokens' => 150],
            ])),
        ]));
        $stack->push(Middleware::history($history));
        $client = new Client(apiKey: 'test-key', requestOptions: ['transporter' => new \GuzzleHttp\Client(['handler' => $stack]), 'maxRetries' => 0]);

        $reading = (new ClaudeProofReader($client, 'claude-opus-5-5'))->read('fake-png-bytes', 'image/png');

        $this->assertSame('BLANKETS AND WINE KENYA', $reading->eventName);
        $request = $history[0]['request'];
        $body = json_decode((string) $request->getBody(), true);
        $this->assertSame('/v1/messages', $request->getUri()->getPath());
        $this->assertStringContainsString('server-side-fallback-2026-07-01', $request->getHeaderLine('anthropic-beta'));
        $this->assertSame('claude-opus-5-5', $body['model']);
        $this->assertSame('default', $body['fallbacks']);
        $this->assertSame('low', $body['output_config']['effort']);
        $this->assertSame('json_schema', $body['output_config']['format']['type']);
        $this->assertSame(['type' => 'base64', 'media_type' => 'image/png', 'data' => base64_encode('fake-png-bytes')], $body['messages'][0]['content'][0]['source']);
    }

    public function test_claude_reader_reports_a_refusal(): void
    {
        $stack = HandlerStack::create(new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'id' => 'msg_2', 'type' => 'message', 'role' => 'assistant', 'model' => 'claude-opus-5-5',
                'content' => [], 'stop_reason' => 'refusal', 'stop_sequence' => null,
                'usage' => ['input_tokens' => 10, 'output_tokens' => 0],
            ])),
        ]));
        $client = new Client(apiKey: 'test-key', requestOptions: ['transporter' => new \GuzzleHttp\Client(['handler' => $stack]), 'maxRetries' => 0]);

        $this->expectException(ProofReadException::class);
        (new ClaudeProofReader($client, 'claude-opus-5-5'))->read('pdf-bytes', 'application/pdf');
    }
}
