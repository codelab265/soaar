<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePremium
{
    public function __construct(public SubscriptionService $subscriptions) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $this->subscriptions->isPremium($user)) {
            return response()->json([
                'message' => 'Premium subscription required.',
            ], 403);
        }

        return $next($request);
    }
}
