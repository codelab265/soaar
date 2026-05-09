import AppLayout from '@/layouts/app-layout';
import { Head, useForm } from '@inertiajs/react';

export default function SendGlobalNotification() {
    const form = useForm({
        audience: 'all',
        title: '',
        body: '',
    });

    return (
        <AppLayout breadcrumbs={[{ title: 'Send Global Notification', href: '/admin/notifications/global' }]}>
            <Head title="Send Global Notification" />
            <div className="p-4">
                <div className="rounded-xl border p-6">
                    <div className="text-lg font-semibold">Send Global Notification</div>
                    <div className="text-muted-foreground mt-1 text-sm">Sends an in-app notification to selected users.</div>

                    <form
                        className="mt-6 grid gap-4"
                        onSubmit={(e) => {
                            e.preventDefault();
                            form.post('/admin/notifications/global');
                        }}
                    >
                        <div className="grid gap-2">
                            <label className="text-sm font-medium">Audience</label>
                            <select
                                className="rounded-md border bg-transparent px-3 py-2 text-sm"
                                value={form.data.audience}
                                onChange={(e) => form.setData('audience', e.target.value)}
                            >
                                <option value="all">All users (except suspended)</option>
                                <option value="inactive">Inactive (2+ days)</option>
                                <option value="streak_at_risk">Streak at risk (yesterday activity)</option>
                            </select>
                            {form.errors.audience ? <div className="text-sm text-red-600">{form.errors.audience}</div> : null}
                        </div>

                        <div className="grid gap-2">
                            <label className="text-sm font-medium">Title</label>
                            <input
                                className="rounded-md border bg-transparent px-3 py-2 text-sm"
                                value={form.data.title}
                                onChange={(e) => form.setData('title', e.target.value)}
                                maxLength={120}
                            />
                            {form.errors.title ? <div className="text-sm text-red-600">{form.errors.title}</div> : null}
                        </div>

                        <div className="grid gap-2">
                            <label className="text-sm font-medium">Body</label>
                            <textarea
                                className="min-h-32 rounded-md border bg-transparent px-3 py-2 text-sm"
                                value={form.data.body}
                                onChange={(e) => form.setData('body', e.target.value)}
                                maxLength={500}
                            />
                            {form.errors.body ? <div className="text-sm text-red-600">{form.errors.body}</div> : null}
                        </div>

                        <div className="flex items-center gap-3">
                            <button
                                type="submit"
                                disabled={form.processing}
                                className="inline-flex items-center rounded-md border px-4 py-2 text-sm font-medium hover:bg-muted disabled:opacity-50"
                            >
                                Send
                            </button>
                            {form.recentlySuccessful ? (
                                <div className="text-sm text-emerald-600">Sent.</div>
                            ) : (
                                <div className="text-muted-foreground text-sm">This writes to the database notifications table.</div>
                            )}
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}

