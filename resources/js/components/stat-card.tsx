import { cn } from '@/lib/utils';
import { type LucideIcon } from 'lucide-react';

export function StatCard({
    title,
    value,
    subtitle,
    icon: Icon,
    className,
}: {
    title: string;
    value: React.ReactNode;
    subtitle?: string;
    icon?: LucideIcon;
    className?: string;
}) {
    return (
        <div className={cn('bg-card rounded-2xl border p-5', className)}>
            <div className="flex items-start justify-between gap-3">
                <p className="text-muted-foreground text-sm font-medium leading-none">{title}</p>
                {Icon ? (
                    <div className="bg-primary/10 flex size-8 shrink-0 items-center justify-center rounded-lg">
                        <Icon className="text-primary size-4" />
                    </div>
                ) : null}
            </div>
            <p className="mt-3 text-3xl font-bold tracking-tight">{value}</p>
            {subtitle ? <p className="text-muted-foreground mt-1.5 text-xs">{subtitle}</p> : null}
        </div>
    );
}
