<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Shield, ShieldCheck } from 'lucide-vue-next';
import FormSection from '@/components/admin/FormSection.vue';
import RolePermissionCard from '@/components/admin/RolePermissionCard.vue';
import InputError from '@/components/InputError.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    edit as editRole,
    index as rolesIndex,
    update as updateRole,
} from '@/routes/roles';
import { update as updateRolePermissions } from '@/routes/roles/permissions';
import type { BreadcrumbItem, ManagedRole, PermissionGroup } from '@/types';

type Props = {
    role: ManagedRole;
    permissionGroups: PermissionGroup[];
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Roles',
        href: rolesIndex(),
    },
    {
        title: props.role.name,
        href: editRole(props.role.id),
    },
];

const detailsForm = useForm({
    name: props.role.name,
    description: props.role.description ?? '',
});

const permissionsForm = useForm({
    permissions: [...props.role.permissions],
});

const saveDetails = (): void => {
    detailsForm.put(updateRole(props.role.id).url);
};

const savePermissions = (): void => {
    permissionsForm.put(updateRolePermissions(props.role.id).url);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Edit ${role.name}`" />

        <PageContainer>
            <PageHeader
                :title="`Edit ${role.name}`"
                description="Update role metadata and the exact permissions this role should carry across the application."
            >
                <template #eyebrow>
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-medium tracking-[0.2em] text-sky-900 uppercase dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-100"
                    >
                        <ShieldCheck class="size-3.5" />
                        Role editor
                    </div>
                </template>
                <template #actions>
                    <div class="flex items-center gap-2">
                        <Badge v-if="role.isSystem" variant="outline">
                            System role
                        </Badge>
                        <Button as-child variant="outline">
                            <Link :href="rolesIndex()">
                                <ArrowLeft class="size-4" />
                                Back to roles
                            </Link>
                        </Button>
                    </div>
                </template>
            </PageHeader>

            <form class="grid gap-6" @submit.prevent="saveDetails">
                <FormSection
                    title="Role details"
                    description="System role names are fixed so the boilerplate always keeps predictable baseline roles."
                >
                    <template #headerAction>
                        <Button
                            type="submit"
                            :disabled="
                                detailsForm.processing || !detailsForm.isDirty
                            "
                        >
                            Save details
                        </Button>
                    </template>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="name">Role name</Label>
                            <Input
                                id="name"
                                v-model="detailsForm.name"
                                :disabled="role.isSystem"
                            />
                            <InputError :message="detailsForm.errors.name" />
                        </div>

                        <div class="grid gap-2 md:col-span-2">
                            <Label for="description">Description</Label>
                            <textarea
                                id="description"
                                v-model="detailsForm.description"
                                rows="4"
                                class="flex min-h-28 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs ring-offset-background outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                            />
                            <InputError
                                :message="detailsForm.errors.description"
                            />
                        </div>
                    </div>
                </FormSection>
            </form>

            <form class="grid gap-3" @submit.prevent="savePermissions">
                <RolePermissionCard
                    v-model:permissions="permissionsForm.permissions"
                    :permission-groups="permissionGroups"
                    :disabled="permissionsForm.processing || role.isSuperAdmin"
                />
                <InputError :message="permissionsForm.errors.permissions" />
                <div class="flex items-center justify-end gap-3">
                    <Button
                        type="submit"
                        :disabled="
                            permissionsForm.processing ||
                            !permissionsForm.isDirty ||
                            role.isSuperAdmin
                        "
                    >
                        <Shield class="size-4" />
                        Save permissions
                    </Button>
                </div>
            </form>
        </PageContainer>
    </AppLayout>
</template>
