import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';

export default function AdminPlaceholder({ title }: { title: string }) {
    return (
        <AppLayout breadcrumbs={[{ title, href: `/admin/${title.toLowerCase().replaceAll(' ', '-')}` }]}>
            <Head title={title} />
            <div className="p-4">
                <div className="rounded-xl border p-6">
                    <div className="text-lg font-semibold">{title}</div>
                    <div className="text-muted-foreground mt-2 text-sm">
                        This section is being migrated from Filament to the unified Inertia admin UI.
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

