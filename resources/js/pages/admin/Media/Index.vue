<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    Download,
    FolderKanban,
    Paperclip,
    Trash2,
    Upload,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ActionIconLink from '@/components/admin/ActionIconLink.vue';
import ConfirmActionDialog from '@/components/admin/ConfirmActionDialog.vue';
import MediaUploadField from '@/components/admin/MediaUploadField.vue';
import ResourcePagination from '@/components/admin/ResourcePagination.vue';
import ResourceTable from '@/components/admin/ResourceTable.vue';
import ResourceToolbar from '@/components/admin/ResourceToolbar.vue';
import InputError from '@/components/InputError.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    destroy as destroyMedia,
    download as downloadMedia,
    index as mediaIndex,
    store as storeMedia,
} from '@/routes/media';
import type {
    BreadcrumbItem,
    ManagedMedia,
    PaginatedResource,
    ResourceFilters,
} from '@/types';

type Props = {
    media: PaginatedResource<ManagedMedia>;
    filters: ResourceFilters;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Media',
        href: mediaIndex(),
    },
];

const search = ref(props.filters.search);
const selectedFilename = ref<string | null>(null);

const uploadForm = useForm<{
    file: File | null;
    collection: string;
}>({
    file: null,
    collection: 'library',
});

const submitSearch = (): void => {
    router.get(
        mediaIndex.url({
            query: {
                search: search.value || undefined,
            },
        }),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
};

const resetSearch = (): void => {
    search.value = '';
    submitSearch();
};

const onFileSelected = (file: File | null): void => {
    uploadForm.file = file;
    selectedFilename.value = file?.name ?? null;
};

const submitUpload = (): void => {
    uploadForm.post(storeMedia().url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            uploadForm.reset();
            uploadForm.collection = 'library';
            selectedFilename.value = null;
        },
    });
};

const deleteSelectedMedia = (media: ManagedMedia): void => {
    router.delete(destroyMedia(media.id).url, {
        preserveScroll: true,
    });
};

const formatSize = (bytes: number): string => {
    if (bytes < 1024) {
        return `${bytes} B`;
    }

    const kilobytes = bytes / 1024;

    if (kilobytes < 1024) {
        return `${kilobytes.toFixed(1)} KB`;
    }

    return `${(kilobytes / 1024).toFixed(1)} MB`;
};

const collectionHint = computed(() => {
    return uploadForm.collection.trim() === ''
        ? 'library'
        : uploadForm.collection.trim();
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Media" />

        <PageContainer>
            <PageHeader
                title="Media library"
                description="Upload once, reuse later. This is the shared file foundation for future records, attachments, and document-heavy modules."
            >
                <template #eyebrow>
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-violet-200 bg-violet-50 px-3 py-1 text-xs font-medium tracking-[0.2em] text-violet-900 uppercase dark:border-violet-500/30 dark:bg-violet-500/10 dark:text-violet-100"
                    >
                        <FolderKanban class="size-3.5" />
                        Media foundation
                    </div>
                </template>
            </PageHeader>

            <form
                class="grid gap-5 rounded-[1.5rem] border border-border/70 bg-card/85 p-5 shadow-sm backdrop-blur"
                @submit.prevent="submitUpload"
            >
                <div class="grid gap-5 md:grid-cols-[1.2fr_0.8fr]">
                    <MediaUploadField
                        label="Upload file"
                        description="Files land in the shared media library first so future modules can attach or reference them later."
                        :filename="selectedFilename"
                        @change="onFileSelected"
                    />

                    <div class="grid gap-2">
                        <Label for="collection">Collection</Label>
                        <Input
                            id="collection"
                            v-model="uploadForm.collection"
                            placeholder="library"
                        />
                        <p class="text-sm leading-6 text-muted-foreground">
                            Group files by purpose. Current target collection:
                            <span class="font-medium text-foreground">{{
                                collectionHint
                            }}</span>
                        </p>
                    </div>
                </div>

                <InputError :message="uploadForm.errors.file" />
                <InputError :message="uploadForm.errors.collection" />

                <div class="flex items-center justify-end gap-3">
                    <Button
                        type="submit"
                        :disabled="uploadForm.processing || !uploadForm.file"
                    >
                        <Upload class="size-4" />
                        Upload file
                    </Button>
                </div>
            </form>

            <ResourceToolbar
                v-model:search="search"
                search-placeholder="Search by filename, collection, or MIME type"
                @submit="submitSearch"
                @reset="resetSearch"
            />

            <ResourceTable
                :has-results="media.data.length > 0"
                empty-title="No files found"
                empty-description="Upload the first shared file or try a different search term."
                :empty-icon="Paperclip"
            >
                <template #head>
                    <tr
                        class="text-left text-xs tracking-wide text-muted-foreground uppercase"
                    >
                        <th class="px-4 py-3 font-medium">File</th>
                        <th class="px-4 py-3 font-medium">Collection</th>
                        <th class="px-4 py-3 font-medium">Size</th>
                        <th class="px-4 py-3 font-medium">Uploaded by</th>
                        <th class="px-4 py-3 font-medium">Created</th>
                        <th class="px-4 py-3 text-right font-medium">
                            Actions
                        </th>
                    </tr>
                </template>

                <template #body>
                    <tr
                        v-for="item in media.data"
                        :key="item.id"
                        class="align-top"
                    >
                        <td class="px-4 py-4">
                            <div class="space-y-1">
                                <div class="font-medium text-foreground">
                                    {{ item.originalName }}
                                </div>
                                <p class="text-sm text-muted-foreground">
                                    {{ item.mimeType ?? 'Unknown type' }}
                                </p>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <Badge variant="secondary">{{
                                item.collection
                            }}</Badge>
                        </td>
                        <td class="px-4 py-4 text-sm text-muted-foreground">
                            {{ formatSize(item.size) }}
                        </td>
                        <td class="px-4 py-4 text-sm text-muted-foreground">
                            {{ item.uploadedBy ?? 'Unknown' }}
                        </td>
                        <td class="px-4 py-4 text-sm text-muted-foreground">
                            {{
                                item.createdAt
                                    ? new Date(
                                          item.createdAt,
                                      ).toLocaleDateString()
                                    : 'N/A'
                            }}
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <ActionIconLink
                                    :href="downloadMedia(item.id)"
                                    label="Download file"
                                    :icon="Download"
                                />

                                <ConfirmActionDialog
                                    title="Delete file"
                                    :description="`Delete ${item.originalName}? This removes the file from storage and the shared media library.`"
                                    confirm-label="Delete file"
                                    @confirm="deleteSelectedMedia(item)"
                                >
                                    <template #trigger>
                                        <Button
                                            variant="ghost"
                                            size="icon-sm"
                                            class="rounded-full text-destructive"
                                        >
                                            <Trash2 class="size-4" />
                                        </Button>
                                    </template>
                                </ConfirmActionDialog>
                            </div>
                        </td>
                    </tr>
                </template>
            </ResourceTable>

            <ResourcePagination :resource="media" />
        </PageContainer>
    </AppLayout>
</template>
