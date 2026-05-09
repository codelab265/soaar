<?php

namespace App\Services\PayChangu;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PayChanguClient
{
    private function request(): PendingRequest
    {
        $secretKey = config('paychangu.secret_key');

        if (! is_string($secretKey) || $secretKey === '') {
            throw new RuntimeException('PAYCHANGU_SECRET_KEY is not configured.');
        }

        /** @var string $baseUrl */
        $baseUrl = config('paychangu.base_url');

        return Http::baseUrl($baseUrl)
            ->acceptJson()
            ->asJson()
            ->withToken($secretKey)
            ->timeout(20)
            ->retry(2, 250);
    }

    /**
     * Standard / inline checkout: creates a hosted checkout session.
     *
     * @param  array{amount:string,currency:string,callback_url:string,return_url:string,tx_ref?:string,first_name?:string,last_name?:string,email?:string,customization?:array{title?:string,description?:string},meta?:array<string, mixed>}  $payload
     * @return array<string, mixed>
     */
    public function initiateTransaction(array $payload): array
    {
        return $this->request()
            ->post('/payment', $payload)
            ->throw()
            ->json();
    }

    /**
     * Standard / inline checkout verification by tx_ref.
     *
     * @return array<string, mixed>
     */
    public function verifyPayment(string $txRef): array
    {
        return $this->request()
            ->get("/verify-payment/{$txRef}")
            ->throw()
            ->json();
    }

    /**
     * @param  array{mobile:string,mobile_money_operator_ref_id:string,amount:string,charge_id:string,email?:string,first_name?:string,last_name?:string}  $payload
     * @return array<string, mixed>
     */
    public function initializeMobileMoney(array $payload): array
    {
        return $this->request()
            ->post('/mobile-money/payments/initialize', $payload)
            ->throw()
            ->json();
    }

    /**
     * @return array<string, mixed>
     */
    public function verifyMobileMoney(string $chargeId): array
    {
        return $this->request()
            ->get("/mobile-money/payments/{$chargeId}/verify")
            ->throw()
            ->json();
    }

    /**
     * List supported mobile money operators.
     *
     * @return array<string, mixed>
     */
    public function mobileMoneyOperators(): array
    {
        return $this->request()
            ->get('/mobile-money')
            ->throw()
            ->json();
    }

    /**
     * @param  array{payment_method:string,amount:string,currency:string,charge_id:string,email?:string,first_name?:string,last_name?:string,mobile?:string,create_permanent_account?:bool}  $payload
     * @return array<string, mixed>
     */
    public function initializeBankTransfer(array $payload): array
    {
        return $this->request()
            ->post('/direct-charge/payments/initialize', $payload)
            ->throw()
            ->json();
    }

    /**
     * @return array<string, mixed>
     */
    public function verifyBankTransfer(string $chargeId): array
    {
        return $this->request()
            ->get("/direct-charge/transactions/{$chargeId}/details")
            ->throw()
            ->json();
    }

    /**
     * Card direct charge (PCI DSS required). Kept behind feature flag.
     *
     * @param  array{card_number:string,expiry:string,cvv:string,cardholder_name:string,amount:string,currency:string,charge_id:string,redirect_url:string,email?:string}  $payload
     * @return array<string, mixed>
     */
    public function initializeCard(array $payload): array
    {
        if (! (bool) config('paychangu.card_enabled')) {
            throw new RuntimeException('PayChangu card direct charge is disabled (PAYCHANGU_CARD_ENABLED=false).');
        }

        return $this->request()
            ->post('/charge-card/payments', $payload)
            ->throw()
            ->json();
    }

    /**
     * @return array<string, mixed>
     */
    public function verifyCard(string $chargeId): array
    {
        if (! (bool) config('paychangu.card_enabled')) {
            throw new RuntimeException('PayChangu card direct charge is disabled (PAYCHANGU_CARD_ENABLED=false).');
        }

        return $this->request()
            ->get("/charge-card/verify/{$chargeId}")
            ->throw()
            ->json();
    }
}
