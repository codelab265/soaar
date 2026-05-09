<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SubscriptionStatus;
use App\Enums\SubscriptionTier;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService,
    ) {}

    public function index(Request $request): Response
    {
        $tiers = array_map(fn (SubscriptionTier $tier): string => $tier->value, SubscriptionTier::cases());
        $statuses = array_map(fn (SubscriptionStatus $status): string => $status->value, SubscriptionStatus::cases());

        $tier = (string) $request->query('tier', 'all');
        if ($tier !== 'all' && ! in_array($tier, $tiers, true)) {
            $tier = 'all';
        }

        $status = (string) $request->query('status', 'all');
        if ($status !== 'all' && ! in_array($status, $statuses, true)) {
            $status = 'all';
        }

        $search = trim((string) $request->query('search', ''));

        $subscriptions = Subscription::query()
            ->with(['user:id,name,username,email'])
            ->when($tier !== 'all', fn (Builder $query): Builder => $query->where('tier', $tier))
            ->when($status !== 'all', fn (Builder $query): Builder => $query->where('status', $status))
            ->when($search !== '', fn (Builder $query): Builder => $query->whereHas('user', fn (Builder $q): Builder => $this->searchUser($q, $search)))
            ->latest('starts_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Subscription $subscription): array => [
                'id' => $subscription->id,
                'tier' => $subscription->tier->value,
                'status' => $subscription->status->value,
                'price_mwk' => $subscription->price_mwk,
                'starts_at' => $subscription->starts_at?->toIso8601String(),
                'ends_at' => $subscription->ends_at?->toIso8601String(),
                'cancelled_at' => $subscription->cancelled_at?->toIso8601String(),
                'user' => $this->userPayload($subscription->user),
            ]);

        return Inertia::render('admin/subscriptions', [
            'filters' => [
                'search' => $search,
                'tier' => $tier,
                'status' => $status,
            ],
            'subscriptions' => $subscriptions,
            'summary' => [
                'total' => Subscription::count(),
                'active' => Subscription::where('status', SubscriptionStatus::Active->value)->count(),
                'cancelled' => Subscription::where('status', SubscriptionStatus::Cancelled->value)->count(),
                'expired' => Subscription::where('status', SubscriptionStatus::Expired->value)->count(),
                'premium' => Subscription::where('tier', SubscriptionTier::Premium->value)->count(),
            ],
        ]);
    }

    public function cancel(Subscription $subscription): RedirectResponse
    {
        $this->subscriptionService->cancel($subscription);

        return back()->with('success', 'Subscription cancelled.');
    }

    public function renew(Subscription $subscription): RedirectResponse
    {
        $this->subscriptionService->renew($subscription);

        return back()->with('success', 'Subscription renewed.');
    }

    private function searchUser(Builder $query, string $search): Builder
    {
        return $query
            ->where('name', 'like', "%{$search}%")
            ->orWhere('username', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%");
    }

    /**
     * @return array{id: int, name: string, username: string|null, email: string}
     */
    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
        ];
    }
}
