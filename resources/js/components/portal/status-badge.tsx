import {
    STATUS_CFG,
    SEVERITY_CFG,
    RISK_CFG,
    ANNOUNCEMENT_CFG,
    ROLE_CFG,
    PERSONNEL_STATUS_CFG,
    EDUCATION_CATEGORY_CFG,
} from '@/lib/fire-status';
import type {
    ReportStatus,
    SeverityLevel,
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

export function SeverityBadge({ severity }: { severity: SeverityLevel }) {
    const cfg = SEVERITY_CFG[severity];

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
