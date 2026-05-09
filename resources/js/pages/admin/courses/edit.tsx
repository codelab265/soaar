import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { Head, router, usePage } from '@inertiajs/react';

type Props = {
    course: {
        id: number;
        name: string;
        description: string | null;
        duration: string | null;
        price_mwk: number;
        price_points: number;
        content_type: string | null;
        content_url: string | null;
        is_active: boolean;
    };
};

export default function AdminCoursesEdit({ course }: Props) {
    const { errors } = usePage().props as unknown as { errors: Record<string, string> };

    function submit(e: React.FormEvent<HTMLFormElement>) {
        e.preventDefault();
        const form = new FormData(e.currentTarget);
        router.put(`/admin/courses/${course.id}`, Object.fromEntries(form.entries()));
    }

    return (
        <AppLayout breadcrumbs={[{ title: 'Courses', href: '/admin/courses' }, { title: 'Edit', href: `/admin/courses/${course.id}/edit` }]}>
            <Head title="Edit Course" />

            <div className="p-4">
                <div className="bg-card rounded-2xl border p-5">
                    <div className="text-lg font-semibold">Edit course</div>
                    <div className="text-muted-foreground mt-1 text-sm">Update course details and pricing.</div>

                    <form onSubmit={submit} className="mt-6 grid gap-4 md:max-w-2xl">
                        <div className="grid gap-2">
                            <Label htmlFor="name">Name</Label>
                            <Input id="name" name="name" defaultValue={course.name} required />
                            {errors.name ? <div className="text-sm text-destructive">{errors.name}</div> : null}
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="description">Description</Label>
                            <textarea
                                id="description"
                                name="description"
                                defaultValue={course.description ?? ''}
                                className="min-h-24 rounded-md border bg-background px-3 py-2 text-sm"
                            />
                            {errors.description ? <div className="text-sm text-destructive">{errors.description}</div> : null}
                        </div>

                        <div className="grid gap-4 md:grid-cols-2">
                            <div className="grid gap-2">
                                <Label htmlFor="duration">Duration</Label>
                                <Input id="duration" name="duration" defaultValue={course.duration ?? ''} />
                                {errors.duration ? <div className="text-sm text-destructive">{errors.duration}</div> : null}
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="is_active">Active</Label>
                                <select
                                    id="is_active"
                                    name="is_active"
                                    defaultValue={course.is_active ? '1' : '0'}
                                    className="rounded-md border bg-background px-3 py-2 text-sm"
                                >
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                                {errors.is_active ? <div className="text-sm text-destructive">{errors.is_active}</div> : null}
                            </div>
                        </div>

                        <div className="grid gap-4 md:grid-cols-2">
                            <div className="grid gap-2">
                                <Label htmlFor="price_mwk">Price (MWK)</Label>
                                <Input id="price_mwk" name="price_mwk" type="number" min={0} defaultValue={course.price_mwk} required />
                                {errors.price_mwk ? <div className="text-sm text-destructive">{errors.price_mwk}</div> : null}
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="price_points">Price (points)</Label>
                                <Input id="price_points" name="price_points" type="number" min={0} defaultValue={course.price_points} required />
                                {errors.price_points ? <div className="text-sm text-destructive">{errors.price_points}</div> : null}
                            </div>
                        </div>

                        <div className="grid gap-4 md:grid-cols-2">
                            <div className="grid gap-2">
                                <Label htmlFor="content_type">Content type</Label>
                                <Input id="content_type" name="content_type" defaultValue={course.content_type ?? ''} />
                                {errors.content_type ? <div className="text-sm text-destructive">{errors.content_type}</div> : null}
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="content_url">Content URL</Label>
                                <Input id="content_url" name="content_url" defaultValue={course.content_url ?? ''} />
                                {errors.content_url ? <div className="text-sm text-destructive">{errors.content_url}</div> : null}
                            </div>
                        </div>

                        <div className="flex gap-2 pt-2">
                            <Button type="submit">Save</Button>
                            <Button type="button" variant="outline" onClick={() => router.visit('/admin/courses')}>
                                Cancel
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}

