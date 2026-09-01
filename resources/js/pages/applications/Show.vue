<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, CheckCheck, PencilLine, Plus, Rocket, Send, Undo2, UserPlus, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import ConfirmActionDialog from '@/components/admin/ConfirmActionDialog.vue';
import FormSection from '@/components/admin/FormSection.vue';
import InputError from '@/components/InputError.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    edit as editApplication,
    index as applicationsIndex,
    revise as reviseApplication,
    show as showApplication,
    submit as submitApplication,
} from '@/routes/applications';
import { destroy as destroyMember, store as storeMember, update as updateMember } from '@/routes/applications/members';
import type { ApplicationUserOption, BreadcrumbItem, ManagedApplication, ManagedApplicationMember, ManagedApplicationVersion } from '@/types';

type Props = {
    application: ManagedApplication;
    currentVersion: ManagedApplicationVersion | null;
    members: ManagedApplicationMember[];
    memberUsers: ApplicationUserOption[];
    canManageMembers: boolean;
    canEdit: boolean;
    canSubmit: boolean;
    canRevise: boolean;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Applications', href: applicationsIndex() },
    { title: `#${props.application.id}`, href: showApplication(props.application.id) },
];

const memberForm = useForm({
    user_id: '',
    status: 'active',
});

const memberOptions = computed(() =>
    props.memberUsers.filter((user) => !props.members.some((member) => member.userId === user.id && member.status === 'active')),
);

const statusTone = (status: string): 'neutral' | 'info' | 'success' | 'warning' => {
    if (status === 'draft') return 'info';
    if (status === 'submitted') return 'success';
    if (status === 'archived') return 'neutral';
    if (status === 'ended') return 'warning';

    return 'warning';
};

const statusLabel = (status: string): string => status.replaceAll('_', ' ');

const formatDate = (value: string | null): string =>
    value
        ? new Intl.DateTimeFormat(undefined, {
              dateStyle: 'medium',
              timeStyle: 'short',
          }).format(new Date(value))
        : '—';

const submit = (): void => {
    router.post(submitApplication(props.application.id).url);
};

const revise = (): void => {
    router.post(reviseApplication(props.application.id).url);
};

const addMember = (): void => {
    memberForm.post(storeMember(props.application.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            memberForm.reset();
            memberForm.clearErrors();
            window.location.reload();
        },
    });
};

const updateMemberStatus = (member: ManagedApplicationMember, nextStatus: string): void => {
    router.put(updateMember({ application: props.application.id, member: member.id }).url, {
        status: nextStatus,
        end_reason: nextStatus === 'ended' ? 'removed_by_owner' : null,
    }, {
        preserveScroll: true,
        onSuccess: () => window.location.reload(),
    });
};

const removeMember = (member: ManagedApplicationMember): void => {
    router.delete(destroyMember({ application: props.application.id, member: member.id }).url, {
        preserveScroll: true,
        onSuccess: () => window.location.reload(),
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Application #${application.id}`" />

        <PageContainer>
            <PageHeader
                :title="`Application #${application.id}`"
                :description="application.reference ?? 'No reference set for this draft.'"
            >
                <template #eyebrow>
                    <div class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-medium tracking-[0.2em] text-sky-900 uppercase dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-100">
                        <Rocket class="size-3.5" />
                        {{ application.applicantType }} applicant
                    </div>
                </template>
                <template #actions>
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <StatusBadge :label="statusLabel(application.status)" :tone="statusTone(application.status)" />
                        <Button v-if="canEdit" as-child variant="outline">
                            <Link :href="editApplication(application.id)">
                                <PencilLine class="size-4" />
                                Edit draft
                            </Link>
                        </Button>
                        <Button v-if="canSubmit" @click="submit">
                            <Send class="size-4" />
                            Submit application
                        </Button>
                        <Button v-if="canRevise" variant="outline" @click="revise">
                            <Undo2 class="size-4" />
                            Revise submission
                        </Button>
                        <Button as-child variant="outline">
                            <Link :href="applicationsIndex()">
                                <ArrowLeft class="size-4" />
                                All applications
                            </Link>
                        </Button>
                    </div>
                </template>
            </PageHeader>

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_18rem]">
                <FormSection title="Application overview">
                    <dl class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-medium tracking-wide text-muted-foreground uppercase">Program</dt>
                            <dd class="mt-1 text-sm font-medium text-foreground">{{ application.programId }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium tracking-wide text-muted-foreground uppercase">Applicant type</dt>
                            <dd class="mt-1 text-sm font-medium text-foreground">{{ application.applicantType }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium tracking-wide text-muted-foreground uppercase">Reference</dt>
                            <dd class="mt-1 text-sm font-medium text-foreground">{{ application.reference ?? 'Not set' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium tracking-wide text-muted-foreground uppercase">Primary owner</dt>
                            <dd class="mt-1 text-sm font-medium text-foreground">#{{ application.primaryOwnerId }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-medium tracking-wide text-muted-foreground uppercase">Current version</dt>
                            <dd class="mt-1 text-sm text-foreground">
                                <span v-if="currentVersion">
                                    Version {{ currentVersion.versionNumber }} · {{ statusLabel(currentVersion.status) }}
                                </span>
                                <span v-else>Not available</span>
                            </dd>
                        </div>
                    </dl>
                </FormSection>

                <FormSection title="Lifecycle" compact>
                    <div class="space-y-5 text-sm">
                        <div class="flex items-start gap-3">
                            <CheckCheck class="mt-0.5 size-4 shrink-0 text-emerald-600 dark:text-emerald-300" />
                            <div>
                                <p class="font-medium text-foreground">Submitted</p>
                                <p class="mt-1 leading-5 text-muted-foreground">{{ formatDate(application.submittedAt) }}</p>
                            </div>
                        </div>
                        <div class="border-t border-border/60 pt-4">
                            <p class="text-xs font-medium tracking-wide text-muted-foreground uppercase">Created</p>
                            <p class="mt-1 font-medium text-foreground">{{ formatDate(application.createdAt) }}</p>
                        </div>
                    </div>
                </FormSection>
            </div>

            <div v-if="canManageMembers || members.length" class="mt-6 overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                <div class="flex flex-col gap-4 border-b border-border bg-muted/30 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-2">
                        <Users class="size-4 text-muted-foreground" />
                        <h2 class="text-base font-semibold text-foreground">Application members</h2>
                    </div>
                    <div v-if="canManageMembers" class="flex items-center gap-2">
                        <Button variant="outline" size="sm" @click="memberForm.reset(); memberForm.clearErrors();">
                            <Plus class="size-4" />
                            Add member
                        </Button>
                    </div>
                </div>

                <div v-if="canManageMembers" class="border-b border-border px-4 py-4">
                    <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_auto] md:items-end">
                        <div>
                            <label for="member-user" class="mb-2 block text-sm font-medium text-foreground">Select user</label>
                            <select
                                id="member-user"
                                v-model="memberForm.user_id"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none transition-[color,box-shadow] focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                            >
                                <option value="">Choose a user</option>
                                <option v-for="user in memberOptions" :key="user.id" :value="String(user.id)">
                                    {{ user.name }} — {{ user.email }}
                                </option>
                            </select>
                            <InputError :message="memberForm.errors.user_id" />
                        </div>

                        <Button :disabled="memberForm.processing || !memberForm.user_id" @click="addMember">
                            <UserPlus class="size-4" />
                            Add member
                        </Button>
                    </div>
                </div>

                <div v-if="members.length === 0" class="px-4 py-8 text-center text-sm text-muted-foreground">
                    No application members are currently recorded.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b border-border bg-muted/40 text-xs tracking-wide text-muted-foreground uppercase">
                            <tr>
                                <th class="px-4 py-3 font-medium">Member</th>
                                <th class="px-4 py-3 font-medium">Status</th>
                                <th class="hidden px-4 py-3 font-medium md:table-cell">Joined</th>
                                <th class="px-4 py-3 text-right font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="member in members" :key="member.id" class="border-b border-border/60 last:border-b-0">
                                <td class="px-4 py-4">
                                    <div class="space-y-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="font-medium text-foreground">{{ member.userName ?? `User #${member.userId}` }}</span>
                                            <span
                                                v-if="member.userId === application.primaryOwnerId"
                                                class="rounded-full border border-violet-200 bg-violet-50 px-2 py-0.5 text-[10px] font-medium tracking-[0.18em] text-violet-900 uppercase dark:border-violet-500/30 dark:bg-violet-500/10 dark:text-violet-100"
                                            >
                                                Owner
                                            </span>
                                        </div>
                                        <p v-if="member.userEmail" class="text-xs text-muted-foreground">{{ member.userEmail }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <StatusBadge :label="statusLabel(member.status)" :tone="member.status === 'active' ? 'success' : 'warning'" />
                                </td>
                                <td class="hidden px-4 py-4 text-muted-foreground md:table-cell">
                                    {{ formatDate(member.joinedAt) }}
                                </td>
                                <td class="px-4 py-4">
                                    <div v-if="canManageMembers" class="flex items-center justify-end gap-2">
                                        <label class="sr-only" :for="`member-status-${member.id}`">Member status</label>
                                        <select
                                            :id="`member-status-${member.id}`"
                                            :value="member.status"
                                            class="rounded-md border border-input bg-background px-2 py-1.5 text-xs shadow-xs outline-none transition-[color,box-shadow] focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                            @change="updateMemberStatus(member, ($event.target as HTMLSelectElement).value)"
                                        >
                                            <option value="active">Active</option>
                                            <option value="ended">Ended</option>
                                        </select>

                                        <ConfirmActionDialog
                                            v-if="member.userId !== application.primaryOwnerId"
                                            title="Remove member"
                                            :description="`Deactivate ${member.userName ?? 'this member'} from this application? This preserves the historical record but removes active authority.`"
                                            confirm-label="Remove member"
                                            @confirm="removeMember(member)"
                                        >
                                            <template #trigger>
                                                <Button variant="ghost" size="sm" class="text-destructive">
                                                    Remove
                                                </Button>
                                            </template>
                                        </ConfirmActionDialog>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </PageContainer>
    </AppLayout>
</template>
