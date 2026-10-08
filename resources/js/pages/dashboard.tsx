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
import { map as fireMap } from '@/routes';
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

export default function Dashboard({
    kpis,
    recentIncidents,
    barangayRisk,
    monthlyTrend,
}: {
    kpis: Kpis;
    recentIncidents: RecentIncident[];
    barangayRisk: BarangayRiskPoint[];
    monthlyTrend: MonthlyTrendPoint[];
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

                <div className="grid gap-4 lg:grid-cols-2">
                    {/* Recent incidents table */}
                    <PortalCard className="flex flex-col">
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
                        <div className="flex-1 overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead>
                                    <tr style={{ background: '#FAFBFC' }}>
                                        {[
                                            'Barangay',
                                            'Type',
                                            'Status',
                                            'Date / Time',
                                        ].map((h) => (
                                            <th
                                                key={h}
                                                className="px-4 py-2.5 text-left text-xs font-semibold tracking-wider text-brand-muted uppercase"
                                            >
                                                {h}
                                            </th>
                                        ))}
                                    </tr>
                                </thead>
                                <tbody>
                                    {recentIncidents.length === 0 && (
                                        <tr>
                                            <td
                                                colSpan={4}
                                                className="px-4 py-6 text-center text-xs text-brand-muted"
                                            >
                                                No incident records yet.
                                            </td>
                                        </tr>
                                    )}
                                    {recentIncidents.map((inc) => (
                                        <tr
                                            key={inc.report_id}
                                            onClick={() =>
                                                router.visit(
                                                    showIncident(inc.report_id),
                                                )
                                            }
                                            className="cursor-pointer border-t transition-colors hover:bg-gray-50"
                                            style={{
                                                borderColor:
                                                    'rgba(43,45,66,0.06)',
                                            }}
                                        >
                                            <td className="px-4 py-3 text-xs text-brand-navy">
                                                {inc.barangay}
                                            </td>
                                            <td className="px-4 py-3 text-xs text-brand-navy">
                                                {inc.type}
                                            </td>
                                            <td className="px-4 py-3">
                                                <StatusBadge
                                                    status={inc.status}
                                                />
                                            </td>
                                            <td className="px-4 py-3 font-mono text-xs text-brand-muted">
                                                {inc.dateTime}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </PortalCard>

                    {/* Risk map */}
                    <PortalCard className="flex flex-col">
                        <div
                            className="flex items-center justify-between border-b px-4 py-4"
                            style={{ borderColor: 'rgba(43,45,66,0.08)' }}
                        >
                            <h2 className="text-sm font-bold text-brand-navy">
                                Barangay Risk Map
                            </h2>
                            <Link
                                href={fireMap()}
                                className="flex items-center gap-1 text-xs font-semibold text-brand-blue"
                            >
                                Expand <ChevronRight size={13} />
                            </Link>
                        </div>
                        <div className="flex flex-1 flex-col gap-3 p-4">
                            <BarangayRiskMap
                                barangays={barangayRisk}
                                height={220}
                            />
                            <div className="flex items-center gap-4 px-1">
                                {(
                                    [
                                        'low',
                                        'moderate',
                                        'high',
                                        'critical',
                                    ] as const
                                ).map((level) => (
                                    <div
                                        key={level}
                                        className="flex items-center gap-1.5"
                                    >
                                        <div
                                            className="size-2.5 rounded-full"
                                            style={{
                                                background:
                                                    RISK_CFG[level].color,
                                            }}
                                        />
                                        <span className="text-xs text-brand-muted">
                                            {RISK_CFG[level].label}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </PortalCard>
                </div>

                {/* Monthly trend */}
                <PortalCard>
                    <div
                        className="border-b px-5 py-4"
                        style={{ borderColor: 'rgba(43,45,66,0.08)' }}
                    >
                        <h2 className="text-sm font-bold text-brand-navy">
                            Monthly Incident Trend
                        </h2>
                    </div>
                    <div className="p-5">
                        <ResponsiveContainer width="100%" height={180}>
                            <AreaChart
                                data={monthlyTrend}
                                margin={{
                                    top: 8,
                                    right: 8,
                                    bottom: 0,
                                    left: 0,
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
                                    width={28}
                                />
                                <Tooltip
                                    contentStyle={{
                                        fontSize: 12,
                                        borderRadius: 8,
                                        border: '1px solid rgba(43,45,66,0.1)',
                                        boxShadow: '0 4px 12px rgba(29, 53, 87, 0.08)',
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
            </div>
        </PortalLayout>
    );
}
