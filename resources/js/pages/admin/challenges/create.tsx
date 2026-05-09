import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { Head, router, usePage } from '@inertiajs/react';

type Props = {
    statuses: string[];
    defaults: {
        status: string;
    };
};

export default function AdminChallengesCreate({ statuses, defaults }: Props) {
    const { errors } = usePage().props as unknown as { errors: Record<string, string> };

    function submit(e: React.FormEvent<HTMLFormElement>) {
        e.preventDefault();
        const form = new FormData(e.currentTarget);
        router.post('/admin/challenges', Object.fromEntries(form.entries()));
    }

    return (
        <AppLayout breadcrumbs={[{ title: 'Challenges', href: '/admin/challenges' }, { title: 'Create', href: '/admin/challenges/create' }]}>
            <Head title="Create Challenge" />

            <div className="p-4">
                <div className="bg-card rounded-2xl border p-5">
                    <div className="text-lg font-semibold">Create challenge</div>
                    <div className="text-muted-foreground mt-1 text-sm">Create a new challenge for users to join.</div>

                    <form onSubmit={submit} className="mt-6 grid gap-4 md:max-w-2xl">
                        <div className="grid gap-2">
                            <Label htmlFor="title">Title</Label>
                            <Input id="title" name="title" required />
                            {errors.title ? <div className="text-sm text-destructive">{errors.title}</div> : null}
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="description">Description</Label>
                            <textarea
                                id="description"
                                name="description"
                                className="min-h-24 rounded-md border bg-background px-3 py-2 text-sm"
                            />
                            {errors.description ? <div className="text-sm text-destructive">{errors.description}</div> : null}
                        </div>

                        <div className="grid gap-4 md:grid-cols-2">
                            <div className="grid gap-2">
                                <Label htmlFor="duration_days">Duration (days)</Label>
                                <Input id="duration_days" name="duration_days" type="number" min={1} required />
                                {errors.duration_days ? <div className="text-sm text-destructive">{errors.duration_days}</div> : null}
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="reward_points">Reward points</Label>
                                <Input id="reward_points" name="reward_points" type="number" min={0} required />
                                {errors.reward_points ? <div className="text-sm text-destructive">{errors.reward_points}</div> : null}
                            </div>
                        </div>

                        <div className="grid gap-4 md:grid-cols-2">
                            <div className="grid gap-2">
                                <Label htmlFor="status">Status</Label>
                                <select
                                    id="status"
                                    name="status"
                                    defaultValue={defaults.status}
                                    className="rounded-md border bg-background px-3 py-2 text-sm"
                                >
                                    {statuses.map((s) => (
                                        <option key={s} value={s}>
                                            {s}
                                        </option>
                                    ))}
                                </select>
                                {errors.status ? <div className="text-sm text-destructive">{errors.status}</div> : null}
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="start_date">Start date</Label>
                                <Input id="start_date" name="start_date" type="date" required />
                                {errors.start_date ? <div className="text-sm text-destructive">{errors.start_date}</div> : null}
                            </div>
                        </div>

                        <div className="grid gap-2 md:max-w-sm">
                            <Label htmlFor="end_date">End date</Label>
                            <Input id="end_date" name="end_date" type="date" required />
                            {errors.end_date ? <div className="text-sm text-destructive">{errors.end_date}</div> : null}
                        </div>

                        <div className="flex gap-2 pt-2">
                            <Button type="submit">Create</Button>
                            <Button type="button" variant="outline" onClick={() => router.visit('/admin/challenges')}>
                                Cancel
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}

