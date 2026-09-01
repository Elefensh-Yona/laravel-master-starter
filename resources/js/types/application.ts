export type ManagedApplication = {
    id: number;
    programId: number;
    primaryOwnerId: number;
    applicantType: 'INDIVIDUAL' | 'TEAM' | 'ORGANIZATION' | string;
    status: string;
    reference: string | null;
    submittedAt: string | null;
    createdAt: string;
    canEdit?: boolean;
    canSubmit?: boolean;
    canRevise?: boolean;
};

export type ManagedApplicationVersion = {
    id: number;
    applicationId: number;
    versionNumber: number;
    status: string;
    submittedAt: string | null;
};

export type ManagedApplicationMember = {
    id: number;
    applicationId: number;
    userId: number;
    userName: string | null;
    userEmail: string | null;
    status: 'active' | 'ended' | string;
    joinedAt: string | null;
    endedAt: string | null;
    endReason: string | null;
};

export type ApplicationUserOption = {
    id: number;
    name: string;
    email: string;
};

export type ApplicationProgramOption = {
    id: number;
    name: string;
    code: string;
};
