import AppLayout from '@/layouts/app-layout';
import { ActivityChart, AdminActivityChart } from '@/components/activity-chart';
import { StatCard } from '@/components/stat-card';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import {
    CalendarCheck,
    CreditCard,
    Flag,
    Flame,
    ListChecks,
    Medal,
    Megaphone,
    Package,
    ShieldCheck,
    Target,
    Trophy,
    Users,
    Zap,
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

type DashboardStats = {
    totalPoints: number;
    disciplineScore: string | number;
    currentStreak: number;
    longestStreak: number;
    activeGoals: number;
    tasksDueToday: number;
    pendingTasks: number;
    completedTasksThisWeek: number;
    dailyStreakCurrent: number | null;
    dailyStreakLongest: number | null;
    dailyStreakLastActivityDate: string | null;
};

type RecentTask = {
    id: number;
    title: string;
    status: string;
    scheduled_date: string | null;
    completed_at: string | null;
    points_value: number;
    objective: null | {
        title: string;
        goal: null | {
            title: string;
        };
    };
};

type AdminStats = {
    totalUsers: number;
    activeUsers: number;
    suspendedUsers: number;
    activeGoals: number;
    pendingVerificationGoals: number;
    completedGoals: number;
    pendingTasks: number;
    tasksDueToday: number;
    completedTasksThisWeek: number;
};

type ActivityDay = {
    date: string;
    completedTasks: number;
    pointsEarned: number;
};

type AdminActivityDay = ActivityDay & { newUsers: number };

type DashboardActivity = {
    activityDays: ActivityDay[];
    adminActivityDays: AdminActivityDay[] | null;
};

const statusConfig: Record<string, { label: string; dot: string }> = {
    completed: { label: 'Completed', dot: 'bg-emerald-500' },
    in_progress: { label: 'In progress', dot: 'bg-blue-500' },
    pending: { label: 'Pending', dot: 'bg-amber-400' },
    missed: { label: 'Missed', dot: 'bg-red-500' },
};

function StatusDot({ status }: { status: string }) {
    const config = statusConfig[status] ?? { label: status.replaceAll('_', ' '), dot: 'bg-muted-foreground' };
    return (
        <span className="flex items-center gap-1.5 whitespace-nowrap text-xs">
            <span className={`inline-block size-1.5 rounded-full ${config.dot}`} />
            {config.label}
        </span>
    );
}

const adminNavItems = [
    { title: 'Users', href: '/admin/users', icon: Users },
    { title: 'Goals', href: '/admin/goals', icon: Flag },
    { title: 'Tasks', href: '/admin/tasks', icon: ListChecks },
    { title: 'Challenges', href: '/admin/challenges', icon: Trophy },
    { title: 'Courses', href: '/admin/courses', icon: Package },
    { title: 'Objectives', href: '/admin/objectives', icon: Target },
    { title: 'Subscriptions', href: '/admin/subscriptions', icon: CreditCard },
    { title: 'Partner Requests', href: '/admin/partner-requests', icon: ShieldCheck },
    { title: 'Notify All', href: '/admin/notifications/global', icon: Megaphone },
];

const PERIOD_OPTIONS: { label: string; days: number }[] = [
    { label: '7D', days: 7 },
    { label: '14D', days: 14 },
    { label: '30D', days: 30 },
];

export default function Dashboard({
    isAdmin,
    stats,
    recentTasks,
    adminStats,
    activity,
    filters,
}: {
    isAdmin: boolean;
    stats: DashboardStats;
    recentTasks: RecentTask[];
    adminStats: AdminStats | null;
    activity: DashboardActivity;
    filters: { days: number };
}) {
    const { auth } = usePage<SharedData>().props;
    const firstName = auth.user.name.split(' ')[0];
    const activityDays = activity?.activityDays ?? [];
    const totalTasksThisWeek = activityDays.reduce((sum, d) => sum + d.completedTasks, 0);
    const totalPointsThisWeek = activityDays.reduce((sum, d) => sum + d.pointsEarned, 0);

    function setDays(days: number) {
        router.get('/dashboard', { days }, { preserveScroll: true, preserveState: true });
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">

                {/* Page header */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">
                            {isAdmin ? 'Platform Overview' : `Welcome back, ${firstName}`}
                        </h1>
                        <p className="text-muted-foreground mt-0.5 text-sm">
                            {isAdmin ? 'Manage your platform from one place.' : "Here's how you're doing today."}
                        </p>
                    </div>
                    {isAdmin ? (
                        <Link
                            href="/admin/notifications/global"
                            className="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-medium transition-colors"
                        >
                            <Megaphone className="size-4" />
                            Send notification
                        </Link>
                    ) : (
                        <Link
                            href="/tasks"
                            className="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-medium transition-colors"
                        >
                            <Zap className="size-4" />
                            Plan today
                        </Link>
                    )}
                </div>

                {/* ── ADMIN VIEW ────────────────────────────────────────── */}
                {isAdmin && adminStats ? (
                    <div className="flex flex-col gap-6">
                        {/* Admin stat cards */}
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <StatCard
                                title="Total users"
                                value={adminStats.totalUsers}
                                icon={Users}
                                subtitle={`${adminStats.activeUsers} active · ${adminStats.suspendedUsers} suspended`}
                            />
                            <StatCard
                                title="Active goals"
                                value={adminStats.activeGoals}
                                icon={Flag}
                                subtitle={`${adminStats.pendingVerificationGoals} pending review · ${adminStats.completedGoals} completed`}
                            />
                            <StatCard
                                title="Tasks due today"
                                value={adminStats.tasksDueToday}
                                icon={ListChecks}
                                subtitle={`${adminStats.pendingTasks} pending · ${adminStats.completedTasksThisWeek} done this week`}
                            />
                        </div>

                        {/* Admin quick navigation */}
                        <div className="bg-card rounded-2xl border p-5">
                            <p className="text-sm font-semibold">Management</p>
                            <p className="text-muted-foreground mt-0.5 text-xs">Jump to any section</p>
                            <div className="mt-4 grid grid-cols-3 gap-2 sm:grid-cols-4 lg:grid-cols-9">
                                {adminNavItems.map((item) => (
                                    <Link
                                        key={item.href}
                                        href={item.href}
                                        className="hover:bg-accent hover:text-accent-foreground flex flex-col items-center gap-2 rounded-xl border bg-transparent p-3 text-center transition-colors"
                                    >
                                        <item.icon className="text-primary size-5" />
                                        <span className="text-xs font-medium leading-tight">{item.title}</span>
                                    </Link>
                                ))}
                            </div>
                        </div>

                        {/* Analytics header */}
                        <div className="flex items-center justify-between">
                            <p className="text-sm font-semibold">Analytics</p>
                            <div className="flex items-center gap-1 rounded-xl border bg-card p-1">
                                {PERIOD_OPTIONS.map((opt) => (
                                    <button
                                        key={opt.days}
                                        onClick={() => setDays(opt.days)}
                                        className={cn(
                                            'rounded-lg px-3 py-1.5 text-xs font-medium transition-colors',
                                            filters.days === opt.days
                                                ? 'bg-primary text-primary-foreground'
                                                : 'text-muted-foreground hover:text-foreground',
                                        )}
                                    >
                                        {opt.label}
                                    </button>
                                ))}
                            </div>
                        </div>

                        {/* Admin activity charts */}
                        {activity.adminActivityDays ? (
                            <AdminActivityChart data={activity.adminActivityDays} days={filters.days} />
                        ) : null}

                        {/* Admin detail breakdown */}
                        <div className="grid gap-4 md:grid-cols-3">
                            <div className="bg-card rounded-2xl border p-5">
                                <p className="text-sm font-semibold">Users</p>
                                <div className="mt-3 space-y-2.5">
                                    {[
                                        { label: 'Total', value: adminStats.totalUsers },
                                        { label: 'Active', value: adminStats.activeUsers },
                                        { label: 'Suspended', value: adminStats.suspendedUsers },
                                    ].map(({ label, value }) => (
                                        <div key={label} className="flex items-center justify-between text-sm">
                                            <span className="text-muted-foreground">{label}</span>
                                            <span className="font-semibold tabular-nums">{value}</span>
                                        </div>
                                    ))}
                                </div>
                                <div className="mt-4 border-t pt-3">
                                    <Link href="/admin/users" className="text-primary text-xs font-medium hover:underline">
                                        Manage users →
                                    </Link>
                                </div>
                            </div>

                            <div className="bg-card rounded-2xl border p-5">
                                <p className="text-sm font-semibold">Goals</p>
                                <div className="mt-3 space-y-2.5">
                                    {[
                                        { label: 'Active', value: adminStats.activeGoals },
                                        { label: 'Pending review', value: adminStats.pendingVerificationGoals },
                                        { label: 'Completed', value: adminStats.completedGoals },
                                    ].map(({ label, value }) => (
                                        <div key={label} className="flex items-center justify-between text-sm">
                                            <span className="text-muted-foreground">{label}</span>
                                            <span className="font-semibold tabular-nums">{value}</span>
                                        </div>
                                    ))}
                                </div>
                                <div className="mt-4 flex gap-4 border-t pt-3">
                                    <Link href="/admin/goals" className="text-primary text-xs font-medium hover:underline">
                                        Goals →
                                    </Link>
                                    <Link href="/admin/objectives" className="text-primary text-xs font-medium hover:underline">
                                        Objectives →
                                    </Link>
                                </div>
                            </div>

                            <div className="bg-card rounded-2xl border p-5">
                                <p className="text-sm font-semibold">Tasks</p>
                                <div className="mt-3 space-y-2.5">
                                    {[
                                        { label: 'Pending', value: adminStats.pendingTasks },
                                        { label: 'Due today', value: adminStats.tasksDueToday },
                                        { label: 'Completed this week', value: adminStats.completedTasksThisWeek },
                                    ].map(({ label, value }) => (
                                        <div key={label} className="flex items-center justify-between text-sm">
                                            <span className="text-muted-foreground">{label}</span>
                                            <span className="font-semibold tabular-nums">{value}</span>
                                        </div>
                                    ))}
                                </div>
                                <div className="mt-4 flex gap-4 border-t pt-3">
                                    <Link href="/admin/tasks" className="text-primary text-xs font-medium hover:underline">
                                        Tasks →
                                    </Link>
                                    <Link href="/admin/point-transactions" className="text-primary text-xs font-medium hover:underline">
                                        Points →
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                ) : null}

                {/* ── USER VIEW ─────────────────────────────────────────── */}
                {isAdmin ? null : (
                    <div className="flex flex-col gap-6">
                        {/* Stat cards */}
                        <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
                            <StatCard
                                title="Total points"
                                value={stats.totalPoints.toLocaleString()}
                                icon={Trophy}
                                subtitle={`+${totalPointsThisWeek} this week`}
                            />
                            <StatCard
                                title="Discipline score"
                                value={stats.disciplineScore}
                                icon={Medal}
                                subtitle="Consistency over time"
                            />
                            <StatCard
                                title="Current streak"
                                value={stats.currentStreak}
                                icon={Flame}
                                subtitle={`Best: ${stats.longestStreak} days`}
                            />
                            <StatCard
                                title="Daily streak"
                                value={stats.dailyStreakCurrent ?? 0}
                                icon={CalendarCheck}
                                subtitle={`Best: ${stats.dailyStreakLongest ?? 0} days`}
                            />
                        </div>

                        {/* Analytics header */}
                        <div className="flex items-center justify-between">
                            <p className="text-sm font-semibold">Analytics</p>
                            <div className="flex items-center gap-1 rounded-xl border bg-card p-1">
                                {PERIOD_OPTIONS.map((opt) => (
                                    <button
                                        key={opt.days}
                                        onClick={() => setDays(opt.days)}
                                        className={cn(
                                            'rounded-lg px-3 py-1.5 text-xs font-medium transition-colors',
                                            filters.days === opt.days
                                                ? 'bg-primary text-primary-foreground'
                                                : 'text-muted-foreground hover:text-foreground',
                                        )}
                                    >
                                        {opt.label}
                                    </button>
                                ))}
                            </div>
                        </div>

                        {/* Activity charts */}
                        <ActivityChart data={activityDays} days={filters.days} />

                        {/* Main content grid */}
                        <div className="grid gap-4 lg:grid-cols-3">
                            {/* Left: summary + quick actions */}
                            <div className="flex flex-col gap-4">
                                {/* Today summary */}
                                <div className="bg-card rounded-2xl border p-5">
                                    <p className="text-sm font-semibold">Today</p>
                                    <div className="mt-3 space-y-2.5">
                                        {[
                                            { label: 'Active goals', value: stats.activeGoals },
                                            { label: 'Due today', value: stats.tasksDueToday },
                                            { label: 'Pending tasks', value: stats.pendingTasks },
                                            { label: 'Done this week', value: stats.completedTasksThisWeek },
                                        ].map(({ label, value }) => (
                                            <div key={label} className="flex items-center justify-between text-sm">
                                                <span className="text-muted-foreground">{label}</span>
                                                <span className="font-semibold tabular-nums">{value}</span>
                                            </div>
                                        ))}
                                    </div>
                                </div>

                                {/* 7-day activity summary */}
                                <div className="bg-card rounded-2xl border p-5">
                                    <p className="text-sm font-semibold">This week</p>
                                    <div className="mt-3 space-y-2.5">
                                        <div className="flex items-center justify-between text-sm">
                                            <span className="text-muted-foreground">Tasks completed</span>
                                            <span className="font-semibold tabular-nums">{totalTasksThisWeek}</span>
                                        </div>
                                        <div className="flex items-center justify-between text-sm">
                                            <span className="text-muted-foreground">Points earned</span>
                                            <span className="font-semibold tabular-nums">{totalPointsThisWeek.toLocaleString()}</span>
                                        </div>
                                    </div>
                                    <div className="mt-4 border-t pt-4">
                                        <Link
                                            href="/goals"
                                            className="text-primary text-xs font-medium hover:underline"
                                        >
                                            Review goals →
                                        </Link>
                                    </div>
                                </div>
                            </div>

                            {/* Right: recent tasks */}
                            <div className="bg-card rounded-2xl border lg:col-span-2">
                                <div className="flex items-center justify-between border-b px-5 py-4">
                                    <p className="text-sm font-semibold">Recent tasks</p>
                                    <Link href="/tasks" className="text-primary text-xs font-medium hover:underline">
                                        View all →
                                    </Link>
                                </div>

                                {recentTasks.length === 0 ? (
                                    <div className="flex flex-col items-center justify-center gap-2 py-12 text-center">
                                        <ListChecks className="text-muted-foreground/40 size-8" />
                                        <p className="text-muted-foreground text-sm">No tasks yet. Start by planning your day.</p>
                                        <Link
                                            href="/tasks"
                                            className="bg-primary text-primary-foreground hover:bg-primary/90 mt-2 inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium"
                                        >
                                            <Zap className="size-3.5" />
                                            Create a task
                                        </Link>
                                    </div>
                                ) : (
                                    <div className="divide-y">
                                        {recentTasks.map((task) => (
                                            <div key={task.id} className="flex items-center gap-3 px-5 py-3.5">
                                                <div className="min-w-0 flex-1">
                                                    <p className="truncate text-sm font-medium">{task.title}</p>
                                                    {(task.objective?.goal?.title || task.objective?.title) ? (
                                                        <p className="text-muted-foreground mt-0.5 truncate text-xs">
                                                            {[task.objective?.goal?.title, task.objective?.title]
                                                                .filter(Boolean)
                                                                .join(' › ')}
                                                        </p>
                                                    ) : null}
                                                </div>
                                                <div className="flex shrink-0 items-center gap-3">
                                                    <StatusDot status={task.status} />
                                                    <span className="text-muted-foreground w-14 text-right text-xs tabular-nums">
                                                        {task.points_value} pts
                                                    </span>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
