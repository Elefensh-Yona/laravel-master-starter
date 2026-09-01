import {
    Bell,
    BriefcaseBusiness,
    FileOutput,
    FolderOpen,
    LayoutGrid,
    Rocket,
    Settings2,
    Shield,
    ScrollText,
    Users,
} from 'lucide-vue-next';
import { dashboard } from '@/routes';
import { index as activityLogsIndex } from '@/routes/activity-logs';
import { index as applicationsIndex } from '@/routes/applications';
import { edit as adminSettingsEdit } from '@/routes/admin-settings';
import { index as exportsIndex } from '@/routes/exports';
import { index as mediaIndex } from '@/routes/media';
import { index as notificationsIndex } from '@/routes/notifications';
import { index as programsIndex } from '@/routes/programs';
import { index as rolesIndex } from '@/routes/roles';
import { index as usersIndex } from '@/routes/users';
import type { NavGroup } from '@/types';

export const appNavigation: NavGroup[] = [
    {
        title: 'Core',
        items: [
            {
                title: 'Dashboard',
                href: dashboard(),
                icon: LayoutGrid,
                permission: 'dashboard.view',
            },
        ],
    },
    {
        title: 'Insight',
        items: [
            {
                title: 'Notifications',
                href: notificationsIndex(),
                icon: Bell,
                permission: 'notifications.view',
            },
            {
                title: 'Activity logs',
                href: activityLogsIndex(),
                icon: ScrollText,
                permission: 'activity-logs.view',
            },
        ],
    },
    {
        title: 'Management',
        items: [
            {
                title: 'Programs',
                href: programsIndex(),
                icon: Rocket,
                ability: 'managePrograms',
            },
            {
                title: 'Applications',
                href: applicationsIndex(),
                icon: BriefcaseBusiness,
                permission: 'application.view',
            },
            {
                title: 'Export center',
                href: exportsIndex(),
                icon: FileOutput,
                permission: 'exports.view',
            },
            {
                title: 'Settings',
                href: adminSettingsEdit(),
                icon: Settings2,
                permission: 'settings.view',
            },
            {
                title: 'Media',
                href: mediaIndex(),
                icon: FolderOpen,
                permission: 'media.view',
            },
            {
                title: 'Users',
                href: usersIndex(),
                icon: Users,
                permission: 'users.view',
            },
            {
                title: 'Roles',
                href: rolesIndex(),
                icon: Shield,
                permission: 'roles.view',
            },
        ],
    },
];
