<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    FileOutput,
    FolderOpen,
    ScrollText,
    Settings2,
    Shield,
    Users,
} from 'lucide-vue-next';
import { computed } from 'vue';
import RecentActivityPanel from '@/components/admin/RecentActivityPanel.vue';
import StatCard from '@/components/admin/StatCard.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { index as activityLogsIndex } from '@/routes/activity-logs';
import { edit as adminSettingsEdit } from '@/routes/admin-settings';
import { index as exportsIndex } from '@/routes/exports';
import { index as mediaIndex } from '@/routes/media';
import { index as rolesIndex } from '@/routes/roles';
import { index as usersIndex } from '@/routes/users';
import type { Auth, BreadcrumbItem } from '@/types';

type Metric = {
    key: string;
    label: string;
    value: number;
    description: string;
    tone: 'amber' | 'sky' | 'emerald' | 'violet';
};

type ActivityItem = {
    id: number;
    event: string;
    description: string;
    createdAt: string | null;
};

type Props = {
    metrics: Metric[];
    recentActivity: ActivityItem[];
};

defineProps<Props>();

const page = usePage();
const auth = computed(() => page.props.auth as Auth);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];

const quickLinks = computed(() =>
    [
        {
            title: 'Users',
            description: 'Manage workspace members and their assigned roles.',
            href: usersIndex(),
            icon: Users,
            visible: auth.value.can.manageUsers,
        },
        {
            title: 'Roles',
            description:
                'Review access bundles and the permissions they carry.',
            href: rolesIndex(),
            icon: Shield,
            visible: auth.value.can.manageRoles,
        },
        {
            title: 'Media',
            description: 'Upload, download, and organize shared files.',
            href: mediaIndex(),
            icon: FolderOpen,
            visible: auth.value.permissions.includes('media.view'),
        },
        {
            title: 'Export center',
            description:
                'Download CSV snapshots or open a print-friendly summary.',
            href: exportsIndex(),
            icon: FileOutput,
            visible: auth.value.can.viewExports,
        },
        {
            title: 'Settings',
            description:
                'Adjust application and organization-level configuration.',
            href: adminSettingsEdit(),
            icon: Settings2,
            visible: auth.value.can.manageSettings,
        },
        {
            title: 'Activity logs',
            description: 'Audit the actions recorded across the workspace.',
            href: activityLogsIndex(),
            icon: ScrollText,
            visible: auth.value.can.viewActivityLogs,
        },
    ].filter((item) => item.visible),
);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <PageContainer>
            <PageHeader
                title="Workspace overview"
                description="A shared summary of the people, files, and audited activity in your workspace."
            >
                <template #actions>
                    <div
                        v-if="quickLinks.length > 0"
                        class="text-sm text-muted-foreground"
                    >
                        {{ quickLinks.length }} area{{
                            quickLinks.length === 1 ? '' : 's'
                        }}
                        available from your current access set.
                    </div>
                </template>
            </PageHeader>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <StatCard
                    v-for="metric in metrics"
                    :key="metric.key"
                    :label="metric.label"
                    :value="metric.value"
                    :description="metric.description"
                    :tone="metric.tone"
                />
            </section>

            <section class="grid gap-4 xl:grid-cols-[1.3fr_1fr]">
                <RecentActivityPanel :items="recentActivity" />

                <section
                    class="rounded-[1.5rem] border border-border/70 bg-card/90 p-6 shadow-sm backdrop-blur"
                >
                    <h2 class="text-lg font-semibold">Quick access</h2>
                    <div class="mt-5 grid gap-3">
                        <Link
                            v-for="item in quickLinks"
                            :key="item.title"
                            :href="item.href"
                            class="rounded-2xl border border-border/70 bg-background/80 px-4 py-4 transition hover:border-foreground/20 hover:bg-background"
                        >
                            <div class="flex items-start gap-3">
                                <component
                                    :is="item.icon"
                                    class="mt-0.5 size-4 shrink-0 text-muted-foreground"
                                />
                                <div>
                                    <div
                                        class="text-sm font-medium text-foreground"
                                    >
                                        {{ item.title }}
                                    </div>
                                    <p
                                        class="mt-1 text-sm leading-6 text-muted-foreground"
                                    >
                                        {{ item.description }}
                                    </p>
                                </div>
                            </div>
                        </Link>

                        <div
                            v-if="quickLinks.length === 0"
                            class="rounded-2xl border border-dashed border-border/70 bg-background/50 px-4 py-6 text-sm text-muted-foreground"
                        >
                            No quick links are exposed for this role yet.
                        </div>
                    </div>
                </section>
            </section>
        </PageContainer>
    </AppLayout>
</template>
