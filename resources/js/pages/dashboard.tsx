import { Link, router } from '@inertiajs/react';
import { AlertTriangle, CheckCircle, ChevronRight, Clock, FileText } from 'lucide-react';
import { Bar, BarChart, CartesianGrid, Legend, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';
import { BarangayRiskMap, type BarangayRiskPoint } from '@/components/dashboard/barangay-risk-map';
import { KPICard } from '@/components/portal/kpi-card';
import { PortalCard } from '@/components/portal/portal-card';
import { StatusBadge } from '@/components/portal/status-badge';
import PortalLayout from '@/layouts/portal-layout';
import { RISK_CFG } from '@/lib/fire-status';
import type { ReportStatus } from '@/lib/fire-status';
import { map as fireMap } from '@/routes';
import { index as incidents, show as showIncident } from '@/routes/incidents';

type Kpis = {
    reportsToday: number;
    reportsDeltaFromYesterday: number;
    pendingVerification: number;
    activeIncidents: number;
    criticalActiveCount: number;
    dispatchedCount: number;
    resolvedThisWeek: number;
    resolutionRate: number;
};

type RecentIncident = {
    report_id: number;
    reference: string;
    barangay: string;
    type: string;
    status: ReportStatus;
    dateTime: string;
};

type MonthlyTrendPoint = { month: string; incidents: number; resolved: number };

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
    const deltaLabel =
        kpis.reportsDeltaFromYesterday === 0
            ? 'Same as yesterday'
            : `${kpis.reportsDeltaFromYesterday > 0 ? '+' : ''}${kpis.reportsDeltaFromYesterday} from yesterday`;

    return (
        <PortalLayout title="Dashboard" subtitle="BFP Lian Municipal Fire Station">
            <div className="space-y-6">
                {/* KPI Cards */}
                <div className="grid grid-cols-2 gap-4 lg:grid-cols-3">
                    <KPICard
                        label="Total Reports Today"
                        value={kpis.reportsToday}
                        sub={deltaLabel}
                        icon={FileText}
                        accent="#457B9D"
                    />
                    <KPICard
                        label="Pending Verification"
                        value={kpis.pendingVerification}
                        sub="Requires attention"
                        icon={Clock}
                        accent="#F4A261"
                    />
                    <KPICard
                        label="Active Incidents"
                        value={kpis.activeIncidents}
                        sub={`${kpis.criticalActiveCount} critical, ${kpis.dispatchedCount} dispatched`}
                        icon={AlertTriangle}
                        accent="#E63946"
                    />
                </div>

                <div className="grid gap-4 lg:grid-cols-2">
                    {/* Recent incidents table */}
                    <PortalCard className="flex flex-col">
                        <div
                            className="flex items-center justify-between border-b px-5 py-4"
                            style={{ borderColor: 'rgba(43,45,66,0.08)' }}
                        >
                            <h2 className="text-sm font-bold text-brand-navy">Recent Incident Reports</h2>
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
                                        {['Reference ID', 'Barangay', 'Type', 'Status', 'Date / Time'].map((h) => (
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
                                            <td colSpan={5} className="px-4 py-6 text-center text-xs text-brand-muted">
                                                No incident records yet.
                                            </td>
                                        </tr>
                                    )}
                                    {recentIncidents.map((inc) => (
                                        <tr
                                            key={inc.reference}
                                            onClick={() => router.visit(showIncident(inc.report_id))}
                                            className="cursor-pointer border-t transition-colors hover:bg-gray-50"
                                            style={{ borderColor: 'rgba(43,45,66,0.06)' }}
                                        >
                                            <td className="px-4 py-3 font-mono text-xs font-medium text-brand-blue">
                                                {inc.reference}
                                            </td>
                                            <td className="px-4 py-3 text-xs text-brand-navy">{inc.barangay}</td>
                                            <td className="px-4 py-3 text-xs text-brand-navy">{inc.type}</td>
                                            <td className="px-4 py-3">
                                                <StatusBadge status={inc.status} />
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
                            className="flex items-center justify-between px-4 py-4 border-b"
                            style={{ borderColor: 'rgba(43,45,66,0.08)' }}
                        >
                            <h2 className="text-sm font-bold text-brand-navy">Barangay Risk Map</h2>
                            <Link
                                href={fireMap()}
                                className="flex items-center gap-1 text-xs font-semibold text-brand-blue"
                            >
                                Expand <ChevronRight size={13} />
                            </Link>
                        </div>
                        <div className="flex flex-1 flex-col gap-3 p-4">
                            <BarangayRiskMap barangays={barangayRisk} height={220} />
                            <div className="flex items-center gap-4 px-1">
                                {(['low', 'moderate', 'high', 'severe'] as const).map((level) => (
                                    <div key={level} className="flex items-center gap-1.5">
                                        <div
                                            className="size-2.5 rounded-full"
                                            style={{ background: RISK_CFG[level].color }}
                                        />
                                        <span className="text-xs text-brand-muted">{RISK_CFG[level].label}</span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </PortalCard>
                </div>

                {/* Monthly trend */}
                <PortalCard>
                    <div className="border-b px-5 py-4" style={{ borderColor: 'rgba(43,45,66,0.08)' }}>
                        <h2 className="text-sm font-bold text-brand-navy">Monthly Incident Trend</h2>
                    </div>
                    <div className="p-5">
                        <ResponsiveContainer width="100%" height={180}>
                            <BarChart data={monthlyTrend} margin={{ top: 4, right: 4, bottom: 0, left: 0 }} barCategoryGap="32%" barGap={3}>
                                <CartesianGrid strokeDasharray="3 3" stroke="rgba(43,45,66,0.06)" vertical={false} />
                                <XAxis dataKey="month" tick={{ fontSize: 11, fill: '#6B7A8D' }} axisLine={false} tickLine={false} />
                                <YAxis tick={{ fontSize: 11, fill: '#6B7A8D' }} axisLine={false} tickLine={false} width={28} />
                                <Tooltip contentStyle={{ fontSize: 12, borderRadius: 8, border: '1px solid rgba(43,45,66,0.1)' }} />
                                <Legend wrapperStyle={{ fontSize: 11 }} />
                                <Bar dataKey="incidents" name="Incidents" fill="#1D3557" radius={[3, 3, 0, 0]} />
                                <Bar dataKey="resolved" name="Resolved" fill="#2A9D8F" radius={[3, 3, 0, 0]} />
                            </BarChart>
                        </ResponsiveContainer>
                    </div>
                </PortalCard>
            </div>
        </PortalLayout>
    );
}
