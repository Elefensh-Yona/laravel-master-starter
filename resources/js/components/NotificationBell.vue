<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { Bell, CheckCheck } from 'lucide-vue-next';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    index as notificationsIndex,
    read as notificationsRead,
    readAll as notificationsReadAll,
} from '@/routes/notifications';
import type { Auth, ManagedNotification } from '@/types';

const page = usePage();
const auth = computed(() => page.props.auth as Auth);
const preview = computed(() => auth.value.notificationPreview ?? []);

const markRead = (notification: ManagedNotification): void => {
    router.post(
        notificationsRead(notification.id).url,
        {},
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
};

const markAllRead = (): void => {
    router.post(
        notificationsReadAll().url,
        {},
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
};
</script>

<template>
    <DropdownMenu v-if="auth.can.viewNotifications">
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="sm" class="rounded-full">
                <Bell class="size-4" />
                <span>Notifications</span>
                <Badge
                    v-if="auth.notificationCount > 0"
                    variant="secondary"
                    class="ml-1"
                >
                    {{ auth.notificationCount }}
                </Badge>
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent
            align="end"
            :side-offset="8"
            class="w-96 rounded-2xl p-0"
        >
            <DropdownMenuLabel
                class="flex items-center justify-between px-4 py-3"
            >
                <div>
                    <div class="text-sm font-semibold">Notifications</div>
                    <div class="text-xs text-muted-foreground">
                        {{ auth.notificationCount }} unread
                    </div>
                </div>
                <Button
                    v-if="auth.notificationCount > 0"
                    variant="ghost"
                    size="sm"
                    class="h-8 px-2"
                    @click="markAllRead"
                >
                    <CheckCheck class="size-4" />
                    Mark all read
                </Button>
            </DropdownMenuLabel>

            <DropdownMenuSeparator />

            <DropdownMenuGroup v-if="preview.length > 0">
                <DropdownMenuItem
                    v-for="notification in preview"
                    :key="notification.id"
                    class="items-start px-4 py-3"
                >
                    <div class="flex w-full items-start gap-3">
                        <div
                            class="mt-1 size-2 rounded-full"
                            :class="
                                notification.readAt
                                    ? 'bg-muted-foreground/40'
                                    : 'bg-sky-500'
                            "
                        />
                        <div class="min-w-0 flex-1 space-y-1">
                            <div
                                class="flex items-center justify-between gap-3"
                            >
                                <div class="truncate text-sm font-medium">
                                    {{ notification.title }}
                                </div>
                                <div
                                    class="shrink-0 text-[11px] text-muted-foreground"
                                >
                                    {{
                                        notification.createdAt
                                            ? new Date(
                                                  notification.createdAt,
                                              ).toLocaleDateString()
                                            : 'Now'
                                    }}
                                </div>
                            </div>
                            <div
                                class="line-clamp-2 text-xs leading-5 text-muted-foreground"
                            >
                                {{ notification.message }}
                            </div>
                            <div class="flex items-center gap-2 pt-1">
                                <Button
                                    v-if="!notification.readAt"
                                    variant="outline"
                                    size="sm"
                                    class="h-7 px-2 text-xs"
                                    @click.stop.prevent="markRead(notification)"
                                >
                                    Mark read
                                </Button>
                                <Button
                                    v-if="notification.actionUrl"
                                    as-child
                                    variant="ghost"
                                    size="sm"
                                    class="h-7 px-2 text-xs"
                                >
                                    <Link :href="notification.actionUrl">
                                        {{ notification.actionLabel || 'Open' }}
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </div>
                </DropdownMenuItem>
            </DropdownMenuGroup>

            <div v-else class="px-4 py-6 text-center">
                <div class="text-sm font-medium">No notifications yet</div>
                <div class="mt-1 text-xs text-muted-foreground">
                    New modules can post reusable system notifications here.
                </div>
            </div>

            <DropdownMenuSeparator />

            <div class="p-2">
                <Button
                    as-child
                    variant="outline"
                    class="w-full justify-center"
                >
                    <Link :href="notificationsIndex()">
                        View all notifications
                    </Link>
                </Button>
            </div>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
