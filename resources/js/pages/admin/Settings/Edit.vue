<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Landmark, Save } from 'lucide-vue-next';
import FormSection from '@/components/admin/FormSection.vue';
import InputError from '@/components/InputError.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    edit as settingsEdit,
    update as updateSettings,
} from '@/routes/admin-settings';
import type { BreadcrumbItem, ManagedSettingGroup } from '@/types';

type Props = {
    settingGroups: ManagedSettingGroup[];
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Settings',
        href: settingsEdit(),
    },
];

const initialValues = Object.fromEntries(
    props.settingGroups.flatMap((group) =>
        group.fields.map((field) => [field.key, field.value ?? '']),
    ),
);

const form = useForm<Record<string, string>>(initialValues);

const submit = (): void => {
    form.put(updateSettings().url);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Settings" />

        <PageContainer>
            <PageHeader
                title="Settings"
                description="Control shared application and organization values from one reusable workspace."
            >
                <template #eyebrow>
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-medium tracking-[0.2em] text-sky-900 uppercase dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-100"
                    >
                        <Landmark class="size-3.5" />
                        Business settings
                    </div>
                </template>
            </PageHeader>

            <form class="grid gap-6" @submit.prevent="submit">
                <FormSection
                    v-for="group in settingGroups"
                    :key="group.key"
                    :title="group.title"
                    :description="group.description"
                >
                    <div class="grid gap-5 md:grid-cols-2">
                        <div
                            v-for="field in group.fields"
                            :key="field.key"
                            class="grid gap-2"
                            :class="{
                                'md:col-span-2': field.type === 'textarea',
                            }"
                        >
                            <Label :for="field.key">{{ field.label }}</Label>

                            <Input
                                v-if="field.type !== 'textarea'"
                                :id="field.key"
                                v-model="form[field.key]"
                                :type="
                                    field.type === 'email' ? 'email' : 'text'
                                "
                                :placeholder="field.placeholder ?? undefined"
                            />

                            <textarea
                                v-else
                                :id="field.key"
                                v-model="form[field.key]"
                                :rows="field.rows ?? 3"
                                :placeholder="field.placeholder ?? undefined"
                                class="flex min-h-28 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs ring-offset-background outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                            />

                            <p class="text-sm leading-6 text-muted-foreground">
                                {{ field.description }}
                            </p>
                            <InputError :message="form.errors[field.key]" />
                        </div>
                    </div>
                </FormSection>

                <div class="flex items-center justify-end gap-3">
                    <Button
                        type="submit"
                        :disabled="form.processing || !form.isDirty"
                    >
                        <Save class="size-4" />
                        Save settings
                    </Button>
                </div>
            </form>
        </PageContainer>
    </AppLayout>
</template>
