<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { MoonStar, Search, SunMedium } from 'lucide-vue-next';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import NotificationBell from '@/components/NotificationBell.vue';
import { Button } from '@/components/ui/button';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { useAppearance } from '@/composables/useAppearance';
import { index as searchIndex } from '@/routes/search';
import type { Auth, BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const { resolvedAppearance, updateAppearance } = useAppearance();
const page = usePage();
const auth = page.props.auth as Auth;

const toggleAppearance = (): void => {
    updateAppearance(resolvedAppearance.value === 'dark' ? 'light' : 'dark');
};
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 bg-background/80 px-6 backdrop-blur transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex min-w-0 items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>
        <div class="ml-auto flex items-center gap-2">
            <Button
                v-if="auth.can.viewSearch"
                variant="ghost"
                size="sm"
                class="rounded-full"
                as-child
            >
                <Link :href="searchIndex()">
                    <Search class="size-4" />
                    Search
                </Link>
            </Button>
            <NotificationBell />
            <Button
                variant="ghost"
                size="icon-sm"
                class="rounded-full"
                @click="toggleAppearance"
            >
                <SunMedium
                    v-if="resolvedAppearance === 'dark'"
                    class="size-4"
                />
                <MoonStar v-else class="size-4" />
                <span class="sr-only">Toggle appearance</span>
            </Button>
        </div>
    </header>
</template>
