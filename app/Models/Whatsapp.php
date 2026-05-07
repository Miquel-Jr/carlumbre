<?php

namespace App\Models;

use App\Core\Whatsapp as CoreWhatsapp;

class Whatsapp
{
    protected string $businessPhone;
    protected string $countryCode;

    public function __construct()
    {
        $config = CoreWhatsapp::config();
        $this->businessPhone = $config['business_phone'] ?? '';
        $this->countryCode = $config['country_code'] ?? '51';
    }

    public function normalizePhoneNumber(string $number): string
    {
        $digits = preg_replace('/\D+/', '', $number);

        if ($digits === '') {
            return '';
        }

        $digits = ltrim($digits, '0');

        if ($this->countryCode !== '' && str_starts_with($digits, $this->countryCode)) {
            return $digits;
        }

        return $this->countryCode . $digits;
    }

    public function buildChatUrl(string $message, string $numberTo): array
    {
        $normalizedNumber = $this->normalizePhoneNumber($numberTo);

        if ($normalizedNumber === '') {
            return [
                'success' => false,
                'error' => 'Número de teléfono inválido.',
            ];
        }

        return [
            'success' => true,
            'phone_number' => $normalizedNumber,
            'url' => 'https://web.whatsapp.com/send?phone=' . $normalizedNumber . '&text=' . rawurlencode($message),
        ];
    }

    public function getBusinessPhone(): string
    {
        return $this->businessPhone;
    }

    public function getBusinessPhoneDisplay(): string
    {
        if ($this->businessPhone === '') {
            return '';
        }

        return '+' . $this->businessPhone;
    }

    public function buildBusinessChatUrl(string $message = ''): string
    {
        if ($this->businessPhone === '') {
            return '#';
        }

        $url = 'https://web.whatsapp.com/send?phone=' . $this->businessPhone;

        if ($message !== '') {
            $url .= '&text=' . rawurlencode($message);
        }

        return $url;
    }
}
