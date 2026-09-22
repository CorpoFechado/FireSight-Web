/**
 * Shared color/label config for the enums used across community_report,
 * incident_record, risk_assessment, and announcement. Centralized here so
 * every page (Dashboard, Incidents, Map, Analytics, Announcements, …)
 * renders the same badge for the same value.
 */

export type ReportStatus =
    'pending' | 'verified' | 'rejected' | 'dispatched' | 'resolved' | 'completed';
export type SeverityLevel = 'low' | 'moderate' | 'high' | 'critical';
export type RiskLevel = 'low' | 'moderate' | 'high' | 'severe';
export type AnnouncementType =
    'general' | 'advisory' | 'emergency' | 'fire_safety_tip';
export type PersonnelRole = 'bfp_admin' | 'bfp_personnel';
export type PersonnelStatus = 'active' | 'inactive';

type BadgeCfg = { bg: string; text: string; border?: string; label: string };

export const STATUS_CFG: Record<ReportStatus, BadgeCfg> = {
    pending: {
        bg: '#FFF3E0',
        text: '#B75E0A',
        border: '#F4A261',
        label: 'Pending',
    },
    verified: {
        bg: '#E0F5F3',
        text: '#1B7A72',
        border: '#2A9D8F',
        label: 'Verified',
    },
    dispatched: {
        bg: '#E3EFF7',
        text: '#2C5F82',
        border: '#457B9D',
        label: 'Dispatched',
    },
    resolved: {
        bg: '#F3F4F6',
        text: '#4B5563',
        border: '#9CA3AF',
        label: 'Resolved',
    },
    completed: {
        bg: '#DCFCE7',
        text: '#15803D',
        border: '#22C55E',
        label: 'Completed',
    },
    rejected: {
        bg: '#FDE8EA',
        text: '#B91C2C',
        border: '#E63946',
        label: 'Rejected',
    },
};

export const SEVERITY_CFG: Record<SeverityLevel, BadgeCfg> = {
    critical: { bg: '#FDE8EA', text: '#B91C2C', label: 'Critical' },
    high: { bg: '#FFF0E6', text: '#C2410C', label: 'High' },
    moderate: { bg: '#FFFBEB', text: '#92400E', label: 'Moderate' },
    low: { bg: '#E0F5F3', text: '#1B7A72', label: 'Low' },
};

export const RISK_CFG: Record<RiskLevel, BadgeCfg & { color: string }> = {
    severe: {
        bg: '#FDE8EA',
        text: '#B91C2C',
        label: 'Severe',
        color: '#E63946',
    },
    high: { bg: '#FFF0E6', text: '#C2410C', label: 'High', color: '#F77F00' },
    moderate: {
        bg: '#FFFBEB',
        text: '#92400E',
        label: 'Moderate',
        color: '#F4A261',
    },
    low: { bg: '#E0F5F3', text: '#1B7A72', label: 'Low', color: '#2A9D8F' },
};

export const ANNOUNCEMENT_CFG: Record<AnnouncementType, BadgeCfg> = {
    general: { bg: '#E3EFF7', text: '#2C5F82', label: 'General' },
    advisory: { bg: '#FFFBEB', text: '#92400E', label: 'Advisory' },
    emergency: { bg: '#FDE8EA', text: '#B91C2C', label: 'Emergency' },
    fire_safety_tip: {
        bg: '#E0F5F3',
        text: '#1B7A72',
        label: 'Fire Safety',
    },
};

export const ROLE_CFG: Record<PersonnelRole, BadgeCfg> = {
    bfp_admin: { bg: '#F3F4F6', text: '#1D3557', label: 'Admin' },
    bfp_personnel: { bg: '#E3EFF7', text: '#2C5F82', label: 'Personnel' },
};

export const PERSONNEL_STATUS_CFG: Record<PersonnelStatus, BadgeCfg> = {
    active: { bg: '#E0F5F3', text: '#1B7A72', label: 'Active' },
    inactive: { bg: '#F3F4F6', text: '#4B5563', label: 'Inactive' },
};

// ─── Incident type config (matches AnalyticsController::TYPE_LABELS/COLORS) ──

export type IncidentType = 'structural' | 'grass' | 'electrical' | 'vehicular' | 'other';

type TypeCfg = { label: string; color: string };

export const TYPE_CFG: Record<IncidentType, TypeCfg> = {
    structural: { label: 'Structure Fire', color: '#E63946' },
    grass: { label: 'Grass/Vegetation', color: '#F77F00' },
    vehicular: { label: 'Vehicle Fire', color: '#F4A261' },
    electrical: { label: 'Electrical Fire', color: '#457B9D' },
    other: { label: 'Other', color: '#868E96' },
};

/**
 * Map marker fill colors for severity levels — matches Analytics'
 * SEVERITY_COLORS constants so pins and charts use identical hues.
 */
export const SEVERITY_MARKER_COLORS: Record<SeverityLevel, string> = {
    critical: '#E63946',
    high: '#F77F00',
    moderate: '#F4A261',
    low: '#2A9D8F',
};

/** Marker/legend color for a risk level — used by Leaflet map components. */
export function riskLevelColor(level: RiskLevel): string {
    return RISK_CFG[level]?.color ?? '#6B7A8D';
}
