<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { AlertCircle, CircleCheck } from 'lucide-vue-next';
import { computed } from 'vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import type { SharedFlash } from '@/types';

const page = usePage();
const flash = computed(() => (page.props.flash ?? {}) as SharedFlash);
</script>

<template>
    <div
        v-if="flash.success || flash.error"
        class="space-y-3 px-4 pt-4 md:px-6"
    >
        <Alert
            v-if="flash.success"
            class="border-emerald-200/80 bg-emerald-50/90 text-emerald-950 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-50"
        >
            <CircleCheck class="size-4" />
            <AlertTitle>Success</AlertTitle>
            <AlertDescription>{{ flash.success }}</AlertDescription>
        </Alert>

        <Alert v-if="flash.error" variant="destructive">
            <AlertCircle class="size-4" />
            <AlertTitle>Action blocked</AlertTitle>
            <AlertDescription>{{ flash.error }}</AlertDescription>
        </Alert>
    </div>
</template>
