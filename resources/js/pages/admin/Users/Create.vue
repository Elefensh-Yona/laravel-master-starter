<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, ShieldCheck, UserPlus } from 'lucide-vue-next';
import FormSection from '@/components/admin/FormSection.vue';
import UserRoleCard from '@/components/admin/UserRoleCard.vue';
import InputError from '@/components/InputError.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    create as createUser,
    index as usersIndex,
    store as storeUser,
} from '@/routes/users';
import type { BreadcrumbItem, RoleOption } from '@/types';

type Props = {
    roles: RoleOption[];
};

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: usersIndex(),
    },
    {
        title: 'Create',
        href: createUser(),
    },
];

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    roles: [] as string[],
});

const submit = (): void => {
    form.post(storeUser().url);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Create user" />

        <PageContainer>
            <PageHeader
                title="Create user"
                description="Create a user record and assign only the roles needed for their first access level."
            >
                <template #eyebrow>
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-medium tracking-[0.2em] text-emerald-900 uppercase dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-100"
                    >
                        <UserPlus class="size-3.5" />
                        New user
                    </div>
                </template>
                <template #actions>
                    <Button as-child variant="outline">
                        <Link :href="usersIndex()">
                            <ArrowLeft class="size-4" />
                            Back to users
                        </Link>
                    </Button>
                </template>
            </PageHeader>

            <form class="grid gap-6" @submit.prevent="submit">
                <FormSection>
                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="name">Name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="Yonas Example"
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="email">Email</Label>
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="user@example.com"
                            />
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password">Password</Label>
                            <Input
                                id="password"
                                v-model="form.password"
                                type="password"
                            />
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password_confirmation"
                                >Confirm password</Label
                            >
                            <Input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                            />
                        </div>
                    </div>
                </FormSection>

                <UserRoleCard
                    v-model:selected-roles="form.roles"
                    :roles="roles"
                    :disabled="form.processing"
                />
                <InputError :message="form.errors.roles" />

                <div class="flex items-center justify-end gap-3">
                    <Button type="submit" :disabled="form.processing">
                        <ShieldCheck class="size-4" />
                        Create user
                    </Button>
                </div>
            </form>
        </PageContainer>
    </AppLayout>
</template>
