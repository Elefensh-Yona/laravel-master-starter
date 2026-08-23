<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, ShieldCheck, SquarePen, Trash2, Users } from 'lucide-vue-next';
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
    create as createUser,
    destroy as destroyUser,
    edit as editUser,
    index as usersIndex,
} from '@/routes/users';
import type {
    BreadcrumbItem,
    ManagedUser,
    PaginatedResource,
    ResourceFilters,
} from '@/types';

type Props = {
    users: PaginatedResource<ManagedUser>;
    filters: ResourceFilters;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: usersIndex(),
    },
];

const search = ref(props.filters.search);

const submitSearch = (): void => {
    router.get(
        usersIndex.url({
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

const deleteSelectedUser = (user: ManagedUser): void => {
    router.delete(destroyUser(user.id).url, {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Users" />

        <PageContainer>
            <PageHeader
                title="Users"
                description="Manage core user records and send people into the right parts of the application by assigning roles intentionally."
            >
                <template #eyebrow>
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-medium tracking-[0.2em] text-emerald-900 uppercase dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-100"
                    >
                        <Users class="size-3.5" />
                        Admin users
                    </div>
                </template>
                <template #actions>
                    <Button as-child>
                        <Link :href="createUser()">
                            <Plus class="size-4" />
                            Create user
                        </Link>
                    </Button>
                </template>
            </PageHeader>

            <ResourceToolbar
                v-model:search="search"
                search-placeholder="Search users by name or email"
                @submit="submitSearch"
                @reset="resetSearch"
            />

            <ResourceTable
                :has-results="users.data.length > 0"
                empty-title="No users found"
                empty-description="Try a different search term or create a new user record."
                :empty-icon="ShieldCheck"
            >
                <template #head>
                    <tr
                        class="text-left text-xs tracking-wide text-muted-foreground uppercase"
                    >
                        <th class="px-4 py-3 font-medium">User</th>
                        <th class="px-4 py-3 font-medium">Roles</th>
                        <th class="px-4 py-3 font-medium">Verification</th>
                        <th class="px-4 py-3 font-medium">Created</th>
                        <th class="px-4 py-3 text-right font-medium">
                            Actions
                        </th>
                    </tr>
                </template>

                <template #body>
                    <tr
                        v-for="user in users.data"
                        :key="user.id"
                        class="align-top"
                    >
                        <td class="px-4 py-4">
                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-medium text-foreground">
                                        {{ user.name }}
                                    </span>
                                    <Badge
                                        v-if="user.isCurrentUser"
                                        variant="outline"
                                        class="border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100"
                                    >
                                        Current session
                                    </Badge>
                                </div>
                                <p class="text-sm text-muted-foreground">
                                    {{ user.email }}
                                </p>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex flex-wrap gap-2">
                                <Badge
                                    v-for="role in user.roles"
                                    :key="`${user.id}-${role}`"
                                    variant="secondary"
                                >
                                    {{ role }}
                                </Badge>
                                <span
                                    v-if="user.roles.length === 0"
                                    class="text-sm text-muted-foreground"
                                >
                                    No roles
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <Badge
                                :variant="
                                    user.emailVerifiedAt
                                        ? 'secondary'
                                        : 'outline'
                                "
                            >
                                {{
                                    user.emailVerifiedAt
                                        ? 'Verified'
                                        : 'Unverified'
                                }}
                            </Badge>
                        </td>
                        <td class="px-4 py-4 text-sm text-muted-foreground">
                            {{
                                user.createdAt
                                    ? new Date(
                                          user.createdAt,
                                      ).toLocaleDateString()
                                    : 'N/A'
                            }}
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <ActionIconLink
                                    :href="editUser(user.id)"
                                    label="Edit user"
                                    :icon="SquarePen"
                                />

                                <ConfirmActionDialog
                                    v-if="!user.isCurrentUser"
                                    title="Delete user"
                                    :description="`Delete ${user.name}? This removes the user record and any role assignments tied to it.`"
                                    confirm-label="Delete user"
                                    @confirm="deleteSelectedUser(user)"
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

            <ResourcePagination :resource="users" />
        </PageContainer>
    </AppLayout>
</template>
