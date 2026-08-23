<script setup lang="ts">
import type { FunctionalComponent, HTMLAttributes, VNodeProps } from 'vue';
import EmptyState from '@/components/EmptyState.vue';

type Props = {
    hasResults: boolean;
    emptyTitle: string;
    emptyDescription: string;
    emptyIcon?: FunctionalComponent<HTMLAttributes & VNodeProps>;
};

defineProps<Props>();
</script>

<template>
    <div
        class="rounded-[1.5rem] border border-border/70 bg-card/85 shadow-sm backdrop-blur"
    >
        <div v-if="hasResults" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border/70 text-sm">
                <thead class="bg-muted/40">
                    <slot name="head" />
                </thead>
                <tbody class="divide-y divide-border/60">
                    <slot name="body" />
                </tbody>
            </table>
        </div>

        <div v-else class="p-5">
            <EmptyState
                :title="emptyTitle"
                :description="emptyDescription"
                :icon="emptyIcon"
            />
        </div>
    </div>
</template>
