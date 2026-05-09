import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { StatCard } from '@/components/stat-card';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, router } from '@inertiajs/react';
import { CircleCheck, CircleDashed, CirclePlay, Layers, ShieldCheck } from 'lucide-react';

type ObjectiveStatus = 'pending' | 'in_progress' | 'completed' | 'verified';

type AdminUser = {
    id: number;
    name: string;
    username: string | null;
    email: string;
};

type AdminObjective = {
    id: number;
    title: string;
    status: ObjectiveStatus;
    priority: number;
    tasks_count: number;
    goal: {
        id: number;
        title: string;
    };
    owner: AdminUser;
    created_at: string | null;
    updated_at: string | null;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedObjectives = {
    data: AdminObjective[];
    links: PaginationLink[];
    from: number | null;
    to: number | null;
    total: number;
};

type Props = {
    filters: {
        search: string;
        status: 'all' | ObjectiveStatus;
    };
    objectives: PaginatedObjectives;
    summary: {
        total: number;
        pending: number;
        in_progress: number;
        completed: number;
        verified: number;
    };
};

const statusLabels: Record<ObjectiveStatus, string> = {
    pending: 'Pending',
    in_progress: 'In progress',
    completed: 'Completed',
    verified: 'Verified',
};

function statusVariant(status: ObjectiveStatus) {
    if (status === 'verified') {
        return 'default';
    }

    if (status === 'completed') {
        return 'secondary';
    }

    return 'outline';
}

function paginationLabel(label: string) {
    return label.replace('&laquo; Previous', 'Previous').replace('Next &raquo;', 'Next');
}

export default function AdminObjectives({ filters, objectives, summary }: Props) {
    const verifyObjective = (objective: AdminObjective) => {
        router.post(`/admin/objectives/${objective.id}/verify`, {}, { preserveScroll: true });
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Objectives', href: '/admin/objectives' }]}>
            <Head title="Objectives" />

            <div className="space-y-4 p-4">
                <div className="grid gap-4 md:grid-cols-5">
                    <StatCard title="Total objectives" value={summary.total} icon={Layers} tone="brand" />
                    <StatCard title="Pending" value={summary.pending} icon={CircleDashed} tone="warning" />
                    <StatCard title="In progress" value={summary.in_progress} icon={CirclePlay} tone="neutral" />
                    <StatCard title="Completed" value={summary.completed} icon={CircleCheck} tone="success" />
                    <StatCard title="Verified" value={summary.verified} icon={ShieldCheck} tone="success" />
                </div>

                <div className="rounded-xl border p-4">
                    <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                        <div>
                            <div className="text-lg font-semibold">Objectives</div>
                            <div className="text-muted-foreground text-sm">Browse objectives across the platform.</div>
                        </div>

                        <form method="get" className="grid gap-2 md:grid-cols-[240px_200px_auto_auto]">
                            <Input name="search" defaultValue={filters.search} placeholder="Search objective, goal, owner" />
                            <select name="status" defaultValue={filters.status} className="rounded-md border bg-background px-3 py-2 text-sm">
                                <option value="all">All statuses</option>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In progress</option>
                                <option value="completed">Completed</option>
                                <option value="verified">Verified</option>
                            </select>
                            <Button type="submit" variant="outline">
                                Filter
                            </Button>
                            <Button asChild variant="ghost">
                                <Link href="/admin/objectives">Reset</Link>
                            </Button>
                        </form>
                    </div>

                    <div className="mt-4 overflow-hidden rounded-lg border">
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y">
                                <thead className="bg-muted/50">
                                    <tr>
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Objective
                                        </th>
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Goal
                                        </th>
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Owner
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Tasks
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Priority
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {objectives.data.length > 0 ? (
                                        objectives.data.map((objective) => (
                                            <tr key={objective.id} className="hover:bg-muted/30">
                                                <td className="px-4 py-3">
                                                    <div className="flex flex-col gap-2">
                                                        <div className="text-sm font-semibold">{objective.title}</div>
                                                        <div>
                                                            <Badge variant={statusVariant(objective.status)}>{statusLabels[objective.status]}</Badge>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3 text-sm">{objective.goal.title}</td>
                                                <td className="px-4 py-3">
                                                    <UserBlock user={objective.owner} />
                                                </td>
                                                <td className="px-4 py-3 text-right text-sm">{objective.tasks_count}</td>
                                                <td className="px-4 py-3 text-right text-sm">{objective.priority}</td>
                                                <td className="px-4 py-3">
                                                    {objective.status === 'completed' ? (
                                                        <div className="flex justify-end">
                                                            <Button type="button" size="sm" variant="outline" onClick={() => verifyObjective(objective)}>
                                                                Verify
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
                                                No objectives match the current filters.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div className="mt-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div className="text-muted-foreground text-sm">
                            Showing {objectives.from ?? 0} to {objectives.to ?? 0} of {objectives.total} objectives
                        </div>

                        <div className="flex flex-wrap gap-2">
                            {objectives.links.map((link, index) =>
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

