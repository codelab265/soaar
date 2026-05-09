import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { StatCard } from '@/components/stat-card';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, router } from '@inertiajs/react';
import { BadgeCheck, Crown, CreditCard, ShieldAlert, Users } from 'lucide-react';

type SubscriptionTier = 'free' | 'premium';
type SubscriptionStatus = 'active' | 'cancelled' | 'expired' | 'trialing';

type AdminUser = {
    id: number;
    name: string;
    username: string | null;
    email: string;
};

type AdminSubscription = {
    id: number;
    tier: SubscriptionTier;
    status: SubscriptionStatus;
    price_mwk: number;
    starts_at: string | null;
    ends_at: string | null;
    cancelled_at: string | null;
    user: AdminUser;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedSubscriptions = {
    data: AdminSubscription[];
    links: PaginationLink[];
    from: number | null;
    to: number | null;
    total: number;
};

type Props = {
    filters: {
        search: string;
        tier: 'all' | SubscriptionTier;
        status: 'all' | SubscriptionStatus;
    };
    subscriptions: PaginatedSubscriptions;
    summary: {
        total: number;
        active: number;
        cancelled: number;
        expired: number;
        premium: number;
    };
};

function statusVariant(status: SubscriptionStatus) {
    if (status === 'active') {
        return 'default';
    }

    if (status === 'cancelled' || status === 'expired') {
        return 'destructive';
    }

    return 'secondary';
}

function paginationLabel(label: string) {
    return label.replace('&laquo; Previous', 'Previous').replace('Next &raquo;', 'Next');
}

function shortDate(value: string | null) {
    if (!value) {
        return '—';
    }

    return new Intl.DateTimeFormat(undefined, { dateStyle: 'medium' }).format(new Date(value));
}

export default function AdminSubscriptions({ filters, subscriptions, summary }: Props) {
    const cancel = (subscription: AdminSubscription) => {
        router.post(`/admin/subscriptions/${subscription.id}/cancel`, {}, { preserveScroll: true });
    };

    const renew = (subscription: AdminSubscription) => {
        router.post(`/admin/subscriptions/${subscription.id}/renew`, {}, { preserveScroll: true });
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Subscriptions', href: '/admin/subscriptions' }]}>
            <Head title="Subscriptions" />

            <div className="space-y-4 p-4">
                <div className="grid gap-4 md:grid-cols-5">
                    <StatCard title="Total" value={summary.total} icon={Users} tone="brand" />
                    <StatCard title="Active" value={summary.active} icon={BadgeCheck} tone="success" />
                    <StatCard title="Cancelled" value={summary.cancelled} icon={ShieldAlert} tone="warning" />
                    <StatCard title="Expired" value={summary.expired} icon={CreditCard} tone="danger" />
                    <StatCard title="Premium" value={summary.premium} icon={Crown} tone="brand" />
                </div>

                <div className="rounded-xl border p-4">
                    <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                        <div>
                            <div className="text-lg font-semibold">Subscriptions</div>
                            <div className="text-muted-foreground text-sm">Browse and manage subscriptions.</div>
                        </div>

                        <form method="get" className="grid gap-2 md:grid-cols-[240px_160px_160px_auto_auto]">
                            <Input name="search" defaultValue={filters.search} placeholder="Search user" />
                            <select name="tier" defaultValue={filters.tier} className="rounded-md border bg-background px-3 py-2 text-sm">
                                <option value="all">All tiers</option>
                                <option value="free">Free</option>
                                <option value="premium">Premium</option>
                            </select>
                            <select name="status" defaultValue={filters.status} className="rounded-md border bg-background px-3 py-2 text-sm">
                                <option value="all">All status</option>
                                <option value="active">Active</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="expired">Expired</option>
                                <option value="trialing">Trialing</option>
                            </select>
                            <Button type="submit" variant="outline">
                                Filter
                            </Button>
                            <Button asChild variant="ghost">
                                <Link href="/admin/subscriptions">Reset</Link>
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
                                            Tier / Status
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Price (MWK)
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Period
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {subscriptions.data.length > 0 ? (
                                        subscriptions.data.map((subscription) => (
                                            <tr key={subscription.id} className="hover:bg-muted/30">
                                                <td className="px-4 py-3">
                                                    <div className="text-sm font-semibold">{subscription.user.name}</div>
                                                    <div className="text-muted-foreground text-xs">
                                                        @{subscription.user.username ?? 'unknown'} / {subscription.user.email}
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3">
                                                    <div className="flex flex-wrap gap-2">
                                                        <Badge variant="outline">{subscription.tier}</Badge>
                                                        <Badge variant={statusVariant(subscription.status)}>{subscription.status}</Badge>
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3 text-right text-sm">{subscription.price_mwk}</td>
                                                <td className="px-4 py-3 text-right text-sm text-muted-foreground">
                                                    <div>{shortDate(subscription.starts_at)}</div>
                                                    <div>{shortDate(subscription.ends_at)}</div>
                                                </td>
                                                <td className="px-4 py-3">
                                                    <div className="flex justify-end gap-2">
                                                        {subscription.status === 'active' ? (
                                                            <>
                                                                {subscription.tier === 'premium' ? (
                                                                    <Button type="button" size="sm" variant="outline" onClick={() => renew(subscription)}>
                                                                        Renew
                                                                    </Button>
                                                                ) : null}
                                                                <Button type="button" size="sm" variant="destructive" onClick={() => cancel(subscription)}>
                                                                    Cancel
                                                                </Button>
                                                            </>
                                                        ) : (
                                                            <div className="text-right text-sm text-muted-foreground">No action</div>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td colSpan={5} className="px-4 py-8 text-center text-sm text-muted-foreground">
                                                No subscriptions match the current filters.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div className="mt-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div className="text-muted-foreground text-sm">
                            Showing {subscriptions.from ?? 0} to {subscriptions.to ?? 0} of {subscriptions.total} subscriptions
                        </div>

                        <div className="flex flex-wrap gap-2">
                            {subscriptions.links.map((link, index) =>
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

