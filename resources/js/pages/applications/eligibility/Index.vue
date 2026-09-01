<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle2, CircleAlert, ClipboardCheck, Eye, Play, XCircle } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ConfirmActionDialog from '@/components/admin/ConfirmActionDialog.vue';
import FormSection from '@/components/admin/FormSection.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import StatusBadge, { type BadgeTone } from '@/components/admin/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { show as showApplication } from '@/routes/applications';
import { index as validationsIndex, show as showValidation, store as storeValidation } from '@/routes/eligibility-validations';
import type { BreadcrumbItem } from '@/types';

type ApplicationContext = {
    id: number;
    programId: number;
    status: string;
};

type SubmittedVersion = {
    id: number;
    versionNumber: number;
    status: string;
    submittedAt: string | null;
};

type Validation = {
    id: number;
    applicationVersionId: number;
    status: 'passed' | 'failed' | 'error';
    result: Record<string, unknown> | null;
    executedAt: string;
    failureReason: string | null;
};

type Props = {
    application: ApplicationContext;
    validations: Validation[];
    submittedVersions: SubmittedVersion[];
    canValidate: boolean;
};

const props = defineProps<Props>();
const selectedVersionId = ref(props.submittedVersions[0]?.id?.toString() ?? '');

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Applications', href: showApplication(props.application.id) },
    { title: `Application #${props.application.id}`, href: showApplication(props.application.id) },
    { title: 'Eligibility validation', href: validationsIndex(props.application.id) },
];

const selectedVersion = computed(() =>
    props.submittedVersions.find((version) => version.id === Number(selectedVersionId.value)) ?? null,
);

const validationTone = (status: Validation['status']): BadgeTone => ({
    passed: 'success',
    failed: 'danger',
    error: 'warning',
}[status] as BadgeTone);

const validationIcon = (status: Validation['status']) => ({
    passed: CheckCircle2,
    failed: XCircle,
    error: CircleAlert,
}[status]);

const formatDate = (value: string | null): string => value
    ? new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value))
    : 'Not recorded';

const resultSummary = (validation: Validation): string => {
    if (validation.failureReason) return validation.failureReason;
    if (!validation.result || Object.keys(validation.result).length === 0) return 'No rule details were recorded.';

    return `${Object.keys(validation.result).length} rule result${Object.keys(validation.result).length === 1 ? '' : 's'} recorded.`;
};

const runValidation = (): void => {
    if (!selectedVersion.value) return;

    router.post(storeValidation(props.application.id).url, {
        application_version_id: selectedVersion.value.id,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Eligibility validation | Application #${application.id}`" />

        <PageContainer>
            <PageHeader
                title="Eligibility validation"
                description="Objective rule validation for submitted application versions. This does not make the final human eligibility decision."
            >
                <template #eyebrow>
                    <div class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-medium tracking-[0.2em] text-sky-900 uppercase dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-100">
                        <ClipboardCheck class="size-3.5" />
                        Program #{{ application.programId }}
                    </div>
                </template>
                <template #actions>
                    <Button as-child variant="outline">
                        <Link :href="showApplication(application.id)">
                            <ArrowLeft class="size-4" />
                            Application
                        </Link>
                    </Button>
                </template>
            </PageHeader>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_20rem]">
                <FormSection title="Validation history" :description="`Application #${application.id} · Status: ${application.status}`">
                    <div v-if="validations.length === 0" class="flex flex-col items-center justify-center gap-3 py-10 text-center">
                        <ClipboardCheck class="size-8 text-muted-foreground" />
                        <div>
                            <p class="font-medium text-foreground">No validations recorded</p>
                            <p class="mt-1 text-sm text-muted-foreground">Validation history will appear here after a submitted version is checked.</p>
                        </div>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="border-b border-border bg-muted/40 text-xs tracking-wide text-muted-foreground uppercase">
                                <tr>
                                    <th class="px-4 py-3 font-medium">Version</th>
                                    <th class="px-4 py-3 font-medium">Status</th>
                                    <th class="hidden px-4 py-3 font-medium md:table-cell">Executed</th>
                                    <th class="hidden px-4 py-3 font-medium lg:table-cell">Result</th>
                                    <th class="px-4 py-3 text-right font-medium">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="validation in validations" :key="validation.id" class="border-b border-border/60 last:border-b-0">
                                    <td class="px-4 py-4 font-medium text-foreground">
                                        Version {{ submittedVersions.find((version) => version.id === validation.applicationVersionId)?.versionNumber ?? `record #${validation.applicationVersionId}` }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <StatusBadge :label="validation.status" :tone="validationTone(validation.status)" />
                                    </td>
                                    <td class="hidden px-4 py-4 text-muted-foreground md:table-cell">{{ formatDate(validation.executedAt) }}</td>
                                    <td class="hidden max-w-sm px-4 py-4 text-muted-foreground lg:table-cell">{{ resultSummary(validation) }}</td>
                                    <td class="px-4 py-4 text-right">
                                        <Button as-child size="sm" variant="ghost">
                                            <Link :href="showValidation({ application: application.id, validation: validation.id })">
                                                <Eye class="size-4" />
                                                <span class="sr-only sm:not-sr-only sm:ml-2">View</span>
                                            </Link>
                                        </Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </FormSection>

                <FormSection v-if="canValidate" title="Run validation" description="Select the exact submitted version to evaluate." compact>
                    <div v-if="submittedVersions.length" class="space-y-4">
                        <label for="validation-version" class="block text-sm font-medium text-foreground">Submitted version</label>
                        <select
                            id="validation-version"
                            v-model="selectedVersionId"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none transition-[color,box-shadow] focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                        >
                            <option v-for="version in submittedVersions" :key="version.id" :value="String(version.id)">
                                Version {{ version.versionNumber }} · submitted {{ formatDate(version.submittedAt) }}
                            </option>
                        </select>

                        <p v-if="selectedVersion" class="text-sm leading-6 text-muted-foreground">
                            This action evaluates Version {{ selectedVersion.versionNumber }} only. It does not determine the final screening outcome.
                        </p>

                        <ConfirmActionDialog
                            title="Run eligibility validation"
                            :description="selectedVersion ? `Run objective eligibility validation for Version ${selectedVersion.versionNumber}? This records a new immutable validation result.` : 'Select a submitted version first.'"
                            confirm-label="Run validation"
                            @confirm="runValidation"
                        >
                            <template #trigger>
                                <Button class="w-full" :disabled="!selectedVersion">
                                    <Play class="size-4" />
                                    Run validation
                                </Button>
                            </template>
                        </ConfirmActionDialog>
                    </div>
                    <p v-else class="text-sm leading-6 text-muted-foreground">A submitted application version is required before validation can be run.</p>
                </FormSection>
            </div>

            <div v-if="validations.length" class="mt-6 grid gap-4 sm:grid-cols-3">
                <div v-for="status in ['passed', 'failed', 'error'] as const" :key="status" class="flex items-center gap-3 rounded-lg border border-border bg-card px-4 py-3">
                    <component :is="validationIcon(status)" class="size-5" :class="status === 'passed' ? 'text-emerald-600' : status === 'failed' ? 'text-red-600' : 'text-amber-600'" />
                    <div>
                        <p class="text-xs tracking-wide text-muted-foreground uppercase">{{ status }}</p>
                        <p class="font-semibold text-foreground">{{ validations.filter((validation) => validation.status === status).length }}</p>
                    </div>
                </div>
            </div>
        </PageContainer>
    </AppLayout>
</template>
