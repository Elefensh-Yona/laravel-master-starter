export type ManagedProgram = {
    id: number;
    name: string;
    code: string;
    slug: string;
    status: string;
    timezone: string;
    opensAt: string;
    closesAt: string;
    description: string | null;
    publishedAt: string | null;
    canEdit: boolean;
    canPublish: boolean;
};
