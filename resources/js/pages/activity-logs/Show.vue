<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ScrollText } from 'lucide-vue-next';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    index as activityLogsIndex,
    show as activityLogsShow,
} from '@/routes/activity-logs';
import type { BreadcrumbItem, ManagedActivityLog } from '@/types';

type Props = {
    log: ManagedActivityLog;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Activity logs',
        href: activityLogsIndex(),
    },
    {
        title: `Event #${props.log.id}`,
        href: activityLogsShow(props.log.id),
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Activity log #${log.id}`" />

        <PageContainer>
            <PageHeader
                :title="log.event"
                description="Review the exact event payload, actor context, and subject reference for this audit entry."
            >
                <template #eyebrow>
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-medium tracking-[0.2em] text-amber-900 uppercase dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100"
                    >
                        <ScrollText class="size-3.5" />
                        Audit detail
                    </div>
                </template>
                <template #actions>
                    <Button as-child variant="outline">
                        <Link :href="activityLogsIndex()">
                            <ArrowLeft class="size-4" />
                            Back to logs
                        </Link>
                    </Button>
                </template>
            </PageHeader>

            <section class="grid gap-4 lg:grid-cols-[1.4fr_0.9fr]">
                <div
                    class="rounded-[1.5rem] border border-border/70 bg-card/85 p-6 shadow-sm backdrop-blur"
                >
                    <div class="flex items-center gap-3">
                        <Badge variant="outline">{{ log.event }}</Badge>
                        <span class="text-sm text-muted-foreground">
                            {{
                                log.createdAt
                                    ? new Date(log.createdAt).toLocaleString()
                                    : 'N/A'
                            }}
                        </span>
                    </div>

                    <div class="mt-6 space-y-4">
                        <div>
                            <div class="text-sm font-medium">Description</div>
                            <div
                                class="mt-1 text-sm leading-6 text-muted-foreground"
                            >
                                {{ log.description }}
                            </div>
                        </div>

                        <div>
                            <div class="text-sm font-medium">Properties</div>
                            <pre
                                class="mt-2 overflow-x-auto rounded-2xl bg-muted/60 p-4 text-xs leading-6 text-muted-foreground"
                                >{{
                                    JSON.stringify(log.properties, null, 2)
                                }}</pre
                            >
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-[1.5rem] border border-border/70 bg-card/85 p-6 shadow-sm backdrop-blur"
                >
                    <div class="text-sm font-semibold">Context</div>
                    <dl class="mt-4 space-y-4 text-sm">
                        <div>
                            <dt class="text-muted-foreground">Actor</dt>
                            <dd class="mt-1 font-medium">
                                {{ log.actor || 'System' }}
                            </dd>
                            <dd
                                v-if="log.actorEmail"
                                class="text-muted-foreground"
                            >
                                {{ log.actorEmail }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Subject</dt>
                            <dd class="mt-1 font-medium">
                                {{ log.subjectType || 'N/A' }}
                            </dd>
                            <dd class="text-muted-foreground">
                                {{ log.subjectId ?? 'N/A' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">IP address</dt>
                            <dd class="mt-1 font-medium">
                                {{ log.ipAddress || 'N/A' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </section>
        </PageContainer>
    </AppLayout>
</template>
