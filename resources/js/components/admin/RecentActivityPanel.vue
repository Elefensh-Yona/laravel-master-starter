<script setup lang="ts">
type ActivityItem = {
    id: number;
    event: string;
    description: string;
    createdAt: string | null;
};

defineProps<{
    items: ActivityItem[];
}>();
</script>

<template>
    <section
        class="rounded-[1.5rem] border border-border/70 bg-card/90 p-6 shadow-sm backdrop-blur"
    >
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold">Recent activity</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Reusable feed pattern for operators, reporting surfaces, and
                    dashboards.
                </p>
            </div>
        </div>

        <div class="mt-5 space-y-3">
            <article
                v-for="item in items"
                :key="item.id"
                class="rounded-2xl border border-border/70 bg-background/80 px-4 py-4"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-medium text-foreground">
                            {{ item.event }}
                        </div>
                        <p class="mt-1 text-sm leading-6 text-muted-foreground">
                            {{ item.description }}
                        </p>
                    </div>
                    <div class="shrink-0 text-xs text-muted-foreground">
                        {{
                            item.createdAt
                                ? new Date(item.createdAt).toLocaleString()
                                : 'N/A'
                        }}
                    </div>
                </div>
            </article>
        </div>
    </section>
</template>
