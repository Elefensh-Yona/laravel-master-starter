<script setup lang="ts">
import { Inbox } from 'lucide-vue-next';
import type { LucideIcon } from 'lucide-vue-next';

type Props = {
    title: string;
    description: string;
    icon?: LucideIcon;
};

withDefaults(defineProps<Props>(), {
    icon: Inbox,
});
</script>

<template>
    <section
        class="rounded-[1.5rem] border border-dashed border-border bg-card/70 px-6 py-10 text-center shadow-sm"
    >
        <div
            class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-muted text-muted-foreground"
        >
            <component :is="icon" class="size-5" />
        </div>
        <h2 class="mt-4 text-lg font-semibold">{{ title }}</h2>
        <p
            class="mx-auto mt-2 max-w-xl text-sm leading-6 text-muted-foreground"
        >
            {{ description }}
        </p>
        <div v-if="$slots.actions" class="mt-5 flex justify-center gap-2">
            <slot name="actions" />
        </div>
    </section>
</template>
