import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { StatCard } from '@/components/stat-card';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, router } from '@inertiajs/react';
import { Flame, Layers, Swords } from 'lucide-react';

type StreakType = 'daily' | 'challenge';

type AdminUser = {
    id: number;
    name: string;
    username: string | null;
    email: string;
};

type AdminStreak = {
    id: number;
    type: StreakType;
    current_count: number;
    longest_count: number;
    last_activity_date: string | null;
    started_at: string | null;
    user: AdminUser;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedStreaks = {
    data: AdminStreak[];
    links: PaginationLink[];
    from: number | null;
    to: number | null;
    total: number;
};

type Props = {
    filters: {
        search: string;
        type: 'all' | StreakType;
    };
    streaks: PaginatedStreaks;
    summary: {
        total: number;
        daily: number;
        challenge: number;
    };
};

function paginationLabel(label: string) {
    return label.replace('&laquo; Previous', 'Previous').replace('Next &raquo;', 'Next');
}

export default function AdminStreaks({ filters, streaks, summary }: Props) {
    const breakStreak = (streak: AdminStreak) => {
        router.post(`/admin/streaks/${streak.id}/break`, {}, { preserveScroll: true });
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Streaks', href: '/admin/streaks' }]}>
            <Head title="Streaks" />

            <div className="space-y-4 p-4">
                <div className="grid gap-4 md:grid-cols-3">
                    <StatCard title="Total" value={summary.total} icon={Layers} tone="brand" />
                    <StatCard title="Daily" value={summary.daily} icon={Flame} tone="success" />
                    <StatCard title="Challenge" value={summary.challenge} icon={Swords} tone="neutral" />
                </div>

                <div className="rounded-xl border p-4">
                    <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                        <div>
                            <div className="text-lg font-semibold">Streaks</div>
                            <div className="text-muted-foreground text-sm">Review streak state across users.</div>
                        </div>

                        <form method="get" className="grid gap-2 md:grid-cols-[280px_200px_auto_auto]">
                            <Input name="search" defaultValue={filters.search} placeholder="Search user" />
                            <select name="type" defaultValue={filters.type} className="rounded-md border bg-background px-3 py-2 text-sm">
                                <option value="all">All types</option>
                                <option value="daily">Daily</option>
                                <option value="challenge">Challenge</option>
                            </select>
                            <Button type="submit" variant="outline">
                                Filter
                            </Button>
                            <Button asChild variant="ghost">
                                <Link href="/admin/streaks">Reset</Link>
                            </Button>
                        </form>
                    </div>

                    <div className="mt-4 overflow-hidden rounded-lg border">
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y">
                                <thead className="bg-muted/50">
                                    <tr>
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            User
                                        </th>
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Type
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Current
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Longest
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Last activity
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {streaks.data.length > 0 ? (
                                        streaks.data.map((streak) => (
                                            <tr key={streak.id} className="hover:bg-muted/30">
                                                <td className="px-4 py-3">
                                                    <div className="text-sm font-semibold">{streak.user.name}</div>
                                                    <div className="text-muted-foreground text-xs">
                                                        @{streak.user.username ?? 'unknown'} / {streak.user.email}
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3 text-sm">
                                                    <Badge variant="outline">{streak.type}</Badge>
                                                </td>
                                                <td className="px-4 py-3 text-right text-sm">{streak.current_count}</td>
                                                <td className="px-4 py-3 text-right text-sm">{streak.longest_count}</td>
                                                <td className="px-4 py-3 text-right text-sm text-muted-foreground">
                                                    {streak.last_activity_date ?? '—'}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {streak.current_count > 0 ? (
                                                        <div className="flex justify-end">
                                                            <Button type="button" size="sm" variant="destructive" onClick={() => breakStreak(streak)}>
                                                                Break
                                                            </Button>
                                                        </div>
                                                    ) : (
                                                        <div className="text-right text-sm text-muted-foreground">No action</div>
                                                    )}
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td colSpan={6} className="px-4 py-8 text-center text-sm text-muted-foreground">
                                                No streaks match the current filters.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div className="mt-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div className="text-muted-foreground text-sm">
                            Showing {streaks.from ?? 0} to {streaks.to ?? 0} of {streaks.total} streaks
                        </div>

                        <div className="flex flex-wrap gap-2">
                            {streaks.links.map((link, index) =>
                                link.url ? (
                                    <Button key={`${link.label}-${index}`} asChild size="sm" variant={link.active ? 'default' : 'outline'}>
                                        <Link href={link.url} preserveScroll>
                                            {paginationLabel(link.label)}
                                        </Link>
                                    </Button>
                                ) : (
                                    <Button key={`${link.label}-${index}`} size="sm" variant="outline" disabled>
                                        {paginationLabel(link.label)}
                                    </Button>
                                ),
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

