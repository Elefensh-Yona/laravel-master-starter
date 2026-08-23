<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

const page = usePage();
const appName = computed(() => page.props.name ?? 'Laravel Master Starter');
</script>

<template>
    <div
        class="flex aspect-square size-9 items-center justify-center rounded-xl bg-[linear-gradient(135deg,var(--color-sidebar-primary),#b45309)] text-sidebar-primary-foreground shadow-sm"
    >
        <AppLogoIcon class="size-5 fill-current text-white dark:text-black" />
    </div>
    <div class="ml-2 grid flex-1 text-left text-sm">
        <span
            class="truncate text-[0.7rem] font-medium tracking-[0.18em] text-muted-foreground uppercase"
        >
            Master Starter
        </span>
        <span class="truncate leading-tight font-semibold">
            {{ appName }}
        </span>
    </div>
</template>
