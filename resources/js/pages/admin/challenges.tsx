import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { StatCard } from '@/components/stat-card';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, router } from '@inertiajs/react';
import { CircleCheck, CirclePlay, Layers, XCircle } from 'lucide-react';

type ChallengeStatus = 'active' | 'completed' | 'cancelled';

type AdminChallenge = {
    id: number;
    title: string;
    duration_days: number;
    reward_points: number;
    status: ChallengeStatus;
    start_date: string | null;
    end_date: string | null;
    users_count: number;
    created_at: string | null;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedChallenges = {
    data: AdminChallenge[];
    links: PaginationLink[];
    from: number | null;
    to: number | null;
    total: number;
};

type Props = {
    filters: {
        search: string;
        status: 'all' | ChallengeStatus;
    };
    challenges: PaginatedChallenges;
    summary: {
        total: number;
        active: number;
        completed: number;
        cancelled: number;
    };
};

const statusLabels: Record<ChallengeStatus, string> = {
    active: 'Active',
    completed: 'Completed',
    cancelled: 'Cancelled',
};

function statusVariant(status: ChallengeStatus) {
    if (status === 'completed') {
        return 'default';
    }

    if (status === 'cancelled') {
        return 'destructive';
    }

    return 'secondary';
}

function paginationLabel(label: string) {
    return label.replace('&laquo; Previous', 'Previous').replace('Next &raquo;', 'Next');
}

export default function AdminChallenges({ filters, challenges, summary }: Props) {
    return (
        <AppLayout breadcrumbs={[{ title: 'Challenges', href: '/admin/challenges' }]}>
            <Head title="Challenges" />

            <div className="space-y-4 p-4">
                <div className="grid gap-4 md:grid-cols-4">
                    <StatCard title="Total" value={summary.total} icon={Layers} tone="brand" />
                    <StatCard title="Active" value={summary.active} icon={CirclePlay} tone="success" />
                    <StatCard title="Completed" value={summary.completed} icon={CircleCheck} tone="success" />
                    <StatCard title="Cancelled" value={summary.cancelled} icon={XCircle} tone="danger" />
                </div>

                <div className="rounded-xl border p-4">
                    <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                        <div>
                            <div className="text-lg font-semibold">Challenges</div>
                            <div className="text-muted-foreground text-sm">Browse challenges and participation counts.</div>
                        </div>

                        <div className="flex flex-wrap items-center gap-2">
                            <Button onClick={() => router.visit('/admin/challenges/create')}>New challenge</Button>

                        <form method="get" className="grid gap-2 md:grid-cols-[260px_200px_auto_auto]">
                            <Input name="search" defaultValue={filters.search} placeholder="Search title" />
                            <select name="status" defaultValue={filters.status} className="rounded-md border bg-background px-3 py-2 text-sm">
                                <option value="all">All statuses</option>
                                <option value="active">Active</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <Button type="submit" variant="outline">
                                Filter
                            </Button>
                            <Button asChild variant="ghost">
                                <Link href="/admin/challenges">Reset</Link>
                            </Button>
                        </form>
                        </div>
                    </div>

                    <div className="mt-4 overflow-hidden rounded-lg border">
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y">
                                <thead className="bg-muted/50">
                                    <tr>
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Challenge
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Users
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Reward
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Duration
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Dates
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Manage
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {challenges.data.length > 0 ? (
                                        challenges.data.map((challenge) => (
                                            <tr key={challenge.id} className="hover:bg-muted/30">
                                                <td className="px-4 py-3">
                                                    <div className="flex flex-col gap-2">
                                                        <div className="text-sm font-semibold">{challenge.title}</div>
                                                        <div>
                                                            <Badge variant={statusVariant(challenge.status)}>{statusLabels[challenge.status]}</Badge>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3 text-right text-sm">{challenge.users_count}</td>
                                                <td className="px-4 py-3 text-right text-sm">{challenge.reward_points}</td>
                                                <td className="px-4 py-3 text-right text-sm">{challenge.duration_days}d</td>
                                                <td className="px-4 py-3 text-right text-sm text-muted-foreground">
                                                    <div>{challenge.start_date ?? '—'}</div>
                                                    <div>{challenge.end_date ?? '—'}</div>
                                                </td>
                                                <td className="px-4 py-3 text-right">
                                                    <div className="flex flex-wrap justify-end gap-2">
                                                        <select
                                                            aria-label="Change status"
                                                            defaultValue={challenge.status}
                                                            onChange={(e) =>
                                                                router.post(
                                                                    `/admin/challenges/${challenge.id}/status`,
                                                                    { status: e.target.value },
                                                                    { preserveScroll: true },
                                                                )
                                                            }
                                                            className="h-9 rounded-md border bg-background px-3 text-sm"
                                                        >
                                                            <option value="active">Active</option>
                                                            <option value="completed">Completed</option>
                                                            <option value="cancelled">Cancelled</option>
                                                        </select>
                                                        <Button asChild size="sm" variant="outline">
                                                            <Link href={`/admin/challenges/${challenge.id}/edit`}>Edit</Link>
                                                        </Button>
                                                        <Button
                                                            size="sm"
                                                            variant="destructive"
                                                            onClick={() => {
                                                                if (!confirm('Delete this challenge?')) return;
                                                                router.delete(`/admin/challenges/${challenge.id}`, { preserveScroll: true });
                                                            }}
                                                        >
                                                            Delete
                                                        </Button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td colSpan={6} className="px-4 py-8 text-center text-sm text-muted-foreground">
                                                No challenges match the current filters.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div className="mt-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div className="text-muted-foreground text-sm">
                            Showing {challenges.from ?? 0} to {challenges.to ?? 0} of {challenges.total} challenges
                        </div>

                        <div className="flex flex-wrap gap-2">
                            {challenges.links.map((link, index) =>
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

