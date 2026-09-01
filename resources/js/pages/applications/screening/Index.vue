<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, ClipboardCheck, Eye, FileCheck2, PlayCircle } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ConfirmActionDialog from '@/components/admin/ConfirmActionDialog.vue';
import FormSection from '@/components/admin/FormSection.vue';
import StatusBadge, { type BadgeTone } from '@/components/admin/StatusBadge.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { show as showApplication } from '@/routes/applications';
import { index as screeningsIndex, show as showScreening, store as storeScreening } from '@/routes/screenings';
import type { BreadcrumbItem } from '@/types';

type SubmittedVersion = { id: number; versionNumber: number; status: string; submittedAt: string | null };
type Screening = { id: number; applicationVersionId: number; status: 'in_review' | 'completed'; outcome: 'ELIGIBLE' | 'INELIGIBLE' | null; completedAt: string | null; screener: { id: number; name: string } | null; screenedBy: number };
type Props = { application: { id: number; programId: number; status: string }; screenings: Screening[]; submittedVersions: SubmittedVersion[]; latestValidation: { id: number; status: string; executedAt: string } | null; canScreen: boolean };

const props = defineProps<Props>();
const selectedVersionId = ref(props.submittedVersions[0]?.id?.toString() ?? '');
const selectedVersion = computed(() => props.submittedVersions.find((version) => version.id === Number(selectedVersionId.value)) ?? null);
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Application', href: showApplication(props.application.id) }, { title: 'Human screening', href: screeningsIndex(props.application.id) }];
const formatDate = (value: string | null): string => value ? new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value)) : 'Not recorded';
const statusTone = (status: Screening['status']): BadgeTone => (status === 'completed' ? 'success' : 'warning');
const startScreening = (): void => { if (selectedVersion.value) router.post(storeScreening(props.application.id).url, { application_version_id: selectedVersion.value.id }); };
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs"><Head :title="`Human screening | Application #${application.id}`" />
        <PageContainer>
            <PageHeader title="Human eligibility screening" description="Program Staff records the final eligibility outcome. Objective validation is supporting context only.">
                <template #eyebrow><div class="inline-flex items-center gap-2 rounded-full border border-violet-200 bg-violet-50 px-3 py-1 text-xs font-medium tracking-[0.2em] text-violet-900 uppercase dark:border-violet-500/30 dark:bg-violet-500/10 dark:text-violet-100"><FileCheck2 class="size-3.5" />Program #{{ application.programId }}</div></template>
                <template #actions><Button as-child variant="outline"><Link :href="showApplication(application.id)"><ArrowLeft class="size-4" />Application</Link></Button></template>
            </PageHeader>
            <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_20rem]">
                <FormSection title="Screening history" :description="`Application #${application.id} · Status: ${application.status}`">
                    <div v-if="screenings.length === 0" class="flex flex-col items-center gap-3 py-10 text-center"><FileCheck2 class="size-8 text-muted-foreground" /><div><p class="font-medium text-foreground">No screenings recorded</p><p class="mt-1 text-sm text-muted-foreground">A human screening record will appear here when Program Staff starts one.</p></div></div>
                    <div v-else class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="border-b border-border bg-muted/40 text-xs tracking-wide text-muted-foreground uppercase"><tr><th class="px-4 py-3">Version</th><th class="px-4 py-3">Status</th><th class="hidden px-4 py-3 md:table-cell">Outcome</th><th class="hidden px-4 py-3 lg:table-cell">Completed</th><th class="px-4 py-3 text-right">Details</th></tr></thead><tbody><tr v-for="screening in screenings" :key="screening.id" class="border-b border-border/60 last:border-b-0"><td class="px-4 py-4 font-medium text-foreground">Version {{ submittedVersions.find((version) => version.id === screening.applicationVersionId)?.versionNumber ?? `record #${screening.applicationVersionId}` }}</td><td class="px-4 py-4"><StatusBadge :label="screening.status.replace('_', ' ')" :tone="statusTone(screening.status)" /></td><td class="hidden px-4 py-4 md:table-cell">{{ screening.outcome ?? 'Pending' }}</td><td class="hidden px-4 py-4 text-muted-foreground lg:table-cell">{{ formatDate(screening.completedAt) }}</td><td class="px-4 py-4 text-right"><Button as-child size="sm" variant="ghost"><Link :href="showScreening({ application: application.id, screening: screening.id })"><Eye class="size-4" /><span class="sr-only sm:not-sr-only sm:ml-2">View</span></Link></Button></td></tr></tbody></table></div>
                </FormSection>
                <div class="space-y-6">
                    <FormSection v-if="canScreen" title="Start screening" description="Choose the exact submitted version for human review." compact>
                        <div v-if="submittedVersions.length" class="space-y-4"><label for="screening-version" class="block text-sm font-medium text-foreground">Submitted version</label><select id="screening-version" v-model="selectedVersionId" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"><option v-for="version in submittedVersions" :key="version.id" :value="String(version.id)">Version {{ version.versionNumber }} · submitted {{ formatDate(version.submittedAt) }}</option></select><p v-if="selectedVersion" class="text-sm leading-6 text-muted-foreground">Human screening will assess Version {{ selectedVersion.versionNumber }} only.</p><ConfirmActionDialog title="Start human screening" :description="selectedVersion ? `Start screening for Version ${selectedVersion.versionNumber}? The final outcome and rationale are recorded separately when completed.` : 'Select a submitted version first.'" confirm-label="Start screening" @confirm="startScreening"><template #trigger><Button class="w-full" :disabled="!selectedVersion"><PlayCircle class="size-4" />Start screening</Button></template></ConfirmActionDialog></div><p v-else class="text-sm text-muted-foreground">A submitted application version is required before screening can start.</p>
                    </FormSection>
                    <FormSection title="Latest validation" compact><div v-if="latestValidation" class="flex gap-3 text-sm"><ClipboardCheck class="mt-0.5 size-4 shrink-0 text-sky-600" /><div><p class="font-medium text-foreground">{{ latestValidation.status }}</p><p class="mt-1 text-muted-foreground">Executed {{ formatDate(latestValidation.executedAt) }}</p></div></div><p v-else class="text-sm leading-6 text-muted-foreground">No validation result is currently recorded for context.</p></FormSection>
                </div>
            </div>
        </PageContainer>
    </AppLayout>
</template>
