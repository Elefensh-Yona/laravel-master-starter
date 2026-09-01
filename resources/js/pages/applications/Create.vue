<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, PlusCircle } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { create as createApplication, index as applicationsIndex, store as storeApplication } from '@/routes/applications';
import type { ApplicationProgramOption, BreadcrumbItem } from '@/types';

type Props = {
    programs: ApplicationProgramOption[];
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Applications', href: applicationsIndex() },
    { title: 'Create', href: createApplication() },
];

const form = useForm({
    program_id: props.programs[0]?.id ?? '',
    applicant_type: 'INDIVIDUAL',
    reference: '',
});

const submit = (): void => {
    form.post(storeApplication().url);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Create application" />

        <PageContainer>
            <PageHeader
                title="Create application"
                description="Start a draft application for a published program."
            >
                <template #eyebrow>
                    <div class="inline-flex items-center gap-2 rounded-full border border-violet-200 bg-violet-50 px-3 py-1 text-xs font-medium tracking-[0.2em] text-violet-900 uppercase dark:border-violet-500/30 dark:bg-violet-500/10 dark:text-violet-100">
                        <PlusCircle class="size-3.5" />
                        New draft
                    </div>
                </template>
                <template #actions>
                    <Button as-child variant="outline">
                        <Link :href="applicationsIndex()">
                            <ArrowLeft class="size-4" />
                            Back to applications
                        </Link>
                    </Button>
                </template>
            </PageHeader>

            <form class="rounded-xl border border-border bg-card p-6 shadow-sm" @submit.prevent="submit">
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="grid gap-2 md:col-span-2">
                        <Label for="program_id">Program</Label>
                        <select
                            id="program_id"
                            v-model="form.program_id"
                            class="h-10 rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none transition-[color,box-shadow] focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                        >
                            <option v-for="program in programs" :key="program.id" :value="program.id">
                                {{ program.name }} ({{ program.code }})
                            </option>
                        </select>
                        <p v-if="form.errors.program_id" class="text-sm text-destructive">
                            {{ form.errors.program_id }}
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="applicant_type">Applicant type</Label>
                        <select
                            id="applicant_type"
                            v-model="form.applicant_type"
                            class="h-10 rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none transition-[color,box-shadow] focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                        >
                            <option value="INDIVIDUAL">Individual</option>
                            <option value="TEAM">Team</option>
                            <option value="ORGANIZATION">Organization</option>
                        </select>
                    </div>

                    <div class="grid gap-2">
                        <Label for="reference">Reference</Label>
                        <Input id="reference" v-model="form.reference" placeholder="Optional short reference" />
                        <p v-if="form.errors.reference" class="text-sm text-destructive">
                            {{ form.errors.reference }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <Button type="submit" :disabled="form.processing || !form.program_id">
                        <PlusCircle class="size-4" />
                        Create draft
                    </Button>
                </div>
            </form>
        </PageContainer>
    </AppLayout>
</template>
