<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { BriefcaseBusiness, Eye, FileText, Plus, SquarePen } from 'lucide-vue-next';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    create as createApplication,
    edit as editApplication,
    index as applicationsIndex,
    show as showApplication,
} from '@/routes/applications';
import type { BreadcrumbItem, ManagedApplication } from '@/types';

type Props = {
    applications: ManagedApplication[];
};

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Applications',
        href: applicationsIndex(),
    },
];

const formatDate = (value: string | null): string =>
    value
        ? new Intl.DateTimeFormat(undefined, {
              dateStyle: 'medium',
              timeStyle: 'short',
          }).format(new Date(value))
        : '—';

const statusTone = (status: string): 'neutral' | 'info' | 'success' | 'warning' => {
    if (status === 'draft') return 'info';
    if (status === 'submitted') return 'success';
    if (status === 'archived') return 'neutral';

    return 'warning';
};

const statusLabel = (status: string): string => status.replaceAll('_', ' ');
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Applications" />

        <PageContainer>
            <PageHeader
                title="Applications"
                description="Review your application drafts, submissions, and revision history in one place."
            >
                <template #eyebrow>
                    <div class="inline-flex items-center gap-2 rounded-full border border-violet-200 bg-violet-50 px-3 py-1 text-xs font-medium tracking-[0.2em] text-violet-900 uppercase dark:border-violet-500/30 dark:bg-violet-500/10 dark:text-violet-100">
                        <BriefcaseBusiness class="size-3.5" />
                        EAIC applications
                    </div>
                </template>
                <template #actions>
                    <Button as-child>
                        <Link :href="createApplication()">
                            <Plus class="size-4" />
                            Create application
                        </Link>
                    </Button>
                </template>
            </PageHeader>

            <div class="rounded-xl border border-border bg-card shadow-sm">
                <div v-if="applications.length === 0" class="flex flex-col items-center justify-center gap-3 px-6 py-14 text-center">
                    <FileText class="size-10 text-muted-foreground" />
                    <div>
                        <h3 class="text-lg font-semibold text-foreground">No applications yet</h3>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Create a new application to begin an EAIC submission draft.
                        </p>
                    </div>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b border-border bg-muted/40 text-xs tracking-wide text-muted-foreground uppercase">
                            <tr>
                                <th class="px-4 py-3 font-medium">Application</th>
                                <th class="px-4 py-3 font-medium">Status</th>
                                <th class="hidden px-4 py-3 font-medium md:table-cell">Type</th>
                                <th class="hidden px-4 py-3 font-medium xl:table-cell">Created</th>
                                <th class="px-4 py-3 text-right font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="application in applications" :key="application.id" class="border-b border-border/60 last:border-b-0">
                                <td class="px-4 py-4">
                                    <div class="space-y-1">
                                        <Link :href="showApplication(application.id)" class="font-medium text-foreground hover:text-primary">
                                            #{{ application.id }} · {{ application.applicantType }}
                                        </Link>
                                        <p class="text-xs text-muted-foreground">
                                            Program {{ application.programId }}
                                            <span v-if="application.reference">· {{ application.reference }}</span>
                                        </p>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <StatusBadge :label="statusLabel(application.status)" :tone="statusTone(application.status)" />
                                </td>
                                <td class="hidden px-4 py-4 text-muted-foreground md:table-cell">
                                    {{ application.applicantType }}
                                </td>
                                <td class="hidden px-4 py-4 text-muted-foreground xl:table-cell">
                                    {{ formatDate(application.createdAt) }}
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link :href="showApplication(application.id)" class="inline-flex items-center gap-1 rounded-md border border-border bg-background px-2 py-1.5 text-xs font-medium text-foreground hover:bg-accent">
                                            <Eye class="size-3.5" />
                                            View
                                        </Link>
                                        <Link
                                            v-if="application.canEdit"
                                            :href="editApplication(application.id)"
                                            class="inline-flex items-center gap-1 rounded-md border border-border bg-background px-2 py-1.5 text-xs font-medium text-foreground hover:bg-accent"
                                        >
                                            <SquarePen class="size-3.5" />
                                            Edit
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </PageContainer>
    </AppLayout>
</template>
