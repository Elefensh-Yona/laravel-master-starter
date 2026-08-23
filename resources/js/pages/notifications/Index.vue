<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Bell, BellRing, CheckCheck } from 'lucide-vue-next';
import ResourcePagination from '@/components/admin/ResourcePagination.vue';
import ResourceTable from '@/components/admin/ResourceTable.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    index as notificationsIndex,
    readAll as notificationsReadAll,
    read as notificationsRead,
} from '@/routes/notifications';
import type {
    BreadcrumbItem,
    ManagedNotification,
    NotificationFilters,
    NotificationStats,
    PaginatedResource,
} from '@/types';

type Props = {
    notifications: PaginatedResource<ManagedNotification>;
    filters: NotificationFilters;
    stats: NotificationStats;
};

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Notifications',
        href: notificationsIndex(),
    },
];

const setFilter = (read: string): void => {
    router.get(
        notificationsIndex.url({
            query: {
                read: read || undefined,
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

const markRead = (notification: ManagedNotification): void => {
    router.post(
        notificationsRead(notification.id).url,
        {},
        {
            preserveScroll: true,
        },
    );
};

const markAllRead = (): void => {
    router.post(
        notificationsReadAll().url,
        {},
        {
            preserveScroll: true,
        },
    );
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Notifications" />

        <PageContainer>
            <PageHeader
                title="Notifications"
                description="Review unread updates, acknowledge them, and keep a simple in-app signal for each signed-in user."
            >
                <template #eyebrow>
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-medium tracking-[0.2em] text-sky-900 uppercase dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-100"
                    >
                        <BellRing class="size-3.5" />
                        Notification center
                    </div>
                </template>
                <template #actions>
                    <Button
                        :disabled="stats.unreadCount === 0"
                        @click="markAllRead"
                    >
                        <CheckCheck class="size-4" />
                        Mark all read
                    </Button>
                </template>
            </PageHeader>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div
                    class="rounded-[1.25rem] border border-border/70 bg-card/85 p-4 shadow-sm backdrop-blur"
                >
                    <div class="text-sm text-muted-foreground">Unread</div>
                    <div class="mt-2 text-2xl font-semibold">
                        {{ stats.unreadCount }}
                    </div>
                </div>
                <div
                    class="rounded-[1.25rem] border border-border/70 bg-card/85 p-4 shadow-sm backdrop-blur"
                >
                    <div class="text-sm text-muted-foreground">Total</div>
                    <div class="mt-2 text-2xl font-semibold">
                        {{ stats.totalCount }}
                    </div>
                </div>
                <div
                    class="flex items-center gap-2 rounded-[1.25rem] border border-border/70 bg-card/85 p-4 shadow-sm backdrop-blur"
                >
                    <Button
                        :variant="filters.read === '' ? 'default' : 'outline'"
                        @click="setFilter('')"
                    >
                        All
                    </Button>
                    <Button
                        :variant="
                            filters.read === 'unread' ? 'default' : 'outline'
                        "
                        @click="setFilter('unread')"
                    >
                        Unread
                    </Button>
                    <Button
                        :variant="
                            filters.read === 'read' ? 'default' : 'outline'
                        "
                        @click="setFilter('read')"
                    >
                        Read
                    </Button>
                </div>
            </section>

            <ResourceTable
                :has-results="notifications.data.length > 0"
                empty-title="No notifications found"
                empty-description="You have no notifications for the selected filter."
                :empty-icon="Bell"
            >
                <template #head>
                    <tr
                        class="text-left text-xs tracking-wide text-muted-foreground uppercase"
                    >
                        <th class="px-4 py-3 font-medium">Title</th>
                        <th class="px-4 py-3 font-medium">Message</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Created</th>
                        <th class="px-4 py-3 text-right font-medium">
                            Actions
                        </th>
                    </tr>
                </template>

                <template #body>
                    <tr
                        v-for="notification in notifications.data"
                        :key="notification.id"
                        class="align-top"
                    >
                        <td class="px-4 py-4">
                            <div class="font-medium text-foreground">
                                {{ notification.title }}
                            </div>
                        </td>
                        <td
                            class="px-4 py-4 text-sm leading-6 text-muted-foreground"
                        >
                            {{ notification.message }}
                        </td>
                        <td class="px-4 py-4">
                            <Badge
                                :variant="
                                    notification.readAt
                                        ? 'outline'
                                        : 'secondary'
                                "
                            >
                                {{ notification.readAt ? 'Read' : 'Unread' }}
                            </Badge>
                        </td>
                        <td class="px-4 py-4 text-sm text-muted-foreground">
                            {{
                                notification.createdAt
                                    ? new Date(
                                          notification.createdAt,
                                      ).toLocaleString()
                                    : 'N/A'
                            }}
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <Button
                                    v-if="!notification.readAt"
                                    variant="outline"
                                    size="sm"
                                    @click="markRead(notification)"
                                >
                                    Mark read
                                </Button>
                                <Button
                                    v-if="notification.actionUrl"
                                    as-child
                                    variant="ghost"
                                    size="sm"
                                >
                                    <Link :href="notification.actionUrl">
                                        {{ notification.actionLabel || 'Open' }}
                                    </Link>
                                </Button>
                            </div>
                        </td>
                    </tr>
                </template>
            </ResourceTable>

            <ResourcePagination :resource="notifications" />
        </PageContainer>
    </AppLayout>
</template>
