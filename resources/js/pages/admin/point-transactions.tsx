import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { StatCard } from '@/components/stat-card';
import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import { Coins, Minus, Plus } from 'lucide-react';

type AdminUser = {
    id: number;
    name: string;
    username: string | null;
    email: string;
};

type AdminPointTransaction = {
    id: number;
    type: string;
    points: number;
    description: string;
    user: AdminUser;
    created_at: string | null;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedTransactions = {
    data: AdminPointTransaction[];
    links: PaginationLink[];
    from: number | null;
    to: number | null;
    total: number;
};

type Props = {
    filters: {
        search: string;
        type: 'all' | string;
    };
    transactions: PaginatedTransactions;
    summary: {
        total: number;
        awarded: number;
        deducted: number;
    };
};

function pointsVariant(points: number) {
    if (points < 0) {
        return 'destructive';
    }

    if (points > 0) {
        return 'default';
    }

    return 'secondary';
}

function paginationLabel(label: string) {
    return label.replace('&laquo; Previous', 'Previous').replace('Next &raquo;', 'Next');
}

export default function AdminPointTransactions({ filters, transactions, summary }: Props) {
    return (
        <AppLayout breadcrumbs={[{ title: 'Point Transactions', href: '/admin/point-transactions' }]}>
            <Head title="Point Transactions" />

            <div className="space-y-4 p-4">
                <div className="grid gap-4 md:grid-cols-3">
                    <StatCard title="Total" value={summary.total} icon={Coins} tone="brand" />
                    <StatCard title="Awarded" value={summary.awarded} icon={Plus} tone="success" />
                    <StatCard title="Deducted" value={summary.deducted} icon={Minus} tone="danger" />
                </div>

                <div className="rounded-xl border p-4">
                    <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                        <div>
                            <div className="text-lg font-semibold">Point Transactions</div>
                            <div className="text-muted-foreground text-sm">Audit points awarded and deducted across the platform.</div>
                        </div>

                        <form method="get" className="grid gap-2 md:grid-cols-[280px_220px_auto_auto]">
                            <Input name="search" defaultValue={filters.search} placeholder="Search description or user" />
                            <Input name="type" defaultValue={filters.type === 'all' ? '' : filters.type} placeholder="Type (e.g. task_completion)" />
                            <Button type="submit" variant="outline">
                                Filter
                            </Button>
                            <Button asChild variant="ghost">
                                <Link href="/admin/point-transactions">Reset</Link>
                            </Button>
                        </form>
                    </div>

                    <div className="mt-4 overflow-hidden rounded-lg border">
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y">
                                <thead className="bg-muted/50">
                                    <tr>
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Transaction
                                        </th>
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            User
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Points
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Type
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {transactions.data.length > 0 ? (
                                        transactions.data.map((transaction) => (
                                            <tr key={transaction.id} className="hover:bg-muted/30">
                                                <td className="px-4 py-3">
                                                    <div className="text-sm font-semibold">{transaction.description}</div>
                                                    <div className="text-muted-foreground text-xs">{transaction.created_at ?? '—'}</div>
                                                </td>
                                                <td className="px-4 py-3">
                                                    <div className="text-sm font-semibold">{transaction.user.name}</div>
                                                    <div className="text-muted-foreground text-xs">
                                                        @{transaction.user.username ?? 'unknown'} / {transaction.user.email}
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3 text-right">
                                                    <Badge variant={pointsVariant(transaction.points)}>{transaction.points}</Badge>
                                                </td>
                                                <td className="px-4 py-3 text-right text-sm text-muted-foreground">{transaction.type}</td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td colSpan={4} className="px-4 py-8 text-center text-sm text-muted-foreground">
                                                No transactions match the current filters.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div className="mt-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div className="text-muted-foreground text-sm">
                            Showing {transactions.from ?? 0} to {transactions.to ?? 0} of {transactions.total} transactions
                        </div>

                        <div className="flex flex-wrap gap-2">
                            {transactions.links.map((link, index) =>
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

