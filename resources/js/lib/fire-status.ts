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
export type AlarmLevel =
    | '1st_alarm'
    | '2nd_alarm'
    | '3rd_alarm'
    | '4th_alarm'
    | '5th_alarm'
    | 'task_force_alpha'
    | 'task_force_bravo'
    | 'task_force_charlie'
    | 'task_force_delta'
    | 'task_force_echo'
    | 'task_force_hotel'
    | 'task_force_india'
    | 'general_alarm';
export type RiskLevel = 'mild' | 'moderate' | 'high';
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

export const ALARM_LEVEL_CFG: Record<AlarmLevel, BadgeCfg> = {
    '1st_alarm': { bg: '#D1FAE5', text: '#065F46', label: '1st Alarm' },
    '2nd_alarm': { bg: '#D1FAE5', text: '#047857', label: '2nd Alarm' },
    '3rd_alarm': { bg: '#FEF3C7', text: '#92400E', label: '3rd Alarm' },
    '4th_alarm': { bg: '#FEF3C7', text: '#B45309', label: '4th Alarm' },
    '5th_alarm': { bg: '#FFEDD5', text: '#C2410C', label: '5th Alarm' },
    task_force_alpha: { bg: '#FFEDD5', text: '#9A3412', label: 'Task Force Alpha' },
    task_force_bravo: { bg: '#FEE2E2', text: '#B91C1C', label: 'Task Force Bravo' },
    task_force_charlie: { bg: '#FEE2E2', text: '#991B1B', label: 'Task Force Charlie' },
    task_force_delta: { bg: '#FEE2E2', text: '#7F1D1D', label: 'Task Force Delta' },
    task_force_echo: { bg: '#FFE4E6', text: '#881337', label: 'Task Force Echo' },
    task_force_hotel: { bg: '#FFE4E6', text: '#881337', label: 'Task Force Hotel' },
    task_force_india: { bg: '#FFE4E6', text: '#4C0519', label: 'Task Force India' },
    general_alarm: { bg: '#FFE4E6', text: '#4C0519', label: 'General Alarm' },
};

export const RISK_CFG: Record<RiskLevel, BadgeCfg & { color: string }> = {
    high: {
        bg: '#FEE2E2',
        text: '#DC2626',
        label: 'High',
        color: '#DC2626',
    },
    moderate: {
        bg: '#FFEDD5',
        text: '#EA580C',
        label: 'Moderate',
        color: '#F97316',
    },
    mild: {
        bg: '#FEF9C3',
        text: '#854D0E',
        label: 'Mild',
        color: '#EAB308',
    },
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
    residential_fire: { label: 'Residential Fire', color: '#1D3557' },
    commercial_fire: { label: 'Commercial Fire', color: '#1E4D5B' },
    vehicular_fire: { label: 'Vehicular Fire', color: '#236B6E' },
    storage_fire: { label: 'Storage Fire', color: '#2A9D8F' },
    rubbish_fire: { label: 'Rubbish Fire', color: '#48B6A3' },
    others: { label: 'Others', color: '#76CEBF' },
};

/**
 * Map marker fill colors for alarm levels — matches Analytics'
 * ALARM_LEVEL_COLORS constants so pins and charts use identical hues.
 */
export const ALARM_LEVEL_MARKER_COLORS: Record<AlarmLevel, string> = {
    '1st_alarm': '#34D399',
    '2nd_alarm': '#10B981',
    '3rd_alarm': '#FBBF24',
    '4th_alarm': '#F59E0B',
    '5th_alarm': '#F97316',
    task_force_alpha: '#EA580C',
    task_force_bravo: '#EF4444',
    task_force_charlie: '#DC2626',
    task_force_delta: '#B91C1C',
    task_force_echo: '#991B1B',
    task_force_hotel: '#BE123C',
    task_force_india: '#9F1239',
    general_alarm: '#881337',
};

/** Marker/legend color for a risk level — used by Leaflet map components. */
export function riskLevelColor(level: RiskLevel): string {
    return RISK_CFG[level]?.color ?? '#6B7A8D';
}

/**
 * Leaflet polygon styling for risk map layers.
 * Uses lowered opacity and soft borders so polygon fills never clash with
 * the portal theme or drown out underlying street map details.
 */
export function riskMapPolygonStyle(level: RiskLevel | null | undefined): {
    color: string;
    weight: number;
    opacity: number;
    fillColor: string;
    fillOpacity: number;
} {
    const color = level ? riskLevelColor(level) : '#6B7A8D';

    return {
        color,
        weight: 1.5,
        opacity: 0.6,
        fillColor: color,
        fillOpacity: level ? 0.22 : 0.1,
    };
}

// ─── Mobile App AI Fire Verification Config ──────────────────────────────────

export type AiFireLabel = 'fire' | 'smoke' | 'no_fire';

export type AiFireConfig = {
    label: string;
    bg: string;
    text: string;
    border: string;
    badgeBg: string;
    badgeText: string;
};

export const AI_FIRE_LABEL_CFG: Record<string, AiFireConfig> = {
    fire: {
        label: 'Fire Detected',
        bg: 'rgba(230, 57, 70, 0.08)',
        text: '#E63946',
        border: 'rgba(230, 57, 70, 0.25)',
        badgeBg: '#FEE2E2',
        badgeText: '#DC2626',
    },
    smoke: {
        label: 'Smoke Detected',
        bg: 'rgba(244, 162, 97, 0.08)',
        text: '#D97706',
        border: 'rgba(244, 162, 97, 0.25)',
        badgeBg: '#FFEDD5',
        badgeText: '#EA580C',
    },
    no_fire: {
        label: 'No Fire Detected',
        bg: 'rgba(34, 197, 94, 0.08)',
        text: '#16A34A',
        border: 'rgba(34, 197, 94, 0.25)',
        badgeBg: '#DCFCE7',
        badgeText: '#15803D',
    },
};

