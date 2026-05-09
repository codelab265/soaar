import { StatCard } from '@/components/stat-card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, router } from '@inertiajs/react';
import { CheckCircle, Clock, Users, XCircle } from 'lucide-react';

type PartnerRequestStatus = 'pending' | 'accepted' | 'declined';

type AdminUser = {
    id: number;
    name: string;
    username: string | null;
    email: string;
};

type PartnerRequest = {
    id: number;
    status: PartnerRequestStatus;
    goal: {
        id: number;
        title: string;
    };
    requester: AdminUser;
    partner: AdminUser;
    created_at: string | null;
    responded_at: string | null;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedRequests = {
    data: PartnerRequest[];
    links: PaginationLink[];
    from: number | null;
    to: number | null;
    total: number;
};

type Props = {
    filters: {
        search: string;
        status: 'all' | PartnerRequestStatus;
    };
    requests: PaginatedRequests;
    summary: {
        total: number;
        pending: number;
        accepted: number;
        declined: number;
    };
};

const statusLabels: Record<PartnerRequestStatus, string> = {
    pending: 'Pending',
    accepted: 'Accepted',
    declined: 'Declined',
};

function formatDate(value: string | null) {
    if (!value) {
        return 'Not responded';
    }

    return new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

function statusVariant(status: PartnerRequestStatus) {
    if (status === 'accepted') {
        return 'default';
    }

    if (status === 'declined') {
        return 'destructive';
    }

    return 'secondary';
}

function actionUrl(request: PartnerRequest, action: 'accept' | 'decline') {
    return `/admin/partner-requests/${request.id}/${action}`;
}

function paginationLabel(label: string) {
    return label.replace('&laquo; Previous', 'Previous').replace('Next &raquo;', 'Next');
}

export default function AdminPartnerRequests({ filters, requests, summary }: Props) {
    const submitAction = (request: PartnerRequest, action: 'accept' | 'decline') => {
        router.post(actionUrl(request, action), {}, { preserveScroll: true });
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Partner Requests', href: '/admin/partner-requests' }]}>
            <Head title="Partner Requests" />

            <div className="space-y-4 p-4">
                <div className="grid gap-4 md:grid-cols-4">
                    <StatCard title="Total" value={summary.total} icon={Users} />
                    <StatCard title="Pending" value={summary.pending} icon={Clock} />
                    <StatCard title="Accepted" value={summary.accepted} icon={CheckCircle} />
                    <StatCard title="Declined" value={summary.declined} icon={XCircle} />
                </div>

                <div className="rounded-xl border p-4">
                    <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                        <div>
                            <div className="text-lg font-semibold">Partner Requests</div>
                            <div className="text-muted-foreground text-sm">Review accountability partner invitations across the platform.</div>
                        </div>

                        <form method="get" className="grid gap-2 md:grid-cols-[220px_160px_auto_auto]">
                            <Input name="search" defaultValue={filters.search} placeholder="Search users or goals" />
                            <select name="status" defaultValue={filters.status} className="rounded-md border bg-background px-3 py-2 text-sm">
                                <option value="all">All statuses</option>
                                <option value="pending">Pending</option>
                                <option value="accepted">Accepted</option>
                                <option value="declined">Declined</option>
                            </select>
                            <Button type="submit" variant="outline">
                                Filter
                            </Button>
                            <Button asChild variant="ghost">
                                <Link href="/admin/partner-requests">Reset</Link>
                            </Button>
                        </form>
                    </div>

                    <div className="mt-4 overflow-hidden rounded-lg border">
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y">
                                <thead className="bg-muted/50">
                                    <tr>
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Request
                                        </th>
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Requester
                                        </th>
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Partner
                                        </th>
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Dates
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {requests.data.length > 0 ? (
                                        requests.data.map((request) => (
                                            <tr key={request.id} className="hover:bg-muted/30">
                                                <td className="px-4 py-3">
                                                    <div className="flex flex-col gap-2">
                                                        <div className="text-sm font-semibold">{request.goal.title}</div>
                                                        <div>
                                                            <Badge variant={statusVariant(request.status)}>{statusLabels[request.status]}</Badge>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3">
                                                    <UserBlock user={request.requester} />
                                                </td>
                                                <td className="px-4 py-3">
                                                    <UserBlock user={request.partner} />
                                                </td>
                                                <td className="px-4 py-3 text-sm">
                                                    <div>Created {formatDate(request.created_at)}</div>
                                                    <div className="text-muted-foreground">Responded {formatDate(request.responded_at)}</div>
                                                </td>
                                                <td className="px-4 py-3">
                                                    {request.status === 'pending' ? (
                                                        <div className="flex justify-end gap-2">
                                                            <Button
                                                                type="button"
                                                                size="sm"
                                                                variant="outline"
                                                                onClick={() => submitAction(request, 'accept')}
                                                            >
                                                                Accept
                                                            </Button>
                                                            <Button
                                                                type="button"
                                                                size="sm"
                                                                variant="destructive"
                                                                onClick={() => submitAction(request, 'decline')}
                                                            >
                                                                Decline
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
                                                No partner requests match the current filters.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div className="mt-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div className="text-muted-foreground text-sm">
                            Showing {requests.from ?? 0} to {requests.to ?? 0} of {requests.total} requests
                        </div>

                        <div className="flex flex-wrap gap-2">
                            {requests.links.map((link, index) =>
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
