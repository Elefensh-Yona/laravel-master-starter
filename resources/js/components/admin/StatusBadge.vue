<script setup lang="ts">
import { Badge } from '@/components/ui/badge';

export type BadgeTone = 'neutral' | 'info' | 'success' | 'warning' | 'danger';

type Props = {
    label: string;
    tone?: BadgeTone;
};

withDefaults(defineProps<Props>(), {
    tone: 'neutral',
});

const toneClasses: Record<BadgeTone, string> = {
    neutral:
        'border-slate-300 bg-slate-100 text-slate-900 dark:border-slate-500/30 dark:bg-slate-500/10 dark:text-slate-100',
    info: 'border-sky-200 bg-sky-50 text-sky-900 dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-100',
    success:
        'border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-100',
    warning:
        'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100',
    danger: 'border-red-200 bg-red-50 text-red-900 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-100',
};
</script>

<template>
    <Badge variant="outline" :class="toneClasses[tone] ?? toneClasses.neutral">
        {{ label }}
    </Badge>
</template>
