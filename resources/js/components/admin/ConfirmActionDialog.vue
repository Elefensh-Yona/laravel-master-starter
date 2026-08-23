<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

type Props = {
    title: string;
    description: string;
    confirmLabel: string;
    processing?: boolean;
};

withDefaults(defineProps<Props>(), {
    processing: false,
});

defineEmits<{
    confirm: [];
}>();
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <slot name="trigger" />
        </DialogTrigger>

        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription>{{ description }}</DialogDescription>
            </DialogHeader>

            <DialogFooter>
                <DialogClose as-child>
                    <Button variant="outline">Cancel</Button>
                </DialogClose>
                <DialogClose as-child>
                    <Button
                        variant="destructive"
                        :disabled="processing"
                        @click="$emit('confirm')"
                    >
                        {{ confirmLabel }}
                    </Button>
                </DialogClose>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
