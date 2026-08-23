<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, ShieldCheck, UserCog } from 'lucide-vue-next';
import FormSection from '@/components/admin/FormSection.vue';
import UserRoleCard from '@/components/admin/UserRoleCard.vue';
import InputError from '@/components/InputError.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    edit as editUser,
    index as usersIndex,
    update as updateUser,
} from '@/routes/users';
import { update as updateUserRoles } from '@/routes/users/roles';
import type { BreadcrumbItem, ManagedUser, RoleOption } from '@/types';

type Props = {
    user: ManagedUser;
    roles: RoleOption[];
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: usersIndex(),
    },
    {
        title: props.user.name,
        href: editUser(props.user.id),
    },
];

const detailsForm = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
});

const rolesForm = useForm({
    roles: [...props.user.roles],
});

const saveDetails = (): void => {
    detailsForm.put(updateUser(props.user.id).url);
};

const saveRoles = (): void => {
    rolesForm.put(updateUserRoles(props.user.id).url);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Edit ${user.name}`" />

        <PageContainer>
            <PageHeader
                :title="`Edit ${user.name}`"
                description="Update user profile details and keep their role assignments aligned with the access they actually need."
            >
                <template #eyebrow>
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-medium tracking-[0.2em] text-emerald-900 uppercase dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-100"
                    >
                        <UserCog class="size-3.5" />
                        User editor
                    </div>
                </template>
                <template #actions>
                    <div class="flex items-center gap-2">
                        <Badge
                            v-if="user.isCurrentUser"
                            variant="outline"
                            class="border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100"
                        >
                            Current session
                        </Badge>
                        <Button as-child variant="outline">
                            <Link :href="usersIndex()">
                                <ArrowLeft class="size-4" />
                                Back to users
                            </Link>
                        </Button>
                    </div>
                </template>
            </PageHeader>

            <form class="grid gap-6" @submit.prevent="saveDetails">
                <FormSection
                    title="User details"
                    description="Changing the email address will reset email verification for this user."
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
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="detailsForm.name" />
                            <InputError :message="detailsForm.errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="email">Email</Label>
                            <Input
                                id="email"
                                v-model="detailsForm.email"
                                type="email"
                            />
                            <InputError :message="detailsForm.errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password">New password</Label>
                            <Input
                                id="password"
                                v-model="detailsForm.password"
                                type="password"
                            />
                            <InputError
                                :message="detailsForm.errors.password"
                            />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password_confirmation"
                                >Confirm new password</Label
                            >
                            <Input
                                id="password_confirmation"
                                v-model="detailsForm.password_confirmation"
                                type="password"
                            />
                        </div>
                    </div>
                </FormSection>
            </form>

            <form class="grid gap-3" @submit.prevent="saveRoles">
                <UserRoleCard
                    v-model:selected-roles="rolesForm.roles"
                    :roles="roles"
                    :disabled="rolesForm.processing"
                />
                <InputError :message="rolesForm.errors.roles" />
                <div class="flex items-center justify-end gap-3">
                    <Button
                        type="submit"
                        :disabled="rolesForm.processing || !rolesForm.isDirty"
                    >
                        <ShieldCheck class="size-4" />
                        Save roles
                    </Button>
                </div>
            </form>
        </PageContainer>
    </AppLayout>
</template>
