import { Link, router } from '@inertiajs/react';
import {
    Calendar,
    CheckCircle2,
    ChevronRight,
    ClipboardList,
    Flame,
} from 'lucide-react';
import {
    Area,
    AreaChart,
    CartesianGrid,
    Cell,
    Pie,
    PieChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import { BarangayRiskMap } from '@/components/dashboard/barangay-risk-map';
import type { BarangayRiskPoint } from '@/components/dashboard/barangay-risk-map';
import { KPICard } from '@/components/portal/kpi-card';
import { PortalCard } from '@/components/portal/portal-card';
import { StatusBadge } from '@/components/portal/status-badge';
import PortalLayout from '@/layouts/portal-layout';
import { RISK_CFG } from '@/lib/fire-status';
import type { ReportStatus } from '@/lib/fire-status';
import { fireProne } from '@/routes';
import { index as incidents, show as showIncident } from '@/routes/incidents';

type Kpis = {
    activeIncidents: number;
    activeComparison?: string;
    resolvedToday: number;
    resolvedTodayComparison?: string;
    resolvedThisMonth: number;
    resolvedThisMonthComparison?: string;
    pendingVerification: number;
    pendingComparison?: string;
    reportsToday?: number;
    reportsDeltaFromYesterday?: number;
    criticalActiveCount?: number;
    dispatchedCount?: number;
    resolvedThisWeek?: number;
    resolutionRate?: number;
};

type RecentIncident = {
    report_id: number;
    reference: string;
    barangay: string;
    type: string;
    status: ReportStatus;
    dateTime: string;
};

type MonthlyTrendPoint = {
    month: string;
    incidents: number;
    resolved?: number;
};

type IncidentTypeSlice = {
    type: string;
    label: string;
    count: number;
    percentage: number;
    color: string;
};

export default function Dashboard({
    kpis,
    recentIncidents,
    barangayRisk,
    monthlyTrend,
    incidentsByType,
}: {
    kpis: Kpis;
    recentIncidents: RecentIncident[];
    barangayRisk: BarangayRiskPoint[];
    monthlyTrend: MonthlyTrendPoint[];
    incidentsByType: IncidentTypeSlice[];
}) {
    return (
        <PortalLayout
            title="Dashboard"
            subtitle="BFP Lian Municipal Fire Station"
        >
            <div className="space-y-6">
                {/* KPI Cards */}
                <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <KPICard
                        label="Active Incidents"
                        value={kpis.activeIncidents}
                        sub={kpis.activeComparison}
                        icon={Flame}
                        accent="#E63946"
                        bgAccent="rgba(230, 57, 70, 0.12)"
                        href={`${incidents()}?status=accepted`}
                    />
                    <KPICard
                        label="Pending Verification"
                        value={kpis.pendingVerification}
                        sub={kpis.pendingComparison}
                        icon={ClipboardList}
                        accent="#D97706"
                        bgAccent="rgba(217, 119, 6, 0.12)"
                        href={`${incidents()}?status=pending`}
                    />
                    <KPICard
                        label="Resolved Today"
                        value={kpis.resolvedToday}
                        sub={kpis.resolvedTodayComparison}
                        icon={CheckCircle2}
                        accent="#10B981"
                        bgAccent="rgba(16, 185, 129, 0.12)"
                        href={`${incidents()}?status=resolved`}
                    />
                    <KPICard
                        label="Resolved this Month"
                        value={kpis.resolvedThisMonth}
                        sub={kpis.resolvedThisMonthComparison}
                        icon={Calendar}
                        accent="#3B82F6"
                        bgAccent="rgba(59, 130, 246, 0.12)"
                        href={`${incidents()}?status=resolved`}
                    />
                </div>

                <div className="grid items-stretch gap-4 lg:grid-cols-3">
                    {/* Risk map */}
                    <PortalCard className="flex min-h-[420px] flex-col overflow-hidden lg:col-span-2">
                        <div
                            className="flex items-center justify-between border-b px-4 py-4"
                            style={{ borderColor: 'rgba(43,45,66,0.08)' }}
                        >
                            <h2 className="text-sm font-bold text-brand-navy">
                                Barangay Risk Map
                            </h2>
                            <Link
                                href={fireProne()}
                                className="flex items-center gap-1 text-xs font-semibold text-brand-blue"
                            >
                                Expand <ChevronRight size={13} />
                            </Link>
                        </div>
                        <div className="relative flex min-h-[340px] flex-1">
                            <BarangayRiskMap
                                barangays={barangayRisk}
                                className="h-full min-h-[340px] rounded-none"
                            />
                            <div className="absolute top-3 right-3 z-[500] space-y-2 rounded-lg bg-white/90 px-3 py-2.5 shadow-md backdrop-blur-sm">
                                {(['mild', 'moderate', 'high'] as const).map(
                                    (level) => (
                                        <div
                                            key={level}
                                            className="flex items-center gap-2"
                                        >
                                            <span
                                                className="size-2.5 shrink-0 rounded-full"
                                                style={{
                                                    background:
                                                        RISK_CFG[level].color,
                                                }}
                                            />
                                            <span className="text-xs font-medium text-brand-navy">
                                                {RISK_CFG[level].label}
                                            </span>
                                        </div>
                                    ),
                                )}
                            </div>
                        </div>
                    </PortalCard>

                    {/* Recent incidents list */}
                    <PortalCard className="flex min-h-[420px] flex-col overflow-hidden">
                        <div
                            className="flex items-center justify-between border-b px-5 py-4"
                            style={{ borderColor: 'rgba(43,45,66,0.08)' }}
                        >
                            <h2 className="text-sm font-bold text-brand-navy">
                                Recent Incident Reports
                            </h2>
                            <Link
                                href={incidents()}
                                className="flex items-center gap-1 text-xs font-semibold text-brand-blue"
                            >
                                View all <ChevronRight size={13} />
                            </Link>
                        </div>
                        <div className="flex-1 overflow-y-auto">
                            {recentIncidents.length === 0 && (
                                <div className="flex h-full min-h-40 items-center justify-center px-4 py-6 text-center text-xs text-brand-muted">
                                    No incident records yet.
                                </div>
                            )}
                            {recentIncidents.map((inc) => (
                                <button
                                    key={inc.report_id}
                                    type="button"
                                    onClick={() =>
                                        router.visit(
                                            showIncident(inc.report_id),
                                        )
                                    }
                                    className="flex w-full cursor-pointer items-center gap-3 border-b px-4 py-3.5 text-left transition-colors last:border-b-0 hover:bg-gray-50"
                                    style={{
                                        borderColor: 'rgba(43,45,66,0.06)',
                                    }}
                                >
                                    <span className="min-w-0 flex-1">
                                        <span className="block truncate text-sm font-bold text-brand-navy">
                                            {inc.barangay}
                                        </span>
                                        <span className="mt-1 flex min-w-0 items-center gap-1.5 text-xs text-brand-muted">
                                            <span className="truncate">
                                                {inc.type}
                                            </span>
                                            <span
                                                className="shrink-0"
                                                aria-hidden="true"
                                            >
                                                &bull;
                                            </span>
                                            <span className="truncate font-mono">
                                                {inc.dateTime}
                                            </span>
                                        </span>
                                    </span>
                                    <span className="shrink-0 whitespace-nowrap">
                                        <StatusBadge status={inc.status} />
                                    </span>
                                </button>
                            ))}
                        </div>
                    </PortalCard>
                </div>

                {/* Monthly trend and Incidents by Type */}
                <div className="grid items-stretch gap-4 lg:grid-cols-3">
                    {/* Monthly trend */}
                    <PortalCard className="flex min-h-[320px] flex-col lg:col-span-2">
                        <div
                            className="border-b px-5 py-4"
                            style={{ borderColor: 'rgba(43,45,66,0.08)' }}
                        >
                            <h2 className="text-sm font-bold text-brand-navy">
                                Monthly Incident Trend
                            </h2>
                        </div>
                        <div className="min-h-[240px] flex-1 px-2 pt-4 pb-2">
                            <ResponsiveContainer width="100%" height="100%">
                                <AreaChart
                                    data={monthlyTrend}
                                    margin={{
                                        top: 4,
                                        right: 4,
                                        bottom: 0,
                                        left: -8,
                                    }}
                                >
                                    <defs>
                                        <linearGradient
                                            id="dashboardTrendGradient"
                                            x1="0"
                                            y1="0"
                                            x2="0"
                                            y2="1"
                                        >
                                            <stop
                                                offset="5%"
                                                stopColor="#1D3557"
                                                stopOpacity={0.28}
                                            />
                                            <stop
                                                offset="95%"
                                                stopColor="#1D3557"
                                                stopOpacity={0.02}
                                            />
                                        </linearGradient>
                                    </defs>
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
                                        allowDecimals={false}
                                        tick={{ fontSize: 11, fill: '#6B7A8D' }}
                                        axisLine={false}
                                        tickLine={false}
                                        width={24}
                                    />
                                    <Tooltip
                                        contentStyle={{
                                            fontSize: 12,
                                            borderRadius: 8,
                                            border: '1px solid rgba(43,45,66,0.1)',
                                            boxShadow:
                                                '0 4px 12px rgba(29, 53, 87, 0.08)',
                                        }}
                                        formatter={(value: number) => [
                                            `${value} incident${value === 1 ? '' : 's'}`,
                                            'Incidents',
                                        ]}
                                    />
                                    <Area
                                        type="monotone"
                                        dataKey="incidents"
                                        name="Incidents"
                                        stroke="#1D3557"
                                        strokeWidth={2.5}
                                        fillOpacity={1}
                                        fill="url(#dashboardTrendGradient)"
                                        dot={{
                                            r: 3.5,
                                            fill: '#1D3557',
                                            stroke: '#ffffff',
                                            strokeWidth: 1.5,
                                        }}
                                        activeDot={{
                                            r: 5.5,
                                            fill: '#1D3557',
                                            stroke: '#ffffff',
                                            strokeWidth: 2,
                                        }}
                                    />
                                </AreaChart>
                            </ResponsiveContainer>
                        </div>
                    </PortalCard>

                    {/* Incidents by Type */}
                    <PortalCard className="flex min-h-[320px] flex-col">
                        <div
                            className="border-b px-5 py-4"
                            style={{ borderColor: 'rgba(43,45,66,0.08)' }}
                        >
                            <h2 className="text-sm font-bold text-brand-navy">
                                Incidents by Type
                            </h2>
                        </div>
                        <div className="flex flex-1 p-5">
                            {incidentsByType.length === 0 ? (
                                <div className="flex min-h-[220px] flex-1 items-center justify-center">
                                    <p className="text-xs text-brand-muted">
                                        No classified incidents yet.
                                    </p>
                                </div>
                            ) : (
                                <div className="flex min-w-0 flex-1 flex-col gap-4 sm:flex-row sm:items-stretch">
                                    <div className="space-y-2 sm:w-[46%] sm:self-start">
                                        {incidentsByType.map((slice) => (
                                            <div
                                                key={slice.type}
                                                className="flex items-center gap-2 text-xs"
                                            >
                                                <span
                                                    className="size-2 shrink-0 rounded-full"
                                                    style={{
                                                        background: slice.color,
                                                    }}
                                                />
                                                <span className="min-w-0 flex-1 truncate text-brand-navy">
                                                    {slice.label}
                                                </span>
                                                <span className="shrink-0 font-mono text-xs font-semibold text-brand-muted">
                                                    {slice.percentage}%
                                                </span>
                                            </div>
                                        ))}
                                    </div>
                                    <div className="min-h-[200px] min-w-0 flex-1 sm:min-h-0">
                                        <ResponsiveContainer
                                            width="100%"
                                            height="100%"
                                        >
                                            <PieChart>
                                                <Pie
                                                    data={incidentsByType}
                                                    dataKey="count"
                                                    nameKey="label"
                                                    innerRadius="52%"
                                                    outerRadius="84%"
                                                    paddingAngle={2}
                                                    stroke="none"
                                                >
                                                    {incidentsByType.map(
                                                        (slice) => (
                                                            <Cell
                                                                key={slice.type}
                                                                fill={
                                                                    slice.color
                                                                }
                                                            />
                                                        ),
                                                    )}
                                                </Pie>
                                                <Tooltip
                                                    contentStyle={{
                                                        fontSize: 12,
                                                        borderRadius: 8,
                                                        border: '1px solid rgba(43,45,66,0.1)',
                                                        boxShadow:
                                                            '0 4px 12px rgba(29, 53, 87, 0.08)',
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
                                </div>
                            )}
                        </div>
                    </PortalCard>
                </div>
            </div>
        </PortalLayout>
    );
}
