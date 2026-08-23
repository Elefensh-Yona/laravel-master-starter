<script setup lang="ts">
type Props = {
    fields?: number;
    columns?: 1 | 2;
    withHeader?: boolean;
    tallField?: boolean;
};

withDefaults(defineProps<Props>(), {
    fields: 4,
    columns: 2,
    withHeader: true,
    tallField: false,
});
</script>

<template>
    <section
        class="rounded-[1.5rem] border border-border/70 bg-card/85 shadow-sm backdrop-blur"
    >
        <div v-if="withHeader" class="border-b border-border/60 px-5 py-5">
            <div class="h-5 w-40 animate-pulse rounded-full bg-muted" />
            <div class="mt-3 h-4 w-3/4 animate-pulse rounded-full bg-muted" />
        </div>

        <div
            class="grid gap-5 px-5 py-5"
            :class="columns === 2 ? 'md:grid-cols-2' : ''"
        >
            <div
                v-for="index in fields"
                :key="index"
                class="grid gap-2"
                :class="tallField && index === fields ? 'md:col-span-2' : ''"
            >
                <div class="h-4 w-24 animate-pulse rounded-full bg-muted" />
                <div
                    class="animate-pulse rounded-xl bg-muted"
                    :class="tallField && index === fields ? 'h-32' : 'h-11'"
                />
            </div>
        </div>
    </section>
</template>
