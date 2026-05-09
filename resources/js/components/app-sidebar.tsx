import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import {
    CreditCard,
    Flag,
    LayoutGrid,
    ListChecks,
    Megaphone,
    Package,
    ShieldCheck,
    Target,
    Trophy,
    Users,
} from 'lucide-react';
import AppLogo from './app-logo';

const footerNavItems = [];

export function AppSidebar() {
    const { auth } = usePage<SharedData>().props;
    const isAdmin = Boolean((auth.user as unknown as { is_admin?: boolean })?.is_admin);

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain
                    label="Platform"
                    items={[
                        {
                            title: 'Dashboard',
                            url: '/dashboard',
                            icon: LayoutGrid,
                        },
                    ]}
                />

                {isAdmin ? (
                    <>
                        <NavMain
                            label="Admin"
                            items={[
                                {
                                    title: 'Leaderboard',
                                    url: '/admin/leaderboard',
                                    icon: Trophy,
                                },
                                {
                                    title: 'Users',
                                    url: '/admin/users',
                                    icon: Users,
                                },
                                {
                                    title: 'Send Notification',
                                    url: '/admin/notifications/global',
                                    icon: Megaphone,
                                },
                            ]}
                        />

                        <NavMain
                            label="Content"
                            items={[
                                {
                                    title: 'Partner Requests',
                                    url: '/admin/partner-requests',
                                    icon: ShieldCheck,
                                },
                                {
                                    title: 'Goals',
                                    url: '/admin/goals',
                                    icon: Flag,
                                },
                                {
                                    title: 'Objectives',
                                    url: '/admin/objectives',
                                    icon: Target,
                                },
                                {
                                    title: 'Tasks',
                                    url: '/admin/tasks',
                                    icon: ListChecks,
                                },
                                {
                                    title: 'Challenges',
                                    url: '/admin/challenges',
                                    icon: Trophy,
                                },
                                {
                                    title: 'Courses',
                                    url: '/admin/courses',
                                    icon: Package,
                                },
                            ]}
                        />

                        <NavMain
                            label="Finance & Activity"
                            items={[
                                {
                                    title: 'Subscriptions',
                                    url: '/admin/subscriptions',
                                    icon: CreditCard,
                                },
                                {
                                    title: 'Point Transactions',
                                    url: '/admin/point-transactions',
                                    icon: CreditCard,
                                },
                                {
                                    title: 'Streaks',
                                    url: '/admin/streaks',
                                    icon: ListChecks,
                                },
                            ]}
                        />
                    </>
                ) : null}
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
