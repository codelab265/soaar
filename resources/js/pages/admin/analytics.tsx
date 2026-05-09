import { AdminActivityChart } from '@/components/activity-chart';
import { StatCard } from '@/components/stat-card';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import { ChartColumn, Trophy, Users } from 'lucide-react';

type Props = {
    filters: { days: number };
    series: Array<{ date: string; completedTasks: number; pointsEarned: number; newUsers: number }>;
    summary: { completedTasks: number; pointsEarned: number; newUsers: number };
};

const PERIODS: Array<{ label: string; days: number }> = [
    { label: '7D', days: 7 },
    { label: '14D', days: 14 },
    { label: '30D', days: 30 },
    { label: '90D', days: 90 },
];

export default function AdminAnalytics({ filters, series, summary }: Props) {
    return (
        <AppLayout breadcrumbs={[{ title: 'Analytics', href: '/admin/analytics' }]}>
            <Head title="Analytics" />

            <div className="space-y-4 p-4">
                <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                    <div>
                        <div className="text-lg font-semibold">Analytics</div>
                        <div className="text-muted-foreground text-sm">Platform trends over time.</div>
                    </div>

                    <div className="flex flex-wrap items-center gap-2">
                        {PERIODS.map((p) => (
                            <Button
                                key={p.days}
                                size="sm"
                                variant={filters.days === p.days ? 'default' : 'outline'}
                                onClick={() => router.get('/admin/analytics', { days: p.days }, { preserveScroll: true, preserveState: true })}
                            >
                                {p.label}
                            </Button>
                        ))}
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                    <StatCard title="Tasks completed" value={summary.completedTasks} icon={ChartColumn} tone="success" />
                    <StatCard title="Points earned" value={summary.pointsEarned} icon={Trophy} tone="brand" />
                    <StatCard title="New users" value={summary.newUsers} icon={Users} tone="neutral" />
                </div>

                <AdminActivityChart data={series} days={filters.days} />
            </div>
        </AppLayout>
    );
}

