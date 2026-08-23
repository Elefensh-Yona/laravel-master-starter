<script setup lang="ts">
import { computed } from 'vue';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    id?: string;
    label?: string;
    description?: string;
    filename?: string | null;
    accept?: string;
}>();

const emit = defineEmits<{
    change: [file: File | null];
}>();

const inputId = computed(() => props.id ?? 'media-file');

const onChange = (event: Event): void => {
    const target = event.target as HTMLInputElement;
    emit('change', target.files?.[0] ?? null);
};
</script>

<template>
    <div class="grid gap-2">
        <Label :for="inputId">{{ label ?? 'File' }}</Label>
        <input
            :id="inputId"
            type="file"
            :accept="accept"
            class="block w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-primary file:px-3 file:py-2 file:text-sm file:font-medium file:text-primary-foreground"
            @change="onChange"
        />
        <p v-if="description" class="text-sm leading-6 text-muted-foreground">
            {{ description }}
        </p>
        <p v-if="filename" class="text-sm font-medium text-foreground">
            Selected: {{ filename }}
        </p>
    </div>
</template>
