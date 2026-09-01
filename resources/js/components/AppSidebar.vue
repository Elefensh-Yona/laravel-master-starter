<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronsDownUp, ChevronsUpDown } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { appNavigation } from '@/navigation/app';
import { dashboard } from '@/routes';
import type { Auth, NavGroup } from '@/types';

const page = usePage();
const auth = computed(() => page.props.auth as Auth);
const { state } = useSidebar();
const roleSummary = computed(() => auth.value.roles[0] ?? 'No role assigned');

const mainNavigation = computed<NavGroup[]>(() =>
    appNavigation
        .map((group) => ({
            ...group,
            items: group.items.filter((item) => {
                if (item.ability) {
                    return auth.value.can[item.ability];
                }

                if (!item.permission) {
                    return true;
                }

                return auth.value.permissions.includes(item.permission);
            }),
        }))
        .filter((group) => group.items.length > 0),
);

const openGroups = ref<Record<string, boolean>>({});

watch(
    mainNavigation,
    (groups) => {
        openGroups.value = groups.reduce<Record<string, boolean>>(
            (nextState, group) => {
                nextState[group.title] = openGroups.value[group.title] ?? true;

                return nextState;
            },
            {},
        );
    },
    { immediate: true },
);

const allGroupsExpanded = computed(() =>
    mainNavigation.value.every(
        (group) => openGroups.value[group.title] ?? true,
    ),
);

const isSidebarIconCollapsed = computed(() => state.value === 'collapsed');

const toggleGroup = (title: string): void => {
    openGroups.value = {
        ...openGroups.value,
        [title]: !(openGroups.value[title] ?? true),
    };
};

const toggleAllGroups = (): void => {
    const shouldExpand = !allGroupsExpanded.value;

    openGroups.value = Object.fromEntries(
        mainNavigation.value.map((group) => [group.title, shouldExpand]),
    );
};
</script>

<template>
    <Sidebar
        collapsible="icon"
        variant="inset"
        class="border-r border-sidebar-border/70 bg-sidebar/90 backdrop-blur"
    >
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child class="min-w-0">
                        <Link :href="dashboard()" class="min-w-0">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
            <div class="mt-3 px-2 group-data-[collapsible=icon]:hidden">
                <div
                    class="rounded-xl border border-sidebar-border/70 bg-background/70 px-3 py-2"
                >
                    <div
                        class="text-[11px] tracking-[0.18em] text-muted-foreground uppercase"
                    >
                        Access
                    </div>
                    <div
                        class="mt-1 truncate text-sm font-medium text-foreground"
                    >
                        {{ roleSummary }}
                    </div>
                </div>
            </div>
        </SidebarHeader>

        <SidebarContent>
            <NavMain
                :items="mainNavigation"
                :open-groups="openGroups"
                @toggle-group="toggleGroup"
            />
        </SidebarContent>

        <SidebarFooter>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        :tooltip="
                            allGroupsExpanded
                                ? 'Collapse all groups'
                                : 'Expand all groups'
                        "
                        :aria-label="
                            allGroupsExpanded
                                ? 'Collapse all groups'
                                : 'Expand all groups'
                        "
                        class="justify-center"
                        @click="toggleAllGroups"
                    >
                        <component
                            :is="
                                allGroupsExpanded
                                    ? ChevronsDownUp
                                    : ChevronsUpDown
                            "
                        />
                        <span v-if="!isSidebarIconCollapsed" class="sr-only">
                            {{
                                allGroupsExpanded
                                    ? 'Collapse all groups'
                                    : 'Expand all groups'
                            }}
                        </span>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
