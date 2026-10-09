import {
    STATUS_CFG,
    ALARM_LEVEL_CFG,
    RISK_CFG,
    ANNOUNCEMENT_CFG,
    ROLE_CFG,
    PERSONNEL_STATUS_CFG,
    EDUCATION_CATEGORY_CFG,
    AI_FIRE_LABEL_CFG,
} from '@/lib/fire-status';
import type {
    ReportStatus,
    AlarmLevel,
    RiskLevel,
    AnnouncementType,
    PersonnelRole,
    PersonnelStatus,
    FireEducationCategory,
} from '@/lib/fire-status';

export function StatusBadge({ status }: { status: ReportStatus }) {
    const cfg = STATUS_CFG[status];

    return (
        <span
            className="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
            style={{ background: cfg.bg, color: cfg.text }}
        >
            <span
                className="size-1.5 rounded-full"
                style={{ background: cfg.text }}
            />
            {cfg.label}
        </span>
    );
}

export function AlarmLevelBadge({ alarmLevel }: { alarmLevel: AlarmLevel }) {
    const cfg = ALARM_LEVEL_CFG[alarmLevel];

    if (!cfg) {
        return (
            <span className="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold bg-gray-100 text-gray-700">
                {alarmLevel}
            </span>
        );
    }

    return (
        <span
            className="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
            style={{ background: cfg.bg, color: cfg.text }}
        >
            {cfg.label}
        </span>
    );
}

export function RiskBadge({ level }: { level: RiskLevel }) {
    const cfg = RISK_CFG[level];

    if (!cfg) {
        return (
            <span className="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold bg-gray-100 text-gray-700">
                {level}
            </span>
        );
    }

    return (
        <span
            className="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
            style={{ background: cfg.bg, color: cfg.text }}
        >
            {cfg.label}
        </span>
    );
}

export function AnnouncementBadge({ type }: { type: AnnouncementType }) {
    const cfg = ANNOUNCEMENT_CFG[type];

    return (
        <span
            className="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
            style={{ background: cfg.bg, color: cfg.text }}
        >
            {cfg.label}
        </span>
    );
}

export function RoleBadge({ role }: { role: PersonnelRole }) {
    const cfg = ROLE_CFG[role];

    return (
        <span
            className="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
            style={{ background: cfg.bg, color: cfg.text }}
        >
            {cfg.label}
        </span>
    );
}

export function PersonnelStatusBadge({ status }: { status: PersonnelStatus }) {
    const cfg = PERSONNEL_STATUS_CFG[status];

    return (
        <span
            className="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
            style={{ background: cfg.bg, color: cfg.text }}
        >
            <span
                className="size-1.5 rounded-full"
                style={{ background: cfg.text }}
            />
            {cfg.label}
        </span>
    );
}

export function EducationCategoryBadge({
    category,
}: {
    category: FireEducationCategory;
}) {
    const cfg = EDUCATION_CATEGORY_CFG[category] ?? {
        bg: '#F3F4F6',
        text: '#4B5563',
        label: category,
    };

    return (
        <span
            className="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
            style={{ background: cfg.bg, color: cfg.text }}
        >
            {cfg.label}
        </span>
    );
}

export function AiFireBadge({
    label,
    confidence,
    showConfidence = false,
}: {
    label: string | null;
    confidence?: number | null;
    showConfidence?: boolean;
}) {
    if (!label) {
        return (
            <span
                className="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium"
                style={{ background: '#F1F3F5', color: '#6B7A8D' }}
            >
                <span className="size-1.5 rounded-full bg-gray-400" />
                Unverified
            </span>
        );
    }

    const key = label.toLowerCase();
    const cfg = AI_FIRE_LABEL_CFG[key] ?? {
        label: label.replace(/_/g, ' '),
        badgeBg: '#F3F4F6',
        badgeText: '#4B5563',
    };

    const percent =
        confidence !== null && confidence !== undefined
            ? ` ${(confidence * 100).toFixed(0)}%`
            : '';

    return (
        <span
            className="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold"
            style={{ background: cfg.badgeBg, color: cfg.badgeText }}
        >
            <span
                className="size-1.5 rounded-full"
                style={{ background: cfg.badgeText }}
            />
            {cfg.label}
            {showConfidence && percent ? ` · ${percent.trim()}` : ''}
        </span>
    );
}

