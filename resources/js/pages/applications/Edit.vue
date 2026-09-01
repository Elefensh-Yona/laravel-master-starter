<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from 'lucide-vue-next';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { edit as editApplication, index as applicationsIndex, show as showApplication, update as updateApplication } from '@/routes/applications';
import type { BreadcrumbItem, ManagedApplication, ManagedApplicationVersion } from '@/types';

type Props = {
    application: ManagedApplication;
    currentVersion: ManagedApplicationVersion | null;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Applications', href: applicationsIndex() },
    { title: `#${props.application.id}`, href: showApplication(props.application.id) },
    { title: 'Edit', href: editApplication(props.application.id) },
];

const initialContent = JSON.stringify({
    summary: '',
    category: '',
}, null, 2);

const form = useForm({
    content: initialContent,
    metadata: {},
});

const submit = (): void => {
    form.transform((data) => ({
        ...data,
        content: JSON.parse(data.content || '{}'),
    })).put(updateApplication(props.application.id).url);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Edit application #${application.id}`" />

        <PageContainer>
            <PageHeader
                :title="`Edit application #${application.id}`"
                description="Update the draft content payload for this application version."
            >
                <template #actions>
                    <Button as-child variant="outline">
                        <Link :href="showApplication(application.id)">
                            <ArrowLeft class="size-4" />
                            Back to application
                        </Link>
                    </Button>
                </template>
            </PageHeader>

            <form class="rounded-xl border border-border bg-card p-6 shadow-sm" @submit.prevent="submit">
                <div class="space-y-4">
                    <div>
                        <label for="content" class="mb-2 block text-sm font-medium text-foreground">Draft content JSON</label>
                        <textarea
                            id="content"
                            v-model="form.content"
                            rows="18"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none transition-[color,box-shadow] placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                            placeholder='{"summary": "...", "category": "innovation"}'
                        />
                    </div>
                    <p v-if="form.errors.content" class="text-sm text-destructive">
                        {{ form.errors.content }}
                    </p>
                </div>

                <div class="mt-6 flex items-center justify-end">
                    <Button type="submit" :disabled="form.processing">
                        <Save class="size-4" />
                        Save draft
                    </Button>
                </div>
            </form>
        </PageContainer>
    </AppLayout>
</template>
