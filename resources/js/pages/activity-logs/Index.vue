<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowUpRight, Search, ScrollText } from 'lucide-vue-next';
import { ref } from 'vue';
import ResourcePagination from '@/components/admin/ResourcePagination.vue';
import ResourceTable from '@/components/admin/ResourceTable.vue';
import ResourceToolbar from '@/components/admin/ResourceToolbar.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    index as activityLogsIndex,
    show as activityLogsShow,
} from '@/routes/activity-logs';
import type {
    ActivityLogFilters,
    BreadcrumbItem,
    ManagedActivityLog,
    PaginatedResource,
} from '@/types';

type Props = {
    logs: PaginatedResource<ManagedActivityLog>;
    filters: ActivityLogFilters;
    eventOptions: string[];
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Activity logs',
        href: activityLogsIndex(),
    },
];

const search = ref(props.filters.search);
const event = ref(props.filters.event);

const submitFilters = (): void => {
    router.get(
        activityLogsIndex.url({
            query: {
                search: search.value || undefined,
                event: event.value || undefined,
            },
        }),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
};

const resetFilters = (): void => {
    search.value = '';
    event.value = '';
    submitFilters();
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Activity logs" />

        <PageContainer>
            <PageHeader
                title="Activity logs"
                description="Track key admin actions and notification acknowledgement events from one audit-friendly screen."
            >
                <template #eyebrow>
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-medium tracking-[0.2em] text-amber-900 uppercase dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100"
                    >
                        <ScrollText class="size-3.5" />
                        Audit trail
                    </div>
                </template>
            </PageHeader>

            <ResourceToolbar
                v-model:search="search"
                search-placeholder="Search activity descriptions or event keys"
                @submit="submitFilters"
                @reset="resetFilters"
            >
                <template #actions>
                    <div
                        class="flex items-center gap-2 rounded-xl border border-border/70 bg-background/80 px-3 py-2"
                    >
                        <Label
                            for="event-filter"
                            class="text-xs text-muted-foreground"
                        >
                            Event
                        </Label>
                        <select
                            id="event-filter"
                            v-model="event"
                            class="min-w-40 bg-transparent text-sm outline-none"
                            @change="submitFilters"
                        >
                            <option value="">All events</option>
                            <option
                                v-for="option in eventOptions"
                                :key="option"
                                :value="option"
                            >
                                {{ option }}
                            </option>
                        </select>
                    </div>
                </template>
            </ResourceToolbar>

            <ResourceTable
                :has-results="logs.data.length > 0"
                empty-title="No activity logs found"
                empty-description="The selected filters did not match any recorded events yet."
                :empty-icon="Search"
            >
                <template #head>
                    <tr
                        class="text-left text-xs tracking-wide text-muted-foreground uppercase"
                    >
                        <th class="px-4 py-3 font-medium">Event</th>
                        <th class="px-4 py-3 font-medium">Description</th>
                        <th class="px-4 py-3 font-medium">Actor</th>
                        <th class="px-4 py-3 font-medium">IP</th>
                        <th class="px-4 py-3 font-medium">Created</th>
                        <th class="px-4 py-3 text-right font-medium">
                            Actions
                        </th>
                    </tr>
                </template>

                <template #body>
                    <tr
                        v-for="log in logs.data"
                        :key="log.id"
                        class="align-top"
                    >
                        <td class="px-4 py-4">
                            <Badge variant="outline">{{ log.event }}</Badge>
                        </td>
                        <td
                            class="px-4 py-4 text-sm leading-6 text-muted-foreground"
                        >
                            <div>{{ log.description }}</div>
                            <div
                                v-if="Object.keys(log.properties).length > 0"
                                class="mt-2 rounded-xl bg-muted/50 p-2 font-mono text-xs"
                            >
                                {{ JSON.stringify(log.properties) }}
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm text-muted-foreground">
                            {{ log.actor || 'System' }}
                        </td>
                        <td class="px-4 py-4 text-sm text-muted-foreground">
                            {{ log.ipAddress || 'N/A' }}
                        </td>
                        <td class="px-4 py-4 text-sm text-muted-foreground">
                            {{
                                log.createdAt
                                    ? new Date(log.createdAt).toLocaleString()
                                    : 'N/A'
                            }}
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex justify-end">
                                <Button as-child variant="ghost" size="sm">
                                    <Link :href="activityLogsShow(log.id)">
                                        <ArrowUpRight class="size-4" />
                                        View
                                    </Link>
                                </Button>
                            </div>
                        </td>
                    </tr>
                </template>
            </ResourceTable>

            <ResourcePagination :resource="logs" />
        </PageContainer>
    </AppLayout>
</template>
