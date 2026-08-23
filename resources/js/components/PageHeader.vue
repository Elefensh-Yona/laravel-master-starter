<script setup lang="ts">
type Props = {
    title: string;
    description?: string;
};

defineProps<Props>();
</script>

<template>
    <header
        class="flex flex-col gap-4 rounded-[1.5rem] border border-border/70 bg-card/85 px-5 py-5 shadow-sm backdrop-blur md:flex-row md:items-start md:justify-between md:px-6"
    >
        <div class="min-w-0 space-y-2">
            <slot name="eyebrow" />
            <div>
                <h1
                    class="text-2xl font-semibold tracking-tight text-foreground"
                >
                    {{ title }}
                </h1>
                <p
                    v-if="description"
                    class="mt-2 max-w-3xl text-sm leading-6 text-muted-foreground"
                >
                    {{ description }}
                </p>
            </div>
        </div>

        <div
            v-if="$slots.actions"
            class="flex shrink-0 items-center gap-2 md:justify-end"
        >
            <slot name="actions" />
        </div>
    </header>
</template>
