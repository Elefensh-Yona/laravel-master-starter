<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Save } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import FormSection from '@/components/admin/FormSection.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { store as storeProgram, update as updateProgram } from '@/routes/programs';
import type { ManagedProgram } from '@/types';

type Props = {
    program?: ManagedProgram;
};

const props = defineProps<Props>();

const toDateTimeLocal = (value?: string): string => value?.slice(0, 16) ?? '';

const form = useForm({
    name: props.program?.name ?? '',
    code: props.program?.code ?? '',
    slug: props.program?.slug ?? '',
    description: props.program?.description ?? '',
    timezone: props.program?.timezone ?? 'Africa/Addis_Ababa',
    opens_at: toDateTimeLocal(props.program?.opensAt),
    closes_at: toDateTimeLocal(props.program?.closesAt),
});

const submit = (): void => {
    if (props.program) {
        form.put(updateProgram(props.program.id).url);

        return;
    }

    form.post(storeProgram().url);
};
</script>

<template>
    <form class="grid gap-6" @submit.prevent="submit">
        <FormSection
            title="Program details"
            description="Set the public identity and application window for this program. Publication remains a separate confirmed action."
        >
            <div class="grid gap-5 md:grid-cols-2">
                <div class="grid gap-2 md:col-span-2">
                    <Label for="name">Program name</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        placeholder="Ethiopian AI Innovation Challenge"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="code">Program code</Label>
                    <Input
                        id="code"
                        v-model="form.code"
                        placeholder="EAIC-2026-01"
                    />
                    <InputError :message="form.errors.code" />
                </div>

                <div class="grid gap-2">
                    <Label for="slug">Slug</Label>
                    <Input
                        id="slug"
                        v-model="form.slug"
                        placeholder="eaic-innovation-challenge"
                    />
                    <InputError :message="form.errors.slug" />
                </div>

                <div class="grid gap-2 md:col-span-2">
                    <Label for="description">Description</Label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="5"
                        class="min-h-28 rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none transition-[color,box-shadow] placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50"
                        placeholder="Describe the purpose and focus of this program."
                    />
                    <InputError :message="form.errors.description" />
                </div>

                <div class="grid gap-2">
                    <Label for="timezone">Program timezone</Label>
                    <Input
                        id="timezone"
                        v-model="form.timezone"
                        list="program-timezones"
                        placeholder="Africa/Addis_Ababa"
                    />
                    <datalist id="program-timezones">
                        <option value="Africa/Addis_Ababa" />
                        <option value="Africa/Nairobi" />
                        <option value="UTC" />
                    </datalist>
                    <InputError :message="form.errors.timezone" />
                </div>

                <div class="grid gap-2">
                    <Label for="opens_at">Opens at</Label>
                    <Input
                        id="opens_at"
                        v-model="form.opens_at"
                        type="datetime-local"
                    />
                    <InputError :message="form.errors.opens_at" />
                </div>

                <div class="grid gap-2">
                    <Label for="closes_at">Closes at</Label>
                    <Input
                        id="closes_at"
                        v-model="form.closes_at"
                        type="datetime-local"
                    />
                    <InputError :message="form.errors.closes_at" />
                </div>
            </div>
        </FormSection>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
            <p
                v-if="form.hasErrors"
                class="text-sm text-destructive sm:mr-auto"
            >
                Review the highlighted fields and try again.
            </p>
            <Button
                type="submit"
                :disabled="form.processing || (program !== undefined && !form.isDirty)"
            >
                <Save class="size-4" />
                {{ program ? 'Save program' : 'Create program' }}
            </Button>
        </div>
    </form>
</template>
