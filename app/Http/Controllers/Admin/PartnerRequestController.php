<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PartnerRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\AccountabilityPartnerRequest;
use App\Models\User;
use App\Services\AccountabilityPartnerService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PartnerRequestController extends Controller
{
    public function __construct(
        private AccountabilityPartnerService $partnerService,
    ) {}

    public function index(Request $request): Response
    {
        $statuses = array_map(
            fn (PartnerRequestStatus $status): string => $status->value,
            PartnerRequestStatus::cases(),
        );

        $status = (string) $request->query('status', 'all');

        if ($status !== 'all' && ! in_array($status, $statuses, true)) {
            $status = 'all';
        }

        $search = trim((string) $request->query('search', ''));

        $partnerRequests = AccountabilityPartnerRequest::query()
            ->with([
                'goal:id,title,user_id,accountability_partner_id',
                'partner:id,name,username,email',
                'requester:id,name,username,email',
            ])
            ->when($status !== 'all', fn (Builder $query): Builder => $query->where('status', $status))
            ->when($search !== '', function (Builder $query) use ($search): Builder {
                return $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->whereHas('goal', fn (Builder $query): Builder => $query->where('title', 'like', "%{$search}%"))
                        ->orWhereHas('requester', fn (Builder $query): Builder => $this->searchUser($query, $search))
                        ->orWhereHas('partner', fn (Builder $query): Builder => $this->searchUser($query, $search));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (AccountabilityPartnerRequest $partnerRequest): array => [
                'id' => $partnerRequest->id,
                'status' => $partnerRequest->status->value,
                'goal' => [
                    'id' => $partnerRequest->goal->id,
                    'title' => $partnerRequest->goal->title,
                ],
                'requester' => $this->userPayload($partnerRequest->requester),
                'partner' => $this->userPayload($partnerRequest->partner),
                'created_at' => $partnerRequest->created_at?->toIso8601String(),
                'responded_at' => $partnerRequest->responded_at?->toIso8601String(),
            ]);

        return Inertia::render('admin/partner-requests', [
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
            'requests' => $partnerRequests,
            'summary' => [
                'total' => AccountabilityPartnerRequest::count(),
                'pending' => AccountabilityPartnerRequest::where('status', PartnerRequestStatus::Pending->value)->count(),
                'accepted' => AccountabilityPartnerRequest::where('status', PartnerRequestStatus::Accepted->value)->count(),
                'declined' => AccountabilityPartnerRequest::where('status', PartnerRequestStatus::Declined->value)->count(),
            ],
        ]);
    }

    public function accept(AccountabilityPartnerRequest $partnerRequest): RedirectResponse
    {
        if ($partnerRequest->status !== PartnerRequestStatus::Pending) {
            return back()->withErrors([
                'partner_request' => 'Only pending partner requests can be accepted.',
            ]);
        }

        $this->partnerService->acceptRequest($partnerRequest);

        return back()->with('success', 'Partner request accepted.');
    }

    public function decline(AccountabilityPartnerRequest $partnerRequest): RedirectResponse
    {
        if ($partnerRequest->status !== PartnerRequestStatus::Pending) {
            return back()->withErrors([
                'partner_request' => 'Only pending partner requests can be declined.',
            ]);
        }

        $this->partnerService->declineRequest($partnerRequest);

        return back()->with('success', 'Partner request declined.');
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
