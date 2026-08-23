<script setup lang="ts">
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

type Props = {
    searchPlaceholder: string;
    createLabel?: string;
    canCreate?: boolean;
};

withDefaults(defineProps<Props>(), {
    createLabel: 'Create',
    canCreate: false,
});

const model = defineModel<string>('search', { required: true });

const hasSearch = computed(() => model.value.trim().length > 0);

defineEmits<{
    submit: [];
    reset: [];
}>();
</script>

<template>
    <div
        class="flex flex-col gap-3 rounded-[1.25rem] border border-border/70 bg-card/85 p-4 shadow-sm backdrop-blur lg:flex-row lg:items-center lg:justify-between"
    >
        <form
            class="flex flex-1 flex-col gap-3 sm:flex-row"
            @submit.prevent="$emit('submit')"
        >
            <Input
                v-model="model"
                :placeholder="searchPlaceholder"
                class="w-full sm:max-w-md"
            />
            <div class="flex items-center gap-2">
                <Button type="submit" variant="secondary"> Search </Button>
                <Button
                    v-if="hasSearch"
                    type="button"
                    variant="ghost"
                    @click="$emit('reset')"
                >
                    Reset
                </Button>
            </div>
        </form>

        <div v-if="$slots.actions || canCreate" class="flex items-center gap-2">
            <slot name="actions" />
        </div>
    </div>
</template>
