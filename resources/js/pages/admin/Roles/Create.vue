<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Shield, Sparkles } from 'lucide-vue-next';
import FormSection from '@/components/admin/FormSection.vue';
import RolePermissionCard from '@/components/admin/RolePermissionCard.vue';
import InputError from '@/components/InputError.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    create as createRole,
    index as rolesIndex,
    store as storeRole,
} from '@/routes/roles';
import type { BreadcrumbItem, PermissionGroup } from '@/types';

type Props = {
    permissionGroups: PermissionGroup[];
};

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Roles',
        href: rolesIndex(),
    },
    {
        title: 'Create',
        href: createRole(),
    },
];

const form = useForm({
    name: '',
    description: '',
    permissions: [] as string[],
});

const submit = (): void => {
    form.post(storeRole().url);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Create role" />

        <PageContainer>
            <PageHeader
                title="Create role"
                description="Use roles to group permissions into reusable access bundles instead of managing users one permission at a time."
            >
                <template #eyebrow>
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-medium tracking-[0.2em] text-sky-900 uppercase dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-100"
                    >
                        <Sparkles class="size-3.5" />
                        New access profile
                    </div>
                </template>
                <template #actions>
                    <Button as-child variant="outline">
                        <Link :href="rolesIndex()">
                            <ArrowLeft class="size-4" />
                            Back to roles
                        </Link>
                    </Button>
                </template>
            </PageHeader>

            <form class="grid gap-6" @submit.prevent="submit">
                <FormSection>
                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="name">Role name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="SupportLead"
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="grid gap-2 md:col-span-2">
                            <Label for="description">Description</Label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                class="flex min-h-28 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs ring-offset-background outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                placeholder="Explain what this role is meant to do."
                            />
                            <InputError :message="form.errors.description" />
                        </div>
                    </div>
                </FormSection>

                <RolePermissionCard
                    v-model:permissions="form.permissions"
                    :permission-groups="permissionGroups"
                    :disabled="form.processing"
                />

                <InputError :message="form.errors.permissions" />

                <div class="flex items-center justify-end gap-3">
                    <Button type="submit" :disabled="form.processing">
                        <Shield class="size-4" />
                        Create role
                    </Button>
                </div>
            </form>
        </PageContainer>
    </AppLayout>
</template>
