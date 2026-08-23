<script setup lang="ts">
type Props = {
    label: string;
    value: number;
    description: string;
    tone?: 'amber' | 'sky' | 'emerald' | 'violet';
};

withDefaults(defineProps<Props>(), {
    tone: 'amber',
});

const toneClasses: Record<string, string> = {
    amber: 'border-amber-200 bg-amber-50 text-amber-950 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100',
    sky: 'border-sky-200 bg-sky-50 text-sky-950 dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-100',
    emerald:
        'border-emerald-200 bg-emerald-50 text-emerald-950 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-100',
    violet: 'border-violet-200 bg-violet-50 text-violet-950 dark:border-violet-500/30 dark:bg-violet-500/10 dark:text-violet-100',
};
</script>

<template>
    <article
        :class="['rounded-[1.35rem] border p-5 shadow-sm', toneClasses[tone]]"
    >
        <div class="text-xs font-medium tracking-[0.2em] uppercase opacity-80">
            {{ label }}
        </div>
        <div class="mt-3 text-3xl font-semibold">{{ value }}</div>
        <p class="mt-3 text-sm leading-6 opacity-80">{{ description }}</p>
    </article>
</template>
