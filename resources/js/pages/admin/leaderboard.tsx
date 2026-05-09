import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';

type LeaderboardUser = {
    id: number;
    name: string;
    username: string;
    email: string;
    total_points: number;
    discipline_score: string | number;
    current_streak: number;
    longest_streak: number;
};

export default function AdminLeaderboard({ sort, users }: { sort: string; users: LeaderboardUser[] }) {
    return (
        <AppLayout breadcrumbs={[{ title: 'Leaderboard', href: '/admin/leaderboard' }]}>
            <Head title="Leaderboard" />
            <div className="p-4">
                <div className="rounded-xl border p-4">
                    <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <div className="text-lg font-semibold">Leaderboard</div>
                            <div className="text-muted-foreground text-sm">Top 50 users (excluding suspended).</div>
                        </div>

                        <form method="get" className="flex items-center gap-2">
                            <div className="text-muted-foreground text-sm">Sort by</div>
                            <select name="sort" defaultValue={sort} className="rounded-md border bg-transparent px-3 py-2 text-sm">
                                <option value="total_points">Total points</option>
                                <option value="discipline_score">Discipline score</option>
                                <option value="current_streak">Current streak</option>
                            </select>
                            <button type="submit" className="rounded-md border px-3 py-2 text-sm font-medium hover:bg-muted">
                                Apply
                            </button>
                        </form>
                    </div>

                    <div className="mt-4 overflow-hidden rounded-lg border">
                        <table className="min-w-full divide-y">
                            <thead className="bg-muted/50">
                                <tr>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">#</th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">User</th>
                                    <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">Points</th>
                                    <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">Discipline</th>
                                    <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">Streak</th>
                                    <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">Longest</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {users.map((user, idx) => (
                                    <tr key={user.id} className="hover:bg-muted/30">
                                        <td className="px-4 py-3 text-sm text-muted-foreground">{idx + 1}</td>
                                        <td className="px-4 py-3">
                                            <div className="text-sm font-semibold">{user.name}</div>
                                            <div className="text-muted-foreground text-xs">
                                                @{user.username} • {user.email}
                                            </div>
                                        </td>
                                        <td className="px-4 py-3 text-right text-sm">{user.total_points}</td>
                                        <td className="px-4 py-3 text-right text-sm">{Number(user.discipline_score).toFixed(1)}</td>
                                        <td className="px-4 py-3 text-right text-sm">{user.current_streak}</td>
                                        <td className="px-4 py-3 text-right text-sm">{user.longest_streak}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

