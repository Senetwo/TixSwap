<?php

namespace App\Services\ProofCheck;

/**
 * The details visible on a proof of purchase. Null = not visible.
 */
final class ProofReading
{
    /**
     * @param  list<string>  $editSigns
     */
    public function __construct(
        public readonly bool $isTicketOrReceipt,
        public readonly bool $readable,
        public readonly ?string $eventName = null,
        public readonly ?string $eventDate = null,
        public readonly ?string $venue = null,
        public readonly ?string $seatOrTicketType = null,
        public readonly ?float $pricePaid = null,
        public readonly ?string $currency = null,
        public readonly ?string $orderOrTicketNumber = null,
        public readonly ?string $vendor = null,
        public readonly ?string $buyerName = null,
        public readonly array $editSigns = [],
        public readonly ?string $notes = null,
    ) {}

    public static function fromArray(array $d): self
    {
        $str = fn ($v) => is_string($v) && trim($v) !== '' ? trim($v) : null;

        return new self(
            isTicketOrReceipt: (bool) ($d['is_ticket_or_receipt'] ?? false),
            readable: (bool) ($d['readable'] ?? false),
            eventName: $str($d['event_name'] ?? null),
            eventDate: $str($d['event_date'] ?? null),
            venue: $str($d['venue'] ?? null),
            seatOrTicketType: $str($d['seat_or_ticket_type'] ?? null),
            pricePaid: is_numeric($d['price_paid'] ?? null) ? (float) $d['price_paid'] : null,
            currency: $str($d['currency'] ?? null),
            orderOrTicketNumber: $str($d['order_or_ticket_number'] ?? null),
            vendor: $str($d['vendor'] ?? null),
            buyerName: $str($d['buyer_name'] ?? null),
            editSigns: array_values(array_filter(array_map($str, (array) ($d['edit_signs'] ?? [])))),
            notes: $str($d['notes'] ?? null),
        );
    }

    public function toArray(): array
    {
        return [
            'is_ticket_or_receipt' => $this->isTicketOrReceipt,
            'readable' => $this->readable,
            'event_name' => $this->eventName,
            'event_date' => $this->eventDate,
            'venue' => $this->venue,
            'seat_or_ticket_type' => $this->seatOrTicketType,
            'price_paid' => $this->pricePaid,
            'currency' => $this->currency,
            'order_or_ticket_number' => $this->orderOrTicketNumber,
            'vendor' => $this->vendor,
            'buyer_name' => $this->buyerName,
            'edit_signs' => $this->editSigns,
            'notes' => $this->notes,
        ];
    }

    /**
     * JSON schema for structured outputs (every field required; nullable via anyOf).
     */
    public static function schema(): array
    {
        $nullable = fn (string $type, string $description) => ['anyOf' => [['type' => $type], ['type' => 'null']], 'description' => $description];

        return [
            'type' => 'object',
            'properties' => [
                'is_ticket_or_receipt' => ['type' => 'boolean', 'description' => 'True if this is an event ticket, e-ticket or purchase receipt/confirmation.'],
                'readable' => ['type' => 'boolean', 'description' => 'True if the key details can be read.'],
                'event_name' => $nullable('string', 'Event name exactly as shown.'),
                'event_date' => $nullable('string', 'Event date as YYYY-MM-DD, if shown.'),
                'venue' => $nullable('string', 'Venue or location as shown.'),
                'seat_or_ticket_type' => $nullable('string', 'Seat, section, row, or ticket type (e.g. VIP, Regular).'),
                'price_paid' => $nullable('number', 'Price paid for ONE ticket, as a number without currency symbols.'),
                'currency' => $nullable('string', 'Currency code, e.g. KES.'),
                'order_or_ticket_number' => $nullable('string', 'Order, booking or ticket number/reference.'),
                'vendor' => $nullable('string', 'Who sold the original ticket (e.g. Ticketsasa, Mookh, the organiser).'),
                'buyer_name' => $nullable('string', 'Name of the original purchaser, if shown.'),
                'edit_signs' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Concrete visual signs the image was edited or fabricated (mismatched fonts, misaligned text, cloned areas, cropped totals). Empty if none.'],
                'notes' => $nullable('string', 'Anything else a reviewer should know, in one short sentence.'),
            ],
            'required' => ['is_ticket_or_receipt', 'readable', 'event_name', 'event_date', 'venue', 'seat_or_ticket_type', 'price_paid', 'currency', 'order_or_ticket_number', 'vendor', 'buyer_name', 'edit_signs', 'notes'],
            'additionalProperties' => false,
        ];
    }
}
