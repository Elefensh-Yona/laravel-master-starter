export type ManagedRole = {
    id: number;
    name: string;
    description: string | null;
    permissions: string[];
    usersCount: number;
    permissionsCount: number;
    isSystem: boolean;
    canDelete: boolean;
    isSuperAdmin: boolean;
};

export type ManagedUser = {
    id: number;
    name: string;
    email: string;
    roles: string[];
    isCurrentUser: boolean;
    emailVerifiedAt: string | null;
    createdAt: string | null;
};

export type ManagedSettingField = {
    key: string;
    label: string;
    description: string;
    type: string;
    placeholder: string | null;
    rows?: number;
    value: string | null;
};

export type ManagedSettingGroup = {
    key: string;
    title: string;
    description: string;
    fields: ManagedSettingField[];
};

export type ManagedMedia = {
    id: number;
    collection: string;
    originalName: string;
    extension: string | null;
    mimeType: string | null;
    size: number;
    uploadedBy: string | null;
    createdAt: string | null;
    downloadUrl: string;
};

export type RoleOption = {
    name: string;
    label: string;
    description: string | null;
    usersCount: number;
};

export type PermissionDefinition = {
    name: string;
    label: string;
    description: string;
};

export type PermissionGroup = {
    key: string;
    title: string;
    permissions: PermissionDefinition[];
};

export type ResourceFilters = {
    search: string;
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type PaginatedResource<T> = {
    data: T[];
    current_page: number;
    from: number | null;
    last_page: number;
    links: PaginationLink[];
    per_page: number;
    to: number | null;
    total: number;
};

export type FlashMessages = {
    success?: string | null;
    error?: string | null;
};

export type ManagedNotification = {
    id: string;
    title: string;
    message: string;
    actionUrl: string | null;
    actionLabel: string | null;
    level: string;
    readAt: string | null;
    createdAt: string | null;
};

export type NotificationStats = {
    unreadCount: number;
    totalCount: number;
};

export type NotificationFilters = {
    read: string;
};

export type ManagedActivityLog = {
    id: number;
    event: string;
    description: string;
    actor: string | null;
    actorEmail: string | null;
    subjectType: string | null;
    subjectId: number | string | null;
    ipAddress: string | null;
    createdAt: string | null;
    properties: Record<string, unknown>;
};

export type ActivityLogFilters = {
    search: string;
    event: string;
};

export type SearchResultItem = {
    id: string;
    title: string;
    description: string;
    href: string;
    meta: string | null;
};

export type SearchResultGroup = {
    key: string;
    title: string;
    count: number;
    items: SearchResultItem[];
};

export type SearchFilters = {
    q: string;
};

export type ExportResource = {
    key: string;
    title: string;
    description: string;
    href: string;
    actionLabel: string;
    format: string;
};

export type PrintSummary = {
    counts: {
        users: number;
        roles: number;
        unreadNotifications: number;
        activityLogs: number;
    };
    recentUsers: Array<{
        id: number;
        name: string;
        email: string;
        roles: string[];
        createdAt: string | null;
    }>;
    recentEvents: Array<{
        id: number;
        event: string;
        description: string;
        createdAt: string | null;
    }>;
};
