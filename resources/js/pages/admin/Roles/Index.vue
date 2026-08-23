<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Shield, SquarePen, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import ActionIconLink from '@/components/admin/ActionIconLink.vue';
import ConfirmActionDialog from '@/components/admin/ConfirmActionDialog.vue';
import ResourcePagination from '@/components/admin/ResourcePagination.vue';
import ResourceTable from '@/components/admin/ResourceTable.vue';
import ResourceToolbar from '@/components/admin/ResourceToolbar.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    create as createRole,
    destroy as destroyRole,
    edit as editRole,
    index as rolesIndex,
} from '@/routes/roles';
import type {
    BreadcrumbItem,
    ManagedRole,
    PaginatedResource,
    ResourceFilters,
} from '@/types';

type Props = {
    roles: PaginatedResource<ManagedRole>;
    filters: ResourceFilters;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Roles',
        href: rolesIndex(),
    },
];

const search = ref(props.filters.search);

const submitSearch = (): void => {
    router.get(
        rolesIndex.url({
            query: {
                search: search.value || undefined,
            },
        }),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
};

const resetSearch = (): void => {
    search.value = '';
    submitSearch();
};

const deleteSelectedRole = (role: ManagedRole): void => {
    router.delete(destroyRole(role.id).url, {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Roles" />

        <PageContainer>
            <PageHeader
                title="Roles"
                description="Manage role records, their descriptions, and the permissions that decide sidebar visibility, route access, and future CRUD actions."
            >
                <template #eyebrow>
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-medium tracking-[0.2em] text-sky-900 uppercase dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-100"
                    >
                        <Shield class="size-3.5" />
                        Access model
                    </div>
                </template>
                <template #actions>
                    <Button as-child>
                        <Link :href="createRole()">
                            <Plus class="size-4" />
                            Create role
                        </Link>
                    </Button>
                </template>
            </PageHeader>

            <ResourceToolbar
                v-model:search="search"
                search-placeholder="Search roles by name or description"
                @submit="submitSearch"
                @reset="resetSearch"
            />

            <ResourceTable
                :has-results="roles.data.length > 0"
                empty-title="No roles found"
                empty-description="Try a different search term or create a new role for a new permission bundle."
                :empty-icon="Shield"
            >
                <template #head>
                    <tr
                        class="text-left text-xs tracking-wide text-muted-foreground uppercase"
                    >
                        <th class="px-4 py-3 font-medium">Role</th>
                        <th class="px-4 py-3 font-medium">Description</th>
                        <th class="px-4 py-3 font-medium">Permissions</th>
                        <th class="px-4 py-3 font-medium">Users</th>
                        <th class="px-4 py-3 text-right font-medium">
                            Actions
                        </th>
                    </tr>
                </template>

                <template #body>
                    <tr
                        v-for="role in roles.data"
                        :key="role.id"
                        class="align-top"
                    >
                        <td class="px-4 py-4">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-medium text-foreground">
                                    {{ role.name }}
                                </span>
                                <Badge
                                    v-if="role.isSystem"
                                    variant="outline"
                                    class="border-sky-200 bg-sky-50 text-sky-900 dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-100"
                                >
                                    System
                                </Badge>
                            </div>
                        </td>
                        <td
                            class="px-4 py-4 text-sm leading-6 text-muted-foreground"
                        >
                            {{ role.description || 'No description yet.' }}
                        </td>
                        <td class="px-4 py-4">
                            <Badge variant="secondary">
                                {{ role.permissionsCount }} permissions
                            </Badge>
                        </td>
                        <td class="px-4 py-4">
                            <Badge variant="outline">
                                {{ role.usersCount }} users
                            </Badge>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <ActionIconLink
                                    :href="editRole(role.id)"
                                    label="Edit role"
                                    :icon="SquarePen"
                                />

                                <ConfirmActionDialog
                                    v-if="role.canDelete"
                                    title="Delete role"
                                    :description="`Delete ${role.name}? Users assigned to this role will lose these inherited permissions.`"
                                    confirm-label="Delete role"
                                    @confirm="deleteSelectedRole(role)"
                                >
                                    <template #trigger>
                                        <Button
                                            variant="ghost"
                                            size="icon-sm"
                                            class="rounded-full text-destructive"
                                        >
                                            <Trash2 class="size-4" />
                                        </Button>
                                    </template>
                                </ConfirmActionDialog>
                            </div>
                        </td>
                    </tr>
                </template>
            </ResourceTable>

            <ResourcePagination :resource="roles" />
        </PageContainer>
    </AppLayout>
</template>
