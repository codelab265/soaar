<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\PayChangu\PayChanguClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class PayChanguMetaController extends Controller
{
    public function __construct(public PayChanguClient $payChangu) {}

    public function mobileMoneyOperators(): JsonResponse
    {
        $operators = Cache::remember('paychangu:mobile-money-operators', now()->addHours(12), function (): array {
            $json = $this->payChangu->mobileMoneyOperators();

            /** @var array<int, array<string, mixed>> $data */
            $data = is_array(data_get($json, 'data')) ? data_get($json, 'data') : [];

            return collect($data)
                ->filter(fn ($o): bool => is_array($o))
                ->map(fn ($o): array => [
                    'id' => data_get($o, 'id'),
                    'name' => (string) data_get($o, 'name', ''),
                    'ref_id' => (string) data_get($o, 'ref_id', ''),
                    'short_code' => (string) data_get($o, 'short_code', ''),
                    'currency' => (string) data_get($o, 'supported_country.currency', ''),
                ])
                ->filter(fn (array $o): bool => $o['name'] !== '' && $o['ref_id'] !== '')
                ->values()
                ->all();
        });

        return response()->json([
            'data' => $operators,
        ]);
    }
}
