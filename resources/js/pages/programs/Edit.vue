<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, SquarePen } from 'lucide-vue-next';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import ProgramForm from '@/components/programs/ProgramForm.vue';
import ProgramStatusBadge from '@/components/programs/ProgramStatusBadge.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    edit as editProgram,
    index as programsIndex,
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
    { title: 'Edit', href: editProgram(props.program.id) },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Edit ${program.name}`" />

        <PageContainer>
            <PageHeader
                :title="`Edit ${program.name}`"
                description="Update the editable program details. Publication and other controlled state transitions remain separate actions."
            >
                <template #eyebrow>
                    <div class="inline-flex items-center gap-2 rounded-full border border-violet-200 bg-violet-50 px-3 py-1 text-xs font-medium tracking-[0.2em] text-violet-900 uppercase dark:border-violet-500/30 dark:bg-violet-500/10 dark:text-violet-100">
                        <SquarePen class="size-3.5" />
                        Program editor
                    </div>
                </template>
                <template #actions>
                    <div class="flex items-center gap-2">
                        <ProgramStatusBadge :status="program.status" />
                        <Button as-child variant="outline">
                            <Link :href="showProgram(program.id)">
                                <ArrowLeft class="size-4" />
                                View program
                            </Link>
                        </Button>
                    </div>
                </template>
            </PageHeader>

            <ProgramForm :program="program" />
        </PageContainer>
    </AppLayout>
</template>
