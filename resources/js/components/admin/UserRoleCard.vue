<script setup lang="ts">
import { computed } from 'vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import type { RoleOption } from '@/types';

type Props = {
    roles: RoleOption[];
    disabled?: boolean;
};

withDefaults(defineProps<Props>(), {
    disabled: false,
});

const selectedRoles = defineModel<string[]>('selectedRoles', {
    required: true,
});
const assignedRoles = computed(() => new Set(selectedRoles.value));

const toggleRole = (role: string, checked: boolean | 'indeterminate'): void => {
    const nextRoles = new Set(selectedRoles.value);

    if (checked === true) {
        nextRoles.add(role);
    } else {
        nextRoles.delete(role);
    }

    selectedRoles.value = [...nextRoles].sort((left, right) =>
        left.localeCompare(right),
    );
};
</script>

<template>
    <section
        class="rounded-[1.5rem] border border-border/70 bg-card/85 p-5 shadow-sm backdrop-blur"
    >
        <div class="space-y-2">
            <h2 class="text-lg font-semibold text-foreground">
                Assigned roles
            </h2>
            <p class="max-w-3xl text-sm leading-6 text-muted-foreground">
                Users inherit permissions from every checked role. Keep the role
                set small and intentional.
            </p>
        </div>

        <div class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            <label
                v-for="role in roles"
                :key="role.name"
                class="flex gap-3 rounded-2xl border border-border/70 bg-background/70 px-4 py-3 transition-colors hover:border-border hover:bg-accent/35"
            >
                <Checkbox
                    :checked="assignedRoles.has(role.name)"
                    :disabled="disabled"
                    @update:checked="toggleRole(role.name, $event)"
                />

                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <Label class="text-sm font-medium text-foreground">
                            {{ role.label }}
                        </Label>
                        <code
                            class="rounded bg-muted px-1.5 py-0.5 text-[11px] text-muted-foreground"
                        >
                            {{ role.usersCount }} users
                        </code>
                    </div>
                    <p class="mt-1 text-sm leading-5 text-muted-foreground">
                        {{
                            role.description ||
                            'Users with this role inherit the permissions currently attached to it.'
                        }}
                    </p>
                </div>
            </label>
        </div>
    </section>
</template>
