<script setup lang="ts">
import { Badge } from '@/components/ui/badge';

type Props = {
    label: string;
    tone: string;
};

defineProps<Props>();

const toneClasses: Record<string, string> = {
    draft: 'border-stone-300 bg-stone-100 text-stone-900 dark:border-stone-500/30 dark:bg-stone-500/10 dark:text-stone-100',
    review: 'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100',
    published:
        'border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-100',
    archived:
        'border-slate-300 bg-slate-100 text-slate-900 dark:border-slate-500/30 dark:bg-slate-500/10 dark:text-slate-100',
};
</script>

<template>
    <Badge variant="outline" :class="toneClasses[tone] ?? toneClasses.draft">
        {{ label }}
    </Badge>
</template>
