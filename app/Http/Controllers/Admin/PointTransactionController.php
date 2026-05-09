<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PointTransactionType;
use App\Http\Controllers\Controller;
use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PointTransactionController extends Controller
{
    public function index(Request $request): Response
    {
        $types = array_map(fn (PointTransactionType $type): string => $type->value, PointTransactionType::cases());

        $type = trim((string) $request->query('type', 'all'));
        if ($type === '') {
            $type = 'all';
        }
        if ($type !== 'all' && ! in_array($type, $types, true)) {
            $type = 'all';
        }

        $search = trim((string) $request->query('search', ''));

        $transactions = PointTransaction::query()
            ->with(['user:id,name,username,email'])
            ->when($type !== 'all', fn (Builder $query): Builder => $query->where('type', $type))
            ->when($search !== '', function (Builder $query) use ($search): Builder {
                return $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('description', 'like', "%{$search}%")
                        ->orWhereHas('user', fn (Builder $query): Builder => $this->searchUser($query, $search));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn (PointTransaction $transaction): array => [
                'id' => $transaction->id,
                'type' => $transaction->type->value,
                'points' => $transaction->points,
                'description' => $transaction->description,
                'user' => $this->userPayload($transaction->user),
                'created_at' => $transaction->created_at?->toIso8601String(),
            ]);

        return Inertia::render('admin/point-transactions', [
            'filters' => [
                'search' => $search,
                'type' => $type,
            ],
            'transactions' => $transactions,
            'summary' => [
                'total' => PointTransaction::count(),
                'awarded' => PointTransaction::where('points', '>', 0)->count(),
                'deducted' => PointTransaction::where('points', '<', 0)->count(),
            ],
        ]);
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
