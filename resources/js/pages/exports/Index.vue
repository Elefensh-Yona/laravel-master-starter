<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { FileOutput, Printer } from 'lucide-vue-next';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as exportsIndex } from '@/routes/exports';
import type { BreadcrumbItem, ExportResource } from '@/types';

type Props = {
    resources: ExportResource[];
};

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Export center',
        href: exportsIndex(),
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Export center" />

        <PageContainer>
            <PageHeader
                title="Export center"
                description="Reusable download and print actions live here so future modules can plug into one consistent pattern."
            />

            <section class="grid gap-4 md:grid-cols-2">
                <article
                    v-for="resource in resources"
                    :key="resource.key"
                    class="rounded-[1.5rem] border border-border/70 bg-card/85 p-6 shadow-sm backdrop-blur"
                >
                    <div class="flex items-center gap-3">
                        <div class="rounded-2xl bg-muted p-3">
                            <FileOutput
                                v-if="resource.format === 'CSV'"
                                class="size-5"
                            />
                            <Printer v-else class="size-5" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold">
                                {{ resource.title }}
                            </h2>
                            <p
                                class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                            >
                                {{ resource.format }}
                            </p>
                        </div>
                    </div>

                    <p class="mt-4 text-sm leading-6 text-muted-foreground">
                        {{ resource.description }}
                    </p>

                    <div class="mt-6">
                        <Button as-child>
                            <Link :href="resource.href">
                                {{ resource.actionLabel }}
                            </Link>
                        </Button>
                    </div>
                </article>
            </section>
        </PageContainer>
    </AppLayout>
</template>
