import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { StatCard } from '@/components/stat-card';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, router } from '@inertiajs/react';
import { CircleCheck, Clock, Flag, Layers, TriangleAlert, XCircle } from 'lucide-react';

type GoalStatus = 'active' | 'pending_verification' | 'verified_completed' | 'cancelled' | 'expired';

type AdminUser = {
    id: number;
    name: string;
    username: string | null;
    email: string;
};

type AdminGoal = {
    id: number;
    title: string;
    status: GoalStatus;
    deadline: string | null;
    user: AdminUser;
    accountability_partner: AdminUser | null;
    created_at: string | null;
    updated_at: string | null;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedGoals = {
    data: AdminGoal[];
    links: PaginationLink[];
    from: number | null;
    to: number | null;
    total: number;
};

type Props = {
    filters: {
        search: string;
        status: 'all' | GoalStatus;
    };
    goals: PaginatedGoals;
    summary: {
        total: number;
        active: number;
        pending_verification: number;
        verified_completed: number;
        cancelled: number;
        expired: number;
    };
};

const statusLabels: Record<GoalStatus, string> = {
    active: 'Active',
    pending_verification: 'Pending verification',
    verified_completed: 'Verified completed',
    cancelled: 'Cancelled',
    expired: 'Expired',
};

function statusVariant(status: GoalStatus) {
    if (status === 'verified_completed') {
        return 'default';
    }

    if (status === 'expired') {
        return 'destructive';
    }

    if (status === 'cancelled') {
        return 'outline';
    }

    if (status === 'pending_verification') {
        return 'secondary';
    }

    return 'secondary';
}

function paginationLabel(label: string) {
    return label.replace('&laquo; Previous', 'Previous').replace('Next &raquo;', 'Next');
}

function formatDate(value: string | null) {
    if (!value) {
        return '—';
    }

    return new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
    }).format(new Date(value));
}

export default function AdminGoals({ filters, goals, summary }: Props) {
    const postAction = (goal: AdminGoal, action: 'approve' | 'reject') => {
        router.post(`/admin/goals/${goal.id}/${action}`, {}, { preserveScroll: true });
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Goals', href: '/admin/goals' }]}>
            <Head title="Goals" />

            <div className="space-y-4 p-4">
                <div className="grid gap-4 md:grid-cols-6">
                    <StatCard title="Total goals" value={summary.total} icon={Layers} tone="brand" />
                    <StatCard title="Active" value={summary.active} icon={Flag} tone="success" />
                    <StatCard title="Pending" value={summary.pending_verification} icon={Clock} tone="warning" />
                    <StatCard title="Verified" value={summary.verified_completed} icon={CircleCheck} tone="success" />
                    <StatCard title="Cancelled" value={summary.cancelled} icon={XCircle} tone="neutral" />
                    <StatCard title="Expired" value={summary.expired} icon={TriangleAlert} tone="danger" />
                </div>

                <div className="rounded-xl border p-4">
                    <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                        <div>
                            <div className="text-lg font-semibold">Goals</div>
                            <div className="text-muted-foreground text-sm">Browse and moderate goals across the platform.</div>
                        </div>

                        <form method="get" className="grid gap-2 md:grid-cols-[240px_200px_auto_auto]">
                            <Input name="search" defaultValue={filters.search} placeholder="Search title, owner, partner" />
                            <select name="status" defaultValue={filters.status} className="rounded-md border bg-background px-3 py-2 text-sm">
                                <option value="all">All statuses</option>
                                <option value="active">Active</option>
                                <option value="pending_verification">Pending verification</option>
                                <option value="verified_completed">Verified completed</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="expired">Expired</option>
                            </select>
                            <Button type="submit" variant="outline">
                                Filter
                            </Button>
                            <Button asChild variant="ghost">
                                <Link href="/admin/goals">Reset</Link>
                            </Button>
                        </form>
                    </div>

                    <div className="mt-4 overflow-hidden rounded-lg border">
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y">
                                <thead className="bg-muted/50">
                                    <tr>
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Goal
                                        </th>
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Owner
                                        </th>
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Partner
                                        </th>
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Deadline
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {goals.data.length > 0 ? (
                                        goals.data.map((goal) => (
                                            <tr key={goal.id} className="hover:bg-muted/30">
                                                <td className="px-4 py-3">
                                                    <div className="flex flex-col gap-2">
                                                        <div className="text-sm font-semibold">{goal.title}</div>
                                                        <div>
                                                            <Badge variant={statusVariant(goal.status)}>{statusLabels[goal.status]}</Badge>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3">
                                                    <UserBlock user={goal.user} />
                                                </td>
                                                <td className="px-4 py-3">
                                                    {goal.accountability_partner ? (
                                                        <UserBlock user={goal.accountability_partner} />
                                                    ) : (
                                                        <div className="text-sm text-muted-foreground">None</div>
                                                    )}
                                                </td>
                                                <td className="px-4 py-3 text-sm">{formatDate(goal.deadline)}</td>
                                                <td className="px-4 py-3">
                                                    {goal.status === 'pending_verification' ? (
                                                        <div className="flex justify-end gap-2">
                                                            <Button type="button" size="sm" variant="outline" onClick={() => postAction(goal, 'approve')}>
                                                                Approve
                                                            </Button>
                                                            <Button type="button" size="sm" variant="destructive" onClick={() => postAction(goal, 'reject')}>
                                                                Reject
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
                                            <td colSpan={5} className="px-4 py-8 text-center text-sm text-muted-foreground">
                                                No goals match the current filters.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div className="mt-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div className="text-muted-foreground text-sm">
                            Showing {goals.from ?? 0} to {goals.to ?? 0} of {goals.total} goals
                        </div>

                        <div className="flex flex-wrap gap-2">
                            {goals.links.map((link, index) =>
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

function UserBlock({ user }: { user: AdminUser }) {
    return (
        <div>
            <div className="text-sm font-semibold">{user.name}</div>
            <div className="text-muted-foreground text-xs">
                @{user.username ?? 'unknown'} / {user.email}
            </div>
        </div>
    );
}

