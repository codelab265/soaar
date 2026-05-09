import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { StatCard } from '@/components/stat-card';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, router } from '@inertiajs/react';
import { Shield, UserCheck, UserMinus, Users } from 'lucide-react';

type AdminUserRow = {
    id: number;
    name: string;
    username: string | null;
    email: string;
    is_admin: boolean;
    total_points: number;
    discipline_score: string | number;
    current_streak: number;
    longest_streak: number;
    suspended_at: string | null;
    created_at: string | null;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedUsers = {
    data: AdminUserRow[];
    links: PaginationLink[];
    from: number | null;
    to: number | null;
    total: number;
};

type Props = {
    filters: {
        search: string;
        status: 'all' | 'active' | 'suspended';
    };
    users: PaginatedUsers;
    summary: {
        total: number;
        active: number;
        suspended: number;
        admins: number;
    };
};

function paginationLabel(label: string) {
    return label.replace('&laquo; Previous', 'Previous').replace('Next &raquo;', 'Next');
}

function toFixedScore(value: string | number) {
    const asNumber = typeof value === 'number' ? value : Number(value);
    if (Number.isNaN(asNumber)) {
        return String(value);
    }

    return asNumber.toFixed(1);
}

export default function AdminUsers({ filters, users, summary }: Props) {
    const suspend = (user: AdminUserRow) => {
        router.post(`/admin/users/${user.id}/suspend`, {}, { preserveScroll: true });
    };

    const unsuspend = (user: AdminUserRow) => {
        router.post(`/admin/users/${user.id}/unsuspend`, {}, { preserveScroll: true });
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Users', href: '/admin/users' }]}>
            <Head title="Users" />

            <div className="space-y-4 p-4">
                <div className="grid gap-4 md:grid-cols-4">
                    <StatCard title="Total users" value={summary.total} icon={Users} tone="brand" />
                    <StatCard title="Active" value={summary.active} icon={UserCheck} tone="success" />
                    <StatCard title="Suspended" value={summary.suspended} icon={UserMinus} tone="danger" />
                    <StatCard title="Admins" value={summary.admins} icon={Shield} tone="neutral" />
                </div>

                <div className="rounded-xl border p-4">
                    <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                        <div>
                            <div className="text-lg font-semibold">Users</div>
                            <div className="text-muted-foreground text-sm">Search users and manage suspension.</div>
                        </div>

                        <form method="get" className="grid gap-2 md:grid-cols-[280px_180px_auto_auto]">
                            <Input name="search" defaultValue={filters.search} placeholder="Search name, username, email" />
                            <select name="status" defaultValue={filters.status} className="rounded-md border bg-background px-3 py-2 text-sm">
                                <option value="all">All</option>
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                            </select>
                            <Button type="submit" variant="outline">
                                Filter
                            </Button>
                            <Button asChild variant="ghost">
                                <Link href="/admin/users">Reset</Link>
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
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Points
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Discipline
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Streak
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Status
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {users.data.length > 0 ? (
                                        users.data.map((user) => (
                                            <tr key={user.id} className="hover:bg-muted/30">
                                                <td className="px-4 py-3">
                                                    <div className="flex flex-col gap-1">
                                                        <div className="text-sm font-semibold">{user.name}</div>
                                                        <div className="text-muted-foreground text-xs">
                                                            @{user.username ?? 'unknown'} • {user.email}
                                                        </div>
                                                        {user.is_admin ? (
                                                            <div>
                                                                <Badge variant="outline">Admin</Badge>
                                                            </div>
                                                        ) : null}
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3 text-right text-sm">{user.total_points}</td>
                                                <td className="px-4 py-3 text-right text-sm">{toFixedScore(user.discipline_score)}</td>
                                                <td className="px-4 py-3 text-right text-sm">
                                                    <div>{user.current_streak}</div>
                                                    <div className="text-muted-foreground text-xs">longest {user.longest_streak}</div>
                                                </td>
                                                <td className="px-4 py-3 text-right">
                                                    {user.suspended_at ? <Badge variant="destructive">Suspended</Badge> : <Badge>Active</Badge>}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <div className="flex justify-end gap-2">
                                                        {user.suspended_at ? (
                                                            <Button type="button" size="sm" variant="outline" onClick={() => unsuspend(user)}>
                                                                Unsuspend
                                                            </Button>
                                                        ) : (
                                                            <Button type="button" size="sm" variant="destructive" onClick={() => suspend(user)}>
                                                                Suspend
                                                            </Button>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td colSpan={6} className="px-4 py-8 text-center text-sm text-muted-foreground">
                                                No users match the current filters.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div className="mt-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div className="text-muted-foreground text-sm">
                            Showing {users.from ?? 0} to {users.to ?? 0} of {users.total} users
                        </div>

                        <div className="flex flex-wrap gap-2">
                            {users.links.map((link, index) =>
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

