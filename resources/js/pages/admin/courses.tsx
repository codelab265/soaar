import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { StatCard } from '@/components/stat-card';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, router } from '@inertiajs/react';
import { BookOpen, CircleCheck, Layers } from 'lucide-react';

type AdminCourse = {
    id: number;
    name: string;
    duration: string;
    price_mwk: number;
    price_points: number;
    content_type: string;
    content_url: string;
    is_active: boolean;
    enrollments_count: number;
    created_at: string | null;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedCourses = {
    data: AdminCourse[];
    links: PaginationLink[];
    from: number | null;
    to: number | null;
    total: number;
};

type Props = {
    filters: {
        search: string;
        active: 'all' | 'active' | 'inactive';
    };
    courses: PaginatedCourses;
    summary: {
        total: number;
        active: number;
        inactive: number;
    };
};

function paginationLabel(label: string) {
    return label.replace('&laquo; Previous', 'Previous').replace('Next &raquo;', 'Next');
}

export default function AdminCourses({ filters, courses, summary }: Props) {
    return (
        <AppLayout breadcrumbs={[{ title: 'Courses', href: '/admin/courses' }]}>
            <Head title="Courses" />

            <div className="space-y-4 p-4">
                <div className="grid gap-4 md:grid-cols-3">
                    <StatCard title="Total" value={summary.total} icon={Layers} tone="brand" />
                    <StatCard title="Active" value={summary.active} icon={CircleCheck} tone="success" />
                    <StatCard title="Inactive" value={summary.inactive} icon={BookOpen} tone="neutral" />
                </div>

                <div className="rounded-xl border p-4">
                    <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                        <div>
                            <div className="text-lg font-semibold">Courses</div>
                            <div className="text-muted-foreground text-sm">Browse courses and enrollment counts.</div>
                        </div>

                        <div className="flex flex-wrap items-center gap-2">
                            <Button onClick={() => router.visit('/admin/courses/create')}>New course</Button>

                        <form method="get" className="grid gap-2 md:grid-cols-[280px_180px_auto_auto]">
                            <Input name="search" defaultValue={filters.search} placeholder="Search name" />
                            <select name="active" defaultValue={filters.active} className="rounded-md border bg-background px-3 py-2 text-sm">
                                <option value="all">All</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <Button type="submit" variant="outline">
                                Filter
                            </Button>
                            <Button asChild variant="ghost">
                                <Link href="/admin/courses">Reset</Link>
                            </Button>
                        </form>
                        </div>
                    </div>

                    <div className="mt-4 overflow-hidden rounded-lg border">
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y">
                                <thead className="bg-muted/50">
                                    <tr>
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Course
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Enrollments
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            MWK
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Points
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Active
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Manage
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {courses.data.length > 0 ? (
                                        courses.data.map((course) => (
                                            <tr key={course.id} className="hover:bg-muted/30">
                                                <td className="px-4 py-3">
                                                    <div className="text-sm font-semibold">{course.name}</div>
                                                    <div className="text-muted-foreground text-xs">
                                                        {course.duration} • {course.content_type}
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3 text-right text-sm">{course.enrollments_count}</td>
                                                <td className="px-4 py-3 text-right text-sm">{course.price_mwk}</td>
                                                <td className="px-4 py-3 text-right text-sm">{course.price_points}</td>
                                                <td className="px-4 py-3 text-right">
                                                    {course.is_active ? <Badge>Active</Badge> : <Badge variant="outline">Inactive</Badge>}
                                                </td>
                                                <td className="px-4 py-3 text-right">
                                                    <div className="flex flex-wrap justify-end gap-2">
                                                        <Button
                                                            size="sm"
                                                            variant={course.is_active ? 'outline' : 'default'}
                                                            onClick={() =>
                                                                router.post(
                                                                    `/admin/courses/${course.id}/active`,
                                                                    { is_active: course.is_active ? 0 : 1 },
                                                                    { preserveScroll: true },
                                                                )
                                                            }
                                                        >
                                                            {course.is_active ? 'Deactivate' : 'Activate'}
                                                        </Button>
                                                        <Button asChild size="sm" variant="outline">
                                                            <Link href={`/admin/courses/${course.id}/edit`}>Edit</Link>
                                                        </Button>
                                                        <Button
                                                            size="sm"
                                                            variant="destructive"
                                                            onClick={() => {
                                                                if (!confirm('Delete this course?')) return;
                                                                router.delete(`/admin/courses/${course.id}`, { preserveScroll: true });
                                                            }}
                                                        >
                                                            Delete
                                                        </Button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td colSpan={6} className="px-4 py-8 text-center text-sm text-muted-foreground">
                                                No courses match the current filters.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div className="mt-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div className="text-muted-foreground text-sm">
                            Showing {courses.from ?? 0} to {courses.to ?? 0} of {courses.total} courses
                        </div>

                        <div className="flex flex-wrap gap-2">
                            {courses.links.map((link, index) =>
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

