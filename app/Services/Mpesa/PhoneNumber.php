<?php

namespace App\Services\Mpesa;

final class PhoneNumber
{
    /**
     * Matches 0712345678, 712345678, 254712345678 and +254712345678
     * (Safaricom numbers start with 7 or 1 after the country code).
     */
    public const PATTERN = '/^(?:\+?254|0)?([17]\d{8})$/';

    /**
     * Remove the spaces, dashes and brackets people type in phone numbers.
     */
    public static function clean(?string $phone): string
    {
        return preg_replace('/[\s\-()]/', '', (string) $phone);
    }

    /**
     * Convert a Kenyan mobile number to the 2547XXXXXXXX format Daraja expects.
     */
    public static function toMsisdn(string $phone): ?string
    {
        $digits = self::clean($phone);

        if (! preg_match(self::PATTERN, $digits, $m)) {
            return null;
        }

        return '254'.$m[1];
    }

    /**
     * 254712345678 -> 0712 *** 678, for showing on screen.
     */
    public static function mask(string $msisdn): string
    {
        $local = '0'.substr($msisdn, 3);

        return substr($local, 0, 4).' *** '.substr($local, -3);
    }
}
