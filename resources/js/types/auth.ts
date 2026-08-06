export type BfpRole = 'resident' | 'bfp_personnel' | 'bfp_admin';

export type PersonnelStatus = 'active' | 'inactive';

export type User = {
    id: number;
    role: BfpRole;
    status: PersonnelStatus;
    first_name: string;
    last_name: string;
    name: string;
    email: string;
    contact_number: string | null;
    username: string | null;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};

/* @chisel-passkeys */
export type Passkey = {
    id: number;
    name: string;
    authenticator: string | null;
    created_at_diff: string;
    last_used_at_diff: string | null;
};
/* @end-chisel-passkeys */

export type TwoFactorSetupData = {
    svg: string;
    url: string;
};

export type TwoFactorSecretKey = {
    secretKey: string;
};
