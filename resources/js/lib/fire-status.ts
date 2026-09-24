/**
 * Shared color/label config for the enums used across community_report,
 * incident_record, risk_assessment, and announcement. Centralized here so
 * every page (Dashboard, Incidents, Map, Analytics, Announcements, …)
 * renders the same badge for the same value.
 */

export type ReportStatus =
    | 'pending'
    | 'accepted'
    | 'dispatched'
    | 'resolved'
    | 'invalid';
export type SeverityLevel = 'low' | 'moderate' | 'high' | 'critical';
export type RiskLevel = 'low' | 'moderate' | 'high' | 'critical';
export type AnnouncementType =
    | 'general'
    | 'advisory'
    | 'emergency'
    | 'fire_safety_tip';
export type FireEducationCategory =
    | 'prevention'
    | 'emergency_response'
    | 'awareness';
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
    accepted: {
        bg: '#E0F5F3',
        text: '#1B7A72',
        border: '#2A9D8F',
        label: 'Accepted',
    },
    dispatched: {
        bg: '#E3EFF7',
        text: '#2C5F82',
        border: '#457B9D',
        label: 'Dispatched',
    },
    resolved: {
        bg: '#DCFCE7',
        text: '#15803D',
        border: '#22C55E',
        label: 'Resolved',
    },
    invalid: {
        bg: '#FDE8EA',
        text: '#B91C2C',
        border: '#E63946',
        label: 'Invalid',
    },
};

export const SEVERITY_CFG: Record<SeverityLevel, BadgeCfg> = {
    critical: { bg: '#FEE2E2', text: '#DC2626', label: 'Critical' },
    high: { bg: '#FFEDD5', text: '#EA580C', label: 'High' },
    moderate: { bg: '#FEF9C3', text: '#854D0E', label: 'Moderate' },
    low: { bg: '#DCFCE7', text: '#16A34A', label: 'Low' },
};

export const RISK_CFG: Record<RiskLevel, BadgeCfg & { color: string }> = {
    critical: {
        bg: '#FEE2E2',
        text: '#DC2626',
        label: 'Critical',
        color: '#DC2626',
    },
    high: { bg: '#FFEDD5', text: '#EA580C', label: 'High', color: '#F97316' },
    moderate: {
        bg: '#FEF9C3',
        text: '#854D0E',
        label: 'Moderate',
        color: '#EAB308',
    },
    low: { bg: '#DCFCE7', text: '#16A34A', label: 'Low', color: '#16A34A' },
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

export const EDUCATION_CATEGORY_CFG: Record<FireEducationCategory, BadgeCfg> = {
    prevention: { bg: '#E0F5F3', text: '#1B7A72', label: 'Prevention' },
    emergency_response: { bg: '#FDE8EA', text: '#B91C2C', label: 'Emergency Response' },
    awareness: { bg: '#E3EFF7', text: '#2C5F82', label: 'Awareness' },
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

export type IncidentType =
    | 'residential_fire'
    | 'commercial_fire'
    | 'vehicular_fire'
    | 'storage_fire'
    | 'rubbish_fire'
    | 'others';

type TypeCfg = { label: string; color: string };

export const TYPE_CFG: Record<IncidentType, TypeCfg> = {
    residential_fire: { label: 'Residential Fire', color: '#E63946' },
    commercial_fire: { label: 'Commercial Fire', color: '#D62828' },
    vehicular_fire: { label: 'Vehicular Fire', color: '#F4A261' },
    storage_fire: { label: 'Storage Fire', color: '#7B2CBF' },
    rubbish_fire: { label: 'Rubbish Fire', color: '#F77F00' },
    others: { label: 'Others', color: '#457B9D' },
};

/**
 * Map marker fill colors for severity levels — matches Analytics'
 * SEVERITY_COLORS constants so pins and charts use identical hues.
 */
export const SEVERITY_MARKER_COLORS: Record<SeverityLevel, string> = {
    critical: '#DC2626',
    high: '#F97316',
    moderate: '#EAB308',
    low: '#16A34A',
};

/** Marker/legend color for a risk level — used by Leaflet map components. */
export function riskLevelColor(level: RiskLevel): string {
    return RISK_CFG[level]?.color ?? '#6B7A8D';
}
