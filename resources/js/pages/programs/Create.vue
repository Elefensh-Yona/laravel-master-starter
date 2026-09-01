<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Rocket } from 'lucide-vue-next';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import ProgramForm from '@/components/programs/ProgramForm.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    create as createProgram,
    index as programsIndex,
} from '@/routes/programs';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Programs', href: programsIndex() },
    { title: 'Create', href: createProgram() },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Create program" />

        <PageContainer>
            <PageHeader
                title="Create program"
                description="Establish a draft program and its initial operating window. Publishing remains a separate reviewed action."
            >
                <template #eyebrow>
                    <div class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-medium tracking-[0.2em] text-sky-900 uppercase dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-100">
                        <Rocket class="size-3.5" />
                        EAIC program
                    </div>
                </template>
                <template #actions>
                    <Button as-child variant="outline">
                        <Link :href="programsIndex()">
                            <ArrowLeft class="size-4" />
                            Back to programs
                        </Link>
                    </Button>
                </template>
            </PageHeader>

            <ProgramForm />
        </PageContainer>
    </AppLayout>
</template>
