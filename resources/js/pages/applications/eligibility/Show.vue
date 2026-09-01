<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle2, CircleAlert, ClipboardCheck, UserRound, XCircle } from 'lucide-vue-next';
import FormSection from '@/components/admin/FormSection.vue';
import StatusBadge, { type BadgeTone } from '@/components/admin/StatusBadge.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { show as showApplication } from '@/routes/applications';
import { index as validationsIndex } from '@/routes/eligibility-validations';
import type { BreadcrumbItem } from '@/types';

type Props = {
    application: { id: number; programId: number; status: string };
    validation: {
        id: number;
        applicationVersionId: number;
        status: 'passed' | 'failed' | 'error';
        result: Record<string, unknown> | null;
        executedAt: string;
        executedBy: number | null;
        failureReason: string | null;
        applicationVersion: { id: number; versionNumber: number; status: string; submittedAt: string | null } | null;
        executor: { id: number; name: string } | null;
    };
};

const props = defineProps<Props>();
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Application', href: showApplication(props.application.id) },
    { title: 'Eligibility validation', href: validationsIndex(props.application.id) },
    { title: `Validation #${props.validation.id}`, href: '#' },
];

const tone: BadgeTone = { passed: 'success', failed: 'danger', error: 'warning' }[props.validation.status] as BadgeTone;
const statusIcon = { passed: CheckCircle2, failed: XCircle, error: CircleAlert }[props.validation.status];
const formatDate = (value: string | null): string => value
    ? new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value))
    : 'Not recorded';
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Validation #${validation.id} | Application #${application.id}`" />
        <PageContainer>
            <PageHeader
                title="Objective eligibility validation"
                description="Recorded objective rule-check result. Human Program Staff screening remains the final eligibility decision."
            >
                <template #eyebrow>
                    <div class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-medium tracking-[0.2em] text-sky-900 uppercase dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-100">
                        <ClipboardCheck class="size-3.5" />
                        Application #{{ application.id }}
                    </div>
                </template>
                <template #actions>
                    <div class="flex flex-wrap gap-2">
                        <StatusBadge :label="validation.status" :tone="tone" />
                        <Button as-child variant="outline"><Link :href="validationsIndex(application.id)"><ArrowLeft class="size-4" />Validation history</Link></Button>
                    </div>
                </template>
            </PageHeader>

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
                <FormSection title="Recorded result" :description="`Validation #${validation.id} is immutable once recorded.`">
                    <div class="flex items-start gap-4">
                        <component :is="statusIcon" class="mt-0.5 size-6 shrink-0" :class="validation.status === 'passed' ? 'text-emerald-600' : validation.status === 'failed' ? 'text-red-600' : 'text-amber-600'" />
                        <div class="min-w-0">
                            <p class="font-medium text-foreground">{{ validation.status }}</p>
                            <p v-if="validation.failureReason" class="mt-1 text-sm leading-6 text-muted-foreground">{{ validation.failureReason }}</p>
                            <p v-else class="mt-1 text-sm leading-6 text-muted-foreground">Objective validation completed without a recorded failure reason.</p>
                        </div>
                    </div>
                    <div class="mt-6 border-t border-border/60 pt-5">
                        <p class="text-xs font-medium tracking-wide text-muted-foreground uppercase">Rule results supplied by validation</p>
                        <pre v-if="validation.result" class="mt-3 max-h-96 overflow-auto whitespace-pre-wrap break-words rounded-md border border-border bg-muted/30 p-4 text-xs leading-5 text-foreground">{{ JSON.stringify(validation.result, null, 2) }}</pre>
                        <p v-else class="mt-2 text-sm text-muted-foreground">No rule result payload was recorded.</p>
                    </div>
                </FormSection>

                <div class="space-y-6">
                    <FormSection title="Assessed version" compact>
                        <dl class="space-y-4 text-sm">
                            <div><dt class="text-xs tracking-wide text-muted-foreground uppercase">Application</dt><dd class="mt-1 font-medium text-foreground">#{{ application.id }}</dd></div>
                            <div><dt class="text-xs tracking-wide text-muted-foreground uppercase">Program</dt><dd class="mt-1 font-medium text-foreground">#{{ application.programId }}</dd></div>
                            <div><dt class="text-xs tracking-wide text-muted-foreground uppercase">Submitted version</dt><dd class="mt-1 font-medium text-foreground">{{ validation.applicationVersion ? `Version ${validation.applicationVersion.versionNumber}` : `Version record #${validation.applicationVersionId}` }}</dd></div>
                            <div v-if="validation.applicationVersion"><dt class="text-xs tracking-wide text-muted-foreground uppercase">Submitted</dt><dd class="mt-1 text-foreground">{{ formatDate(validation.applicationVersion.submittedAt) }}</dd></div>
                        </dl>
                    </FormSection>
                    <FormSection title="Audit record" compact>
                        <div class="flex gap-3 text-sm"><UserRound class="mt-0.5 size-4 shrink-0 text-sky-600" /><div><p class="font-medium text-foreground">{{ validation.executor?.name ?? `User #${validation.executedBy ?? 'unknown'}` }}</p><p class="mt-1 text-muted-foreground">Executed {{ formatDate(validation.executedAt) }}</p></div></div>
                    </FormSection>
                </div>
            </div>
        </PageContainer>
    </AppLayout>
</template>
