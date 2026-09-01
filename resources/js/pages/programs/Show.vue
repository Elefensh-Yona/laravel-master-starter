<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, CalendarDays, Clock3, Rocket, SquarePen } from 'lucide-vue-next';
import ConfirmActionDialog from '@/components/admin/ConfirmActionDialog.vue';
import FormSection from '@/components/admin/FormSection.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import ProgramStatusBadge from '@/components/programs/ProgramStatusBadge.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    edit as editProgram,
    index as programsIndex,
    publish as publishProgram,
    show as showProgram,
} from '@/routes/programs';
import type { BreadcrumbItem, ManagedProgram } from '@/types';

type Props = {
    program: ManagedProgram;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Programs', href: programsIndex() },
    { title: props.program.name, href: showProgram(props.program.id) },
];

const formatDate = (value: string): string =>
    new Intl.DateTimeFormat(undefined, {
        dateStyle: 'full',
        timeStyle: 'short',
        timeZone: props.program.timezone,
    }).format(new Date(value));

const publish = (): void => {
    router.post(publishProgram(props.program.id).url);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="program.name" />

        <PageContainer>
            <PageHeader
                :title="program.name"
                :description="program.description ?? 'No program description has been provided yet.'"
            >
                <template #eyebrow>
                    <div class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-medium tracking-[0.2em] text-sky-900 uppercase dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-100">
                        <Rocket class="size-3.5" />
                        {{ program.code }}
                    </div>
                </template>
                <template #actions>
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <ProgramStatusBadge :status="program.status" />
                        <Button v-if="program.canEdit" as-child variant="outline">
                            <Link :href="editProgram(program.id)">
                                <SquarePen class="size-4" />
                                Edit
                            </Link>
                        </Button>
                        <ConfirmActionDialog
                            v-if="program.canPublish && program.status === 'draft'"
                            title="Publish program"
                            :description="`Publish ${program.name}? Its visibility will change for permitted viewers.`"
                            confirm-label="Publish program"
                            @confirm="publish"
                        >
                            <template #trigger>
                                <Button>Publish program</Button>
                            </template>
                        </ConfirmActionDialog>
                        <Button as-child variant="outline">
                            <Link :href="programsIndex()">
                                <ArrowLeft class="size-4" />
                                All programs
                            </Link>
                        </Button>
                    </div>
                </template>
            </PageHeader>

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_18rem]">
                <FormSection title="Program information">
                    <dl class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-medium tracking-wide text-muted-foreground uppercase">Program code</dt>
                            <dd class="mt-1 text-sm font-medium text-foreground">{{ program.code }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium tracking-wide text-muted-foreground uppercase">Slug</dt>
                            <dd class="mt-1 text-sm font-medium text-foreground">{{ program.slug }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-medium tracking-wide text-muted-foreground uppercase">Description</dt>
                            <dd class="mt-1 whitespace-pre-line text-sm leading-6 text-foreground">{{ program.description ?? 'No description provided.' }}</dd>
                        </div>
                    </dl>
                </FormSection>

                <FormSection title="Operating window" compact>
                    <div class="space-y-5 text-sm">
                        <div class="flex gap-3">
                            <CalendarDays class="mt-0.5 size-4 shrink-0 text-sky-600 dark:text-sky-300" />
                            <div>
                                <p class="font-medium text-foreground">Opens</p>
                                <p class="mt-1 leading-5 text-muted-foreground">{{ formatDate(program.opensAt) }}</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <Clock3 class="mt-0.5 size-4 shrink-0 text-violet-600 dark:text-violet-300" />
                            <div>
                                <p class="font-medium text-foreground">Closes</p>
                                <p class="mt-1 leading-5 text-muted-foreground">{{ formatDate(program.closesAt) }}</p>
                            </div>
                        </div>
                        <div class="border-t border-border/60 pt-4">
                            <p class="text-xs font-medium tracking-wide text-muted-foreground uppercase">Timezone</p>
                            <p class="mt-1 font-medium text-foreground">{{ program.timezone }}</p>
                        </div>
                    </div>
                </FormSection>
            </div>
        </PageContainer>
    </AppLayout>
</template>
