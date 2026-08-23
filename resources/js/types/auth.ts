import type { ManagedNotification } from './admin';

export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User | null;
    roles: string[];
    permissions: string[];
    can: {
        viewDashboard: boolean;
        viewSearch: boolean;
        viewExports: boolean;
        manageSettings: boolean;
        manageUsers: boolean;
        manageRoles: boolean;
        viewNotifications: boolean;
        viewActivityLogs: boolean;
    };
    notificationCount: number;
    notificationPreview: ManagedNotification[];
};

export type SharedFlash = {
    success?: string | null;
    error?: string | null;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
