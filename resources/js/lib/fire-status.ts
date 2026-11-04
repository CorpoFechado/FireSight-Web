/**
 * Shared color/label config for the enums used across community reports,
 * incident records, risk assessments, fire education, and personnel.
 */

export type ReportStatus =
    'pending' | 'accepted' | 'dispatched' | 'resolved' | 'invalid';
export type AlarmLevel =
    | 'verification'
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
export type FireEducationCategory =
    'prevention' | 'emergency_response' | 'awareness';
export type PersonnelRole = 'bfp_admin' | 'bfp_personnel';
export type PersonnelStatus = 'active' | 'inactive';

type BadgeCfg = { bg: string; text: string; border?: string; label: string };
type AlarmLevelCfg = {
    backgroundClass: string;
    textClass: string;
    color: string;
    strokeColor?: string;
    label: string;
};

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

export const ALARM_LEVEL_CFG: Record<AlarmLevel, AlarmLevelCfg> = {
    verification: {
        backgroundClass: 'bg-alarm-verification-badge',
        textClass: 'text-alarm-verification-badge-foreground',
        color: 'var(--color-alarm-verification)',
        strokeColor: 'var(--color-alarm-verification-stroke)',
        label: 'Verification',
    },
    '1st_alarm': {
        backgroundClass: 'bg-alarm-1st',
        textClass: 'text-alarm-1st-foreground',
        color: 'var(--color-alarm-1st)',
        label: '1st Alarm',
    },
    '2nd_alarm': {
        backgroundClass: 'bg-alarm-2nd',
        textClass: 'text-alarm-2nd-foreground',
        color: 'var(--color-alarm-2nd)',
        label: '2nd Alarm',
    },
    '3rd_alarm': {
        backgroundClass: 'bg-alarm-3rd',
        textClass: 'text-alarm-3rd-foreground',
        color: 'var(--color-alarm-3rd)',
        label: '3rd Alarm',
    },
    '4th_alarm': {
        backgroundClass: 'bg-alarm-4th',
        textClass: 'text-alarm-4th-foreground',
        color: 'var(--color-alarm-4th)',
        label: '4th Alarm',
    },
    '5th_alarm': {
        backgroundClass: 'bg-alarm-5th',
        textClass: 'text-alarm-5th-foreground',
        color: 'var(--color-alarm-5th)',
        label: '5th Alarm',
    },
    task_force_alpha: {
        backgroundClass: 'bg-alarm-tf-alpha',
        textClass: 'text-alarm-tf-alpha-foreground',
        color: 'var(--color-alarm-tf-alpha)',
        label: 'Task Force Alpha',
    },
    task_force_bravo: {
        backgroundClass: 'bg-alarm-tf-bravo',
        textClass: 'text-alarm-tf-bravo-foreground',
        color: 'var(--color-alarm-tf-bravo)',
        label: 'Task Force Bravo',
    },
    task_force_charlie: {
        backgroundClass: 'bg-alarm-tf-charlie',
        textClass: 'text-alarm-tf-charlie-foreground',
        color: 'var(--color-alarm-tf-charlie)',
        label: 'Task Force Charlie',
    },
    task_force_delta: {
        backgroundClass: 'bg-alarm-tf-delta',
        textClass: 'text-alarm-tf-delta-foreground',
        color: 'var(--color-alarm-tf-delta)',
        label: 'Task Force Delta',
    },
    task_force_echo: {
        backgroundClass: 'bg-alarm-tf-echo',
        textClass: 'text-alarm-tf-echo-foreground',
        color: 'var(--color-alarm-tf-echo)',
        label: 'Task Force Echo',
    },
    task_force_hotel: {
        backgroundClass: 'bg-alarm-tf-hotel',
        textClass: 'text-alarm-tf-hotel-foreground',
        color: 'var(--color-alarm-tf-hotel)',
        label: 'Task Force Hotel',
    },
    task_force_india: {
        backgroundClass: 'bg-alarm-tf-india',
        textClass: 'text-alarm-tf-india-foreground',
        color: 'var(--color-alarm-tf-india)',
        label: 'Task Force India',
    },
    general_alarm: {
        backgroundClass: 'bg-alarm-general',
        textClass: 'text-alarm-general-foreground',
        color: 'var(--color-alarm-general)',
        label: 'General Alarm',
    },
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

export const EDUCATION_CATEGORY_CFG: Record<FireEducationCategory, BadgeCfg> = {
    prevention: { bg: '#E0F5F3', text: '#1B7A72', label: 'Prevention' },
    emergency_response: {
        bg: '#FDE8EA',
        text: '#B91C2C',
        label: 'Emergency Response',
    },
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
