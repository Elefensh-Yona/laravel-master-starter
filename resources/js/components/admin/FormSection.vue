<script setup lang="ts">
type Props = {
    title?: string;
    description?: string;
    compact?: boolean;
};

withDefaults(defineProps<Props>(), {
    title: undefined,
    description: undefined,
    compact: false,
});
</script>

<template>
    <section
        class="rounded-[1.5rem] border border-border/70 bg-card/85 shadow-sm backdrop-blur"
    >
        <div
            v-if="title || description || $slots.headerAction"
            class="flex flex-col gap-3 border-b border-border/60 px-5 py-5 md:flex-row md:items-start md:justify-between"
        >
            <div class="min-w-0">
                <h2 v-if="title" class="text-lg font-semibold text-foreground">
                    {{ title }}
                </h2>
                <p
                    v-if="description"
                    class="mt-1 max-w-3xl text-sm leading-6 text-muted-foreground"
                >
                    {{ description }}
                </p>
            </div>

            <div
                v-if="$slots.headerAction"
                class="flex shrink-0 items-center gap-2"
            >
                <slot name="headerAction" />
            </div>
        </div>

        <div :class="compact ? 'px-5 py-5' : 'px-5 py-5 md:px-5'">
            <slot />
        </div>
    </section>
</template>
