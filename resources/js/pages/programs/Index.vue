<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, FolderKanban, Plus, Rocket, SquarePen } from 'lucide-vue-next';
import ActionIconLink from '@/components/admin/ActionIconLink.vue';
import ConfirmActionDialog from '@/components/admin/ConfirmActionDialog.vue';
import ResourceTable from '@/components/admin/ResourceTable.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import ProgramStatusBadge from '@/components/programs/ProgramStatusBadge.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    create as createProgram,
    edit as editProgram,
    index as programsIndex,
    publish as publishProgram,
    show as showProgram,
} from '@/routes/programs';
import type { BreadcrumbItem, ManagedProgram } from '@/types';

type Props = {
    programs: ManagedProgram[];
    canCreate: boolean;
};

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Programs',
        href: programsIndex(),
    },
];

const formatDate = (value: string, timezone: string): string =>
    new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
        timeZone: timezone,
    }).format(new Date(value));

const publish = (program: ManagedProgram): void => {
    router.post(publishProgram(program.id).url);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Programs" />

        <PageContainer>
            <PageHeader
                title="Programs"
                description="Manage EAIC program configuration, operating windows, and controlled publication from one focused workspace."
            >
                <template #eyebrow>
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-medium tracking-[0.2em] text-sky-900 uppercase dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-100"
                    >
                        <Rocket class="size-3.5" />
                        EAIC administration
                    </div>
                </template>
                <template #actions>
                    <Button v-if="canCreate" as-child>
                        <Link :href="createProgram()">
                            <Plus class="size-4" />
                            Create program
                        </Link>
                    </Button>
                </template>
            </PageHeader>

            <ResourceTable
                :has-results="programs.length > 0"
                empty-title="No programs available"
                empty-description="Published programs and programs in your authorized scope will appear here."
                :empty-icon="FolderKanban"
            >
                <template #head>
                    <tr class="text-left text-xs tracking-wide text-muted-foreground uppercase">
                        <th class="px-4 py-3 font-medium">Program</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="hidden px-4 py-3 font-medium lg:table-cell">Window</th>
                        <th class="hidden px-4 py-3 font-medium xl:table-cell">Timezone</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </template>

                <template #body>
                    <tr v-for="program in programs" :key="program.id" class="align-top">
                        <td class="px-4 py-4">
                            <div class="space-y-1">
                                <Link
                                    :href="showProgram(program.id)"
                                    class="font-medium text-foreground transition-colors hover:text-primary"
                                >
                                    {{ program.name }}
                                </Link>
                                <p class="text-sm text-muted-foreground">
                                    {{ program.code }} · {{ program.slug }}
                                </p>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <ProgramStatusBadge :status="program.status" />
                        </td>
                        <td class="hidden px-4 py-4 text-sm text-muted-foreground lg:table-cell">
                            <div>{{ formatDate(program.opensAt, program.timezone) }}</div>
                            <div class="mt-1">to {{ formatDate(program.closesAt, program.timezone) }}</div>
                        </td>
                        <td class="hidden px-4 py-4 text-sm text-muted-foreground xl:table-cell">
                            {{ program.timezone }}
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center justify-end gap-1">
                                <ActionIconLink
                                    :href="showProgram(program.id)"
                                    label="View program"
                                    :icon="Eye"
                                />
                                <ActionIconLink
                                    v-if="program.canEdit"
                                    :href="editProgram(program.id)"
                                    label="Edit program"
                                    :icon="SquarePen"
                                />
                                <ConfirmActionDialog
                                    v-if="program.canPublish && program.status === 'draft'"
                                    title="Publish program"
                                    :description="`Publish ${program.name}? Its visibility will change for permitted viewers.`"
                                    confirm-label="Publish program"
                                    @confirm="publish(program)"
                                >
                                    <template #trigger>
                                        <Button variant="outline" size="sm" class="ml-1">
                                            Publish
                                        </Button>
                                    </template>
                                </ConfirmActionDialog>
                            </div>
                        </td>
                    </tr>
                </template>
            </ResourceTable>
        </PageContainer>
    </AppLayout>
</template>
