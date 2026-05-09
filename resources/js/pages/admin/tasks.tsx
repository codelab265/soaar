import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { StatCard } from '@/components/stat-card';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, router } from '@inertiajs/react';
import { CircleCheck, CircleDashed, CircleHelp, CircleX, ListChecks } from 'lucide-react';

type TaskStatus = 'pending' | 'in_progress' | 'completed' | 'missed';
type TaskDifficulty = 'simple' | 'medium' | 'hard';

type AdminUser = {
    id: number;
    name: string;
    username: string | null;
    email: string;
};

type AdminTask = {
    id: number;
    title: string;
    difficulty: TaskDifficulty;
    status: TaskStatus;
    points_value: number;
    effective_points: number;
    scheduled_date: string | null;
    completed_at: string | null;
    objective: { id: number; title: string };
    goal: { id: number; title: string };
    owner: AdminUser;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedTasks = {
    data: AdminTask[];
    links: PaginationLink[];
    from: number | null;
    to: number | null;
    total: number;
};

type Props = {
    filters: {
        search: string;
        status: 'all' | TaskStatus;
        difficulty: 'all' | TaskDifficulty;
    };
    tasks: PaginatedTasks;
    summary: {
        total: number;
        pending: number;
        in_progress: number;
        completed: number;
        missed: number;
    };
};

const statusLabels: Record<TaskStatus, string> = {
    pending: 'Pending',
    in_progress: 'In progress',
    completed: 'Completed',
    missed: 'Missed',
};

const difficultyLabels: Record<TaskDifficulty, string> = {
    simple: 'Simple',
    medium: 'Medium',
    hard: 'Hard',
};

function statusVariant(status: TaskStatus) {
    if (status === 'completed') {
        return 'default';
    }

    if (status === 'missed') {
        return 'destructive';
    }

    return 'secondary';
}

function paginationLabel(label: string) {
    return label.replace('&laquo; Previous', 'Previous').replace('Next &raquo;', 'Next');
}

export default function AdminTasks({ filters, tasks, summary }: Props) {
    const markMissed = (task: AdminTask) => {
        router.post(`/admin/tasks/${task.id}/miss`, {}, { preserveScroll: true });
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Tasks', href: '/admin/tasks' }]}>
            <Head title="Tasks" />

            <div className="space-y-4 p-4">
                <div className="grid gap-4 md:grid-cols-5">
                    <StatCard title="Total tasks" value={summary.total} icon={ListChecks} tone="brand" />
                    <StatCard title="Pending" value={summary.pending} icon={CircleDashed} tone="warning" />
                    <StatCard title="In progress" value={summary.in_progress} icon={CircleHelp} tone="neutral" />
                    <StatCard title="Completed" value={summary.completed} icon={CircleCheck} tone="success" />
                    <StatCard title="Missed" value={summary.missed} icon={CircleX} tone="danger" />
                </div>

                <div className="rounded-xl border p-4">
                    <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                        <div>
                            <div className="text-lg font-semibold">Tasks</div>
                            <div className="text-muted-foreground text-sm">Browse tasks across the platform.</div>
                        </div>

                        <form method="get" className="grid gap-2 md:grid-cols-[240px_180px_160px_auto_auto]">
                            <Input name="search" defaultValue={filters.search} placeholder="Search task, objective, goal, owner" />
                            <select name="status" defaultValue={filters.status} className="rounded-md border bg-background px-3 py-2 text-sm">
                                <option value="all">All statuses</option>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In progress</option>
                                <option value="completed">Completed</option>
                                <option value="missed">Missed</option>
                            </select>
                            <select name="difficulty" defaultValue={filters.difficulty} className="rounded-md border bg-background px-3 py-2 text-sm">
                                <option value="all">All difficulty</option>
                                <option value="simple">Simple</option>
                                <option value="medium">Medium</option>
                                <option value="hard">Hard</option>
                            </select>
                            <Button type="submit" variant="outline">
                                Filter
                            </Button>
                            <Button asChild variant="ghost">
                                <Link href="/admin/tasks">Reset</Link>
                            </Button>
                        </form>
                    </div>

                    <div className="mt-4 overflow-hidden rounded-lg border">
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y">
                                <thead className="bg-muted/50">
                                    <tr>
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Task
                                        </th>
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Objective / Goal
                                        </th>
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Owner
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Points
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {tasks.data.length > 0 ? (
                                        tasks.data.map((task) => (
                                            <tr key={task.id} className="hover:bg-muted/30">
                                                <td className="px-4 py-3">
                                                    <div className="flex flex-col gap-2">
                                                        <div className="text-sm font-semibold">{task.title}</div>
                                                        <div className="flex flex-wrap gap-2">
                                                            <Badge variant={statusVariant(task.status)}>{statusLabels[task.status]}</Badge>
                                                            <Badge variant="outline">{difficultyLabels[task.difficulty]}</Badge>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3 text-sm">
                                                    <div className="font-medium">{task.objective.title}</div>
                                                    <div className="text-muted-foreground">{task.goal.title}</div>
                                                </td>
                                                <td className="px-4 py-3">
                                                    <UserBlock user={task.owner} />
                                                </td>
                                                <td className="px-4 py-3 text-right text-sm">
                                                    <div>{task.effective_points}</div>
                                                    <div className="text-muted-foreground text-xs">base {task.points_value}</div>
                                                </td>
                                                <td className="px-4 py-3">
                                                    {task.status !== 'completed' && task.status !== 'missed' ? (
                                                        <div className="flex justify-end">
                                                            <Button type="button" size="sm" variant="destructive" onClick={() => markMissed(task)}>
                                                                Mark missed
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
                                                No tasks match the current filters.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div className="mt-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div className="text-muted-foreground text-sm">
                            Showing {tasks.from ?? 0} to {tasks.to ?? 0} of {tasks.total} tasks
                        </div>

                        <div className="flex flex-wrap gap-2">
                            {tasks.links.map((link, index) =>
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

