<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import type { PaginatedResource } from '@/types';

type Props<T> = {
    resource: PaginatedResource<T>;
};

defineProps<Props<unknown>>();
</script>

<template>
    <div
        v-if="resource.last_page > 1"
        class="flex flex-col gap-3 rounded-[1.25rem] border border-border/70 bg-card/85 px-4 py-3 text-sm shadow-sm backdrop-blur md:flex-row md:items-center md:justify-between"
    >
        <p class="text-muted-foreground">
            Showing {{ resource.from ?? 0 }} to {{ resource.to ?? 0 }} of
            {{ resource.total }} results
        </p>

        <div class="flex flex-wrap items-center gap-2">
            <Button
                v-for="link in resource.links"
                :key="link.label"
                as-child
                :variant="link.active ? 'default' : 'outline'"
                size="sm"
                :class="!link.url ? 'pointer-events-none opacity-50' : ''"
            >
                <Link
                    v-if="link.url"
                    :href="link.url"
                    preserve-scroll
                    preserve-state
                >
                    <ChevronLeft
                        v-if="link.label.includes('Previous')"
                        class="size-4"
                    />
                    <span
                        v-else-if="link.label.includes('Next')"
                        class="inline-flex items-center gap-1"
                    >
                        Next
                        <ChevronRight class="size-4" />
                    </span>
                    <span v-else v-html="link.label" />
                </Link>
                <span v-else v-html="link.label" />
            </Button>
        </div>
    </div>
</template>
