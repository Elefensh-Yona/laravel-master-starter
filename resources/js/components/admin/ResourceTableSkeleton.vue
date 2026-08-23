<script setup lang="ts">
type Props = {
    rows?: number;
    columns?: number;
};

withDefaults(defineProps<Props>(), {
    rows: 5,
    columns: 5,
});
</script>

<template>
    <section
        class="rounded-[1.5rem] border border-border/70 bg-card/85 shadow-sm backdrop-blur"
    >
        <div class="border-b border-border/60 bg-muted/30 px-4 py-3">
            <div
                class="grid gap-4"
                :style="{
                    gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))`,
                }"
            >
                <div
                    v-for="column in columns"
                    :key="column"
                    class="h-4 animate-pulse rounded-full bg-muted"
                />
            </div>
        </div>

        <div class="divide-y divide-border/60 px-4">
            <div
                v-for="row in rows"
                :key="row"
                class="grid gap-4 py-4"
                :style="{
                    gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))`,
                }"
            >
                <div
                    v-for="column in columns"
                    :key="`${row}-${column}`"
                    class="h-4 animate-pulse rounded-full bg-muted"
                />
            </div>
        </div>
    </section>
</template>
