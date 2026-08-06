import {
    Bar,
    BarChart,
    CartesianGrid,
    Cell,
    Legend,
    Line,
    LineChart,
    Pie,
    PieChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import { PortalCard } from '@/components/portal/portal-card';
import PortalLayout from '@/layouts/portal-layout';

type IncidentTypeSlice = {
    type: string;
    label: string;
    count: number;
    percentage: number;
    color: string;
};

type MonthlyTrendPoint = { month: string; incidents: number; resolved: number };

type SeverityBar = {
    level: string;
    label: string;
    count: number;
    color: string;
};

type ResponseTimePoint = { month: string; minutes: number };

export default function Analytics({
    incidentsByType,
    monthlyTrend,
    incidentsBySeverity,
    responseTimeTrend,
    periodLabel,
}: {
    incidentsByType: IncidentTypeSlice[];
    monthlyTrend: MonthlyTrendPoint[];
    incidentsBySeverity: SeverityBar[];
    responseTimeTrend: ResponseTimePoint[];
    periodLabel: string;
}) {
    return (
        <PortalLayout title="Analytics & Reports">
            <div className="space-y-4">
                {/* Page header */}
                <div>
                    <h1 className="text-2xl font-bold text-brand-navy">
                        Analytics & Reports
                    </h1>
                    <p className="mt-1 text-sm text-brand-muted">
                        System performance metrics and fire incident
                        intelligence — {periodLabel}
                    </p>
                </div>

                <div className="grid gap-4 lg:grid-cols-2">
                    {/* Incidents by Type */}
                    <PortalCard>
                        <div
                            className="border-b px-5 py-4"
                            style={{ borderColor: 'rgba(43,45,66,0.08)' }}
                        >
                            <h2 className="text-sm font-bold text-brand-navy">
                                Incidents by Type
                            </h2>
                        </div>
                        <div className="flex flex-col items-center gap-4 p-5 sm:flex-row sm:items-center">
                            <div
                                className="mx-auto"
                                style={{ width: 220, height: 220 }}
                            >
                                <ResponsiveContainer width="100%" height="100%">
                                    <PieChart>
                                        <Pie
                                            data={incidentsByType}
                                            dataKey="count"
                                            nameKey="label"
                                            innerRadius={62}
                                            outerRadius={100}
                                            paddingAngle={2}
                                            stroke="none"
                                        >
                                            {incidentsByType.map((slice) => (
                                                <Cell
                                                    key={slice.type}
                                                    fill={slice.color}
                                                />
                                            ))}
                                        </Pie>
                                        <Tooltip
                                            contentStyle={{
                                                fontSize: 12,
                                                borderRadius: 8,
                                                border: '1px solid rgba(43,45,66,0.1)',
                                            }}
                                            formatter={(
                                                value: number,
                                                _name,
                                                item,
                                            ) => [
                                                `${value} (${item.payload.percentage}%)`,
                                                item.payload.label,
                                            ]}
                                        />
                                    </PieChart>
                                </ResponsiveContainer>
                            </div>
                            <div className="w-full flex-1 space-y-2.5">
                                {incidentsByType.length === 0 && (
                                    <p className="text-center text-xs text-brand-muted">
                                        No incident records yet.
                                    </p>
                                )}
                                {incidentsByType.map((slice) => (
                                    <div
                                        key={slice.type}
                                        className="flex items-center gap-2 text-sm"
                                    >
                                        <span
                                            className="size-2.5 flex-shrink-0 rounded-full"
                                            style={{ background: slice.color }}
                                        />
                                        <span className="flex-1 text-brand-navy">
                                            {slice.label}
                                        </span>
                                        <span className="font-mono text-xs font-semibold text-brand-muted">
                                            {slice.percentage}%
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </PortalCard>

                    {/* Monthly Incident & Response Trend */}
                    <PortalCard>
                        <div
                            className="border-b px-5 py-4"
                            style={{ borderColor: 'rgba(43,45,66,0.08)' }}
                        >
                            <h2 className="text-sm font-bold text-brand-navy">
                                Monthly Incident & Response Trend
                            </h2>
                        </div>
                        <div className="p-5">
                            <ResponsiveContainer width="100%" height={260}>
                                <BarChart
                                    data={monthlyTrend}
                                    margin={{
                                        top: 4,
                                        right: 4,
                                        bottom: 0,
                                        left: 0,
                                    }}
                                    barCategoryGap="32%"
                                    barGap={3}
                                >
                                    <CartesianGrid
                                        strokeDasharray="3 3"
                                        stroke="rgba(43,45,66,0.06)"
                                        vertical={false}
                                    />
                                    <XAxis
                                        dataKey="month"
                                        tick={{ fontSize: 11, fill: '#6B7A8D' }}
                                        axisLine={false}
                                        tickLine={false}
                                    />
                                    <YAxis
                                        tick={{ fontSize: 11, fill: '#6B7A8D' }}
                                        axisLine={false}
                                        tickLine={false}
                                        width={28}
                                    />
                                    <Tooltip
                                        contentStyle={{
                                            fontSize: 12,
                                            borderRadius: 8,
                                            border: '1px solid rgba(43,45,66,0.1)',
                                        }}
                                    />
                                    <Legend wrapperStyle={{ fontSize: 11 }} />
                                    <Bar
                                        dataKey="incidents"
                                        name="Incidents"
                                        fill="#1D3557"
                                        radius={[3, 3, 0, 0]}
                                    />
                                    <Bar
                                        dataKey="resolved"
                                        name="Resolved"
                                        fill="#2A9D8F"
                                        radius={[3, 3, 0, 0]}
                                    />
                                </BarChart>
                            </ResponsiveContainer>
                        </div>
                    </PortalCard>
                </div>

                <div className="grid gap-4 lg:grid-cols-2">
                    {/* Incidents by Severity */}
                    <PortalCard>
                        <div
                            className="border-b px-5 py-4"
                            style={{ borderColor: 'rgba(43,45,66,0.08)' }}
                        >
                            <h2 className="text-sm font-bold text-brand-navy">
                                Incidents by Severity
                            </h2>
                        </div>
                        <div className="p-5">
                            <ResponsiveContainer width="100%" height={220}>
                                <BarChart
                                    data={incidentsBySeverity}
                                    margin={{
                                        top: 4,
                                        right: 4,
                                        bottom: 0,
                                        left: 0,
                                    }}
                                >
                                    <CartesianGrid
                                        strokeDasharray="3 3"
                                        stroke="rgba(43,45,66,0.06)"
                                        vertical={false}
                                    />
                                    <XAxis
                                        dataKey="label"
                                        tick={{ fontSize: 11, fill: '#6B7A8D' }}
                                        axisLine={false}
                                        tickLine={false}
                                    />
                                    <YAxis
                                        tick={{ fontSize: 11, fill: '#6B7A8D' }}
                                        axisLine={false}
                                        tickLine={false}
                                        width={28}
                                    />
                                    <Tooltip
                                        contentStyle={{
                                            fontSize: 12,
                                            borderRadius: 8,
                                            border: '1px solid rgba(43,45,66,0.1)',
                                        }}
                                    />
                                    <Bar
                                        dataKey="count"
                                        name="Incidents"
                                        radius={[3, 3, 0, 0]}
                                    >
                                        {incidentsBySeverity.map((row) => (
                                            <Cell
                                                key={row.level}
                                                fill={row.color}
                                            />
                                        ))}
                                    </Bar>
                                </BarChart>
                            </ResponsiveContainer>
                        </div>
                    </PortalCard>

                    {/* Average Response Time Trend */}
                    <PortalCard>
                        <div
                            className="border-b px-5 py-4"
                            style={{ borderColor: 'rgba(43,45,66,0.08)' }}
                        >
                            <h2 className="text-sm font-bold text-brand-navy">
                                Average Response Time Trend (minutes)
                            </h2>
                        </div>
                        <div className="p-5">
                            <ResponsiveContainer width="100%" height={220}>
                                <LineChart
                                    data={responseTimeTrend}
                                    margin={{
                                        top: 4,
                                        right: 4,
                                        bottom: 0,
                                        left: 0,
                                    }}
                                >
                                    <CartesianGrid
                                        strokeDasharray="3 3"
                                        stroke="rgba(43,45,66,0.06)"
                                        vertical={false}
                                    />
                                    <XAxis
                                        dataKey="month"
                                        tick={{ fontSize: 11, fill: '#6B7A8D' }}
                                        axisLine={false}
                                        tickLine={false}
                                    />
                                    <YAxis
                                        tick={{ fontSize: 11, fill: '#6B7A8D' }}
                                        axisLine={false}
                                        tickLine={false}
                                        width={28}
                                    />
                                    <Tooltip
                                        contentStyle={{
                                            fontSize: 12,
                                            borderRadius: 8,
                                            border: '1px solid rgba(43,45,66,0.1)',
                                        }}
                                    />
                                    <Line
                                        type="monotone"
                                        dataKey="minutes"
                                        name="Avg. response time"
                                        stroke="#2A9D8F"
                                        strokeWidth={2}
                                        dot={{ r: 3, fill: '#2A9D8F' }}
                                        activeDot={{ r: 5 }}
                                    />
                                </LineChart>
                            </ResponsiveContainer>
                        </div>
                    </PortalCard>
                </div>
            </div>
        </PortalLayout>
    );
}
