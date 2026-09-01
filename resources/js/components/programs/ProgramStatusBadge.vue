<script setup lang="ts">
import StatusBadge, {
    type BadgeTone,
} from '@/components/admin/StatusBadge.vue';

type Props = {
    status: string;
};

const props = defineProps<Props>();

const toneForStatus = (status: string): BadgeTone => {
    const tones: Record<string, BadgeTone> = {
        archived: 'neutral',
        closed: 'warning',
        draft: 'info',
        published: 'success',
    };

    return tones[status] ?? 'neutral';
};

const label = props.status.replaceAll('_', ' ');
</script>

<template>
    <StatusBadge :label="label" :tone="toneForStatus(status)" />
</template>
