import { Bar, BarChart, CartesianGrid, ResponsiveContainer, Tooltip, type TooltipProps, XAxis, YAxis } from 'recharts';

type ActivityDay = {
    date: string;
    completedTasks: number;
    pointsEarned: number;
    newUsers?: number;
};

type ChartDatum = ActivityDay & { day: string };

function formatDayLabel(dateStr: string, days: number): string {
    const date = new Date(dateStr + 'T00:00:00');
    if (days <= 7) {
        return new Intl.DateTimeFormat(undefined, { weekday: 'short' }).format(date);
    }
    return new Intl.DateTimeFormat(undefined, { month: 'short', day: 'numeric' }).format(date);
}

const tooltipStyle: React.CSSProperties = {
    background: 'white',
    border: '1px solid hsl(174, 10%, 88%)',
    borderRadius: '0.75rem',
    fontSize: '12px',
    padding: '8px 12px',
    boxShadow: 'none',
};

function ChartTooltip({ active, payload, label }: TooltipProps<number, string>) {
    if (!active || !payload?.length) {
        return null;
    }

    return (
        <div style={tooltipStyle}>
            <p className="mb-1.5 font-medium">{label}</p>
            {payload.map((entry) => (
                <p key={entry.name} style={{ color: entry.color }} className="text-xs">
                    {entry.name}: <span className="font-semibold">{entry.value?.toLocaleString()}</span>
                </p>
            ))}
        </div>
    );
}

const axisStyle = { fontSize: 11, fill: 'hsl(174, 8%, 46%)' };

function tickInterval(days: number): number {
    if (days <= 7) return 0;
    if (days <= 14) return 1;
    return 3;
}

function BaseBarChart({
    data,
    dataKey,
    name,
    color,
    days,
}: {
    data: ChartDatum[];
    dataKey: keyof ChartDatum;
    name: string;
    color: string;
    days: number;
}) {
    return (
        <ResponsiveContainer width="100%" height={160}>
            <BarChart data={data} margin={{ top: 4, right: 4, bottom: 0, left: -24 }}>
                <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="hsl(174, 10%, 90%)" />
                <XAxis dataKey="day" tick={axisStyle} axisLine={false} tickLine={false} interval={tickInterval(days)} />
                <YAxis tick={axisStyle} axisLine={false} tickLine={false} allowDecimals={false} />
                <Tooltip content={<ChartTooltip />} cursor={{ fill: 'hsl(174, 10%, 93%)' }} />
                <Bar dataKey={dataKey as string} name={name} fill={color} radius={[4, 4, 0, 0]} maxBarSize={days > 14 ? 16 : 36} />
            </BarChart>
        </ResponsiveContainer>
    );
}

export function ActivityChart({ data, days }: { data: ActivityDay[]; days: number }) {
    const chartData: ChartDatum[] = data.map((d) => ({ ...d, day: formatDayLabel(d.date, days) }));
    const label = `Last ${days} days`;

    return (
        <div className="grid gap-4 sm:grid-cols-2">
            <div className="bg-card rounded-2xl border p-5">
                <p className="text-sm font-semibold">Tasks completed</p>
                <p className="text-muted-foreground mt-0.5 text-xs">{label}</p>
                <div className="mt-4">
                    <BaseBarChart data={chartData} dataKey="completedTasks" name="Tasks" color="#3c7671" days={days} />
                </div>
            </div>
            <div className="bg-card rounded-2xl border p-5">
                <p className="text-sm font-semibold">Points earned</p>
                <p className="text-muted-foreground mt-0.5 text-xs">{label}</p>
                <div className="mt-4">
                    <BaseBarChart data={chartData} dataKey="pointsEarned" name="Points" color="hsl(199, 89%, 48%)" days={days} />
                </div>
            </div>
        </div>
    );
}

export function AdminActivityChart({ data, days }: { data: (ActivityDay & { newUsers: number })[]; days: number }) {
    const chartData: ChartDatum[] = data.map((d) => ({ ...d, day: formatDayLabel(d.date, days) }));
    const label = `Platform · last ${days} days`;

    return (
        <div className="grid gap-4 sm:grid-cols-3">
            <div className="bg-card rounded-2xl border p-5">
                <p className="text-sm font-semibold">Tasks completed</p>
                <p className="text-muted-foreground mt-0.5 text-xs">{label}</p>
                <div className="mt-4">
                    <BaseBarChart data={chartData} dataKey="completedTasks" name="Tasks" color="#3c7671" days={days} />
                </div>
            </div>
            <div className="bg-card rounded-2xl border p-5">
                <p className="text-sm font-semibold">Points earned</p>
                <p className="text-muted-foreground mt-0.5 text-xs">{label}</p>
                <div className="mt-4">
                    <BaseBarChart data={chartData} dataKey="pointsEarned" name="Points" color="hsl(199, 89%, 48%)" days={days} />
                </div>
            </div>
            <div className="bg-card rounded-2xl border p-5">
                <p className="text-sm font-semibold">New users</p>
                <p className="text-muted-foreground mt-0.5 text-xs">{label}</p>
                <div className="mt-4">
                    <BaseBarChart data={chartData} dataKey="newUsers" name="Users" color="hsl(262, 83%, 58%)" days={days} />
                </div>
            </div>
        </div>
    );
}
