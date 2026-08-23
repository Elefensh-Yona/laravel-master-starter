<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Printer } from 'lucide-vue-next';
import { onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import type { PrintSummary } from '@/types';

type Props = {
    summary: PrintSummary;
};

defineProps<Props>();

const triggerPrint = (): void => {
    window.print();
};

onMounted((): void => {
    window.setTimeout(() => {
        triggerPrint();
    }, 150);
});
</script>

<template>
    <div
        class="min-h-screen bg-stone-50 px-6 py-10 text-stone-900 print:bg-white print:px-0 print:py-0"
    >
        <Head title="Workspace print summary" />

        <div
            class="mx-auto max-w-5xl rounded-[2rem] bg-white p-8 shadow-xl print:rounded-none print:p-0 print:shadow-none"
        >
            <div
                class="flex items-start justify-between gap-4 border-b border-stone-200 pb-6 print:hidden"
            >
                <div>
                    <div
                        class="text-xs font-semibold tracking-[0.24em] text-stone-500 uppercase"
                    >
                        Print foundation
                    </div>
                    <h1 class="mt-2 text-3xl font-semibold">
                        Workspace summary
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-stone-600">
                        A print-friendly baseline for summary reports. Future
                        modules can reuse this structure and replace the data
                        sections.
                    </p>
                </div>
                <Button @click="triggerPrint">
                    <Printer class="size-4" />
                    Print now
                </Button>
            </div>

            <section class="grid gap-4 py-8 md:grid-cols-4">
                <div class="rounded-3xl border border-stone-200 p-5">
                    <div class="text-sm text-stone-500">Users</div>
                    <div class="mt-3 text-3xl font-semibold">
                        {{ summary.counts.users }}
                    </div>
                </div>
                <div class="rounded-3xl border border-stone-200 p-5">
                    <div class="text-sm text-stone-500">Roles</div>
                    <div class="mt-3 text-3xl font-semibold">
                        {{ summary.counts.roles }}
                    </div>
                </div>
                <div class="rounded-3xl border border-stone-200 p-5">
                    <div class="text-sm text-stone-500">
                        Unread notifications
                    </div>
                    <div class="mt-3 text-3xl font-semibold">
                        {{ summary.counts.unreadNotifications }}
                    </div>
                </div>
                <div class="rounded-3xl border border-stone-200 p-5">
                    <div class="text-sm text-stone-500">Activity logs</div>
                    <div class="mt-3 text-3xl font-semibold">
                        {{ summary.counts.activityLogs }}
                    </div>
                </div>
            </section>

            <section
                class="grid gap-8 border-t border-stone-200 pt-8 md:grid-cols-2"
            >
                <div>
                    <h2 class="text-lg font-semibold">Recent users</h2>
                    <div class="mt-4 space-y-4">
                        <div
                            v-for="user in summary.recentUsers"
                            :key="user.id"
                            class="rounded-2xl border border-stone-200 p-4"
                        >
                            <div class="font-medium">{{ user.name }}</div>
                            <div class="mt-1 text-sm text-stone-600">
                                {{ user.email }}
                            </div>
                            <div class="mt-2 text-xs text-stone-500">
                                {{
                                    user.roles.join(', ') || 'No roles assigned'
                                }}
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-lg font-semibold">Recent activity</h2>
                    <div class="mt-4 space-y-4">
                        <div
                            v-for="event in summary.recentEvents"
                            :key="event.id"
                            class="rounded-2xl border border-stone-200 p-4"
                        >
                            <div class="font-medium">{{ event.event }}</div>
                            <div class="mt-1 text-sm text-stone-600">
                                {{ event.description }}
                            </div>
                            <div class="mt-2 text-xs text-stone-500">
                                {{
                                    event.createdAt
                                        ? new Date(
                                              event.createdAt,
                                          ).toLocaleString()
                                        : 'N/A'
                                }}
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
