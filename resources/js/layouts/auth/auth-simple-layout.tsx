import { Link } from '@inertiajs/react';

interface AuthLayoutProps {
    children: React.ReactNode;
    title?: string;
    description?: string;
}

export default function AuthSimpleLayout({ children, title, description }: AuthLayoutProps) {
    return (
        <div className="bg-background flex min-h-svh items-center justify-center p-4">
            <div className="w-full max-w-sm">
                <div className="mb-7 flex flex-col items-center gap-3 text-center">
                    <Link href={route('home')}>
                        <img src="/logo.png" alt="Soaar" className="size-14 object-contain" />
                    </Link>
                    <div className="space-y-1">
                        <h1 className="text-xl font-semibold tracking-tight">{title}</h1>
                        <p className="text-muted-foreground text-sm">{description}</p>
                    </div>
                </div>

                <div className="bg-card rounded-2xl border p-6">{children}</div>
            </div>
        </div>
    );
}
