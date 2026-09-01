<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, ClipboardCheck, FileCheck2, UserRound } from 'lucide-vue-next';
import FormSection from '@/components/admin/FormSection.vue';
import InputError from '@/components/InputError.vue';
import StatusBadge, { type BadgeTone } from '@/components/admin/StatusBadge.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { show as showApplication } from '@/routes/applications';
import { index as screeningsIndex, update as updateScreening } from '@/routes/screenings';
import type { BreadcrumbItem } from '@/types';

type Props = { application: { id: number; programId: number; status: string }; screening: { id: number; applicationVersionId: number; validationId: number | null; status: 'in_review' | 'completed'; outcome: 'ELIGIBLE' | 'INELIGIBLE' | null; rationale: string | null; screenedBy: number; completedAt: string | null; applicationVersion: { id: number; versionNumber: number; status: string; submittedAt: string | null } | null; screener: { id: number; name: string } | null; validation: { id: number; status: string; executedAt: string } | null } };
const props = defineProps<Props>();
const form = useForm({ outcome: '' as '' | 'ELIGIBLE' | 'INELIGIBLE', rationale: '' });
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Application', href: showApplication(props.application.id) }, { title: 'Human screening', href: screeningsIndex(props.application.id) }, { title: `Screening #${props.screening.id}`, href: '#' }];
const formatDate = (value: string | null): string => value ? new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value)) : 'Not recorded';
const statusTone: BadgeTone = props.screening.status === 'completed' ? 'success' : 'warning';
const outcomeTone = (outcome: 'ELIGIBLE' | 'INELIGIBLE'): BadgeTone => outcome === 'ELIGIBLE' ? 'success' : 'danger';
const complete = (): void => form.put(updateScreening({ application: props.application.id, screening: props.screening.id }).url);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs"><Head :title="`Screening #${screening.id} | Application #${application.id}`" />
        <PageContainer>
            <PageHeader title="Human eligibility screening" description="A Program Staff screening record. This is distinct from objective eligibility validation.">
                <template #eyebrow><div class="inline-flex items-center gap-2 rounded-full border border-violet-200 bg-violet-50 px-3 py-1 text-xs font-medium tracking-[0.2em] text-violet-900 uppercase dark:border-violet-500/30 dark:bg-violet-500/10 dark:text-violet-100"><FileCheck2 class="size-3.5" />Application #{{ application.id }}</div></template>
                <template #actions><div class="flex flex-wrap gap-2"><StatusBadge :label="screening.status.replace('_', ' ')" :tone="statusTone" /><StatusBadge v-if="screening.outcome" :label="screening.outcome" :tone="outcomeTone(screening.outcome)" /><Button as-child variant="outline"><Link :href="screeningsIndex(application.id)"><ArrowLeft class="size-4" />Screening history</Link></Button></div></template>
            </PageHeader>
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
                <FormSection v-if="screening.status === 'in_review'" title="Complete screening" description="Select the final human outcome and record the required rationale.">
                    <div class="grid gap-5"><div><label for="outcome" class="mb-2 block text-sm font-medium text-foreground">Outcome</label><select id="outcome" v-model="form.outcome" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"><option value="" disabled>Select outcome</option><option value="ELIGIBLE">ELIGIBLE</option><option value="INELIGIBLE">INELIGIBLE</option></select><InputError :message="form.errors.outcome" /></div><div><label for="rationale" class="mb-2 block text-sm font-medium text-foreground">Rationale</label><textarea id="rationale" v-model="form.rationale" rows="7" maxlength="2000" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50" /><div class="mt-1 flex justify-between gap-3"><InputError :message="form.errors.rationale" /><span class="text-xs text-muted-foreground">{{ form.rationale.length }}/2000</span></div></div><Button :disabled="form.processing || !form.outcome || !form.rationale.trim()" @click="complete">Complete screening</Button><p v-if="form.recentlySuccessful" class="text-sm text-emerald-700 dark:text-emerald-300">Screening completed.</p></div>
                </FormSection>
                <FormSection v-else title="Completed screening" description="This finalized outcome and rationale are immutable."><div class="space-y-5"><div><p class="text-xs tracking-wide text-muted-foreground uppercase">Outcome</p><StatusBadge v-if="screening.outcome" class="mt-2" :label="screening.outcome" :tone="outcomeTone(screening.outcome)" /></div><div><p class="text-xs tracking-wide text-muted-foreground uppercase">Rationale</p><p class="mt-2 whitespace-pre-wrap break-words text-sm leading-6 text-foreground">{{ screening.rationale }}</p></div></div></FormSection>
                <div class="space-y-6"><FormSection title="Assessed version" compact><dl class="space-y-4 text-sm"><div><dt class="text-xs tracking-wide text-muted-foreground uppercase">Application</dt><dd class="mt-1 font-medium text-foreground">#{{ application.id }}</dd></div><div><dt class="text-xs tracking-wide text-muted-foreground uppercase">Program</dt><dd class="mt-1 font-medium text-foreground">#{{ application.programId }}</dd></div><div><dt class="text-xs tracking-wide text-muted-foreground uppercase">Submitted version</dt><dd class="mt-1 font-medium text-foreground">{{ screening.applicationVersion ? `Version ${screening.applicationVersion.versionNumber}` : `Version record #${screening.applicationVersionId}` }}</dd></div><div v-if="screening.applicationVersion"><dt class="text-xs tracking-wide text-muted-foreground uppercase">Submitted</dt><dd class="mt-1 text-foreground">{{ formatDate(screening.applicationVersion.submittedAt) }}</dd></div></dl></FormSection><FormSection title="Audit and validation context" compact><div class="space-y-5 text-sm"><div class="flex gap-3"><UserRound class="mt-0.5 size-4 shrink-0 text-violet-600" /><div><p class="font-medium text-foreground">{{ screening.screener?.name ?? `User #${screening.screenedBy}` }}</p><p class="mt-1 text-muted-foreground">Screening record author</p></div></div><div v-if="screening.completedAt" class="border-t border-border/60 pt-4"><p class="text-xs tracking-wide text-muted-foreground uppercase">Completed</p><p class="mt-1 text-foreground">{{ formatDate(screening.completedAt) }}</p></div><div v-if="screening.validation" class="border-t border-border/60 pt-4"><div class="flex gap-3"><ClipboardCheck class="mt-0.5 size-4 shrink-0 text-sky-600" /><div><p class="font-medium text-foreground">Validation #{{ screening.validation.id }}: {{ screening.validation.status }}</p><p class="mt-1 text-muted-foreground">Executed {{ formatDate(screening.validation.executedAt) }}</p></div></div></div></div></FormSection></div>
            </div>
        </PageContainer>
    </AppLayout>
</template>
