<script setup lang="ts">
import { computed } from 'vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import type { PermissionGroup } from '@/types';

type Props = {
    permissionGroups: PermissionGroup[];
    disabled?: boolean;
};

withDefaults(defineProps<Props>(), {
    disabled: false,
});

const permissions = defineModel<string[]>('permissions', { required: true });

const assignedPermissions = computed(() => new Set(permissions.value));

const togglePermission = (
    permission: string,
    checked: boolean | 'indeterminate',
): void => {
    const nextPermissions = new Set(permissions.value);

    if (checked === true) {
        nextPermissions.add(permission);
    } else {
        nextPermissions.delete(permission);
    }

    permissions.value = [...nextPermissions].sort((left, right) =>
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
                Permission access
            </h2>
            <p class="max-w-3xl text-sm leading-6 text-muted-foreground">
                Every checked permission should control a route, a sidebar item,
                or a future action like create, edit, print, or export.
            </p>
        </div>

        <div class="mt-6 grid gap-4 xl:grid-cols-2">
            <section
                v-for="group in permissionGroups"
                :key="group.key"
                class="rounded-2xl border border-border/70 bg-background/70 p-4"
            >
                <div class="mb-4">
                    <h3
                        class="text-sm font-semibold tracking-wide text-foreground uppercase"
                    >
                        {{ group.title }}
                    </h3>
                </div>

                <div class="space-y-3">
                    <label
                        v-for="permission in group.permissions"
                        :key="permission.name"
                        class="flex gap-3 rounded-xl border border-transparent px-3 py-3 transition-colors hover:border-border/80 hover:bg-accent/40"
                    >
                        <Checkbox
                            :checked="assignedPermissions.has(permission.name)"
                            :disabled="disabled"
                            @update:checked="
                                togglePermission(permission.name, $event)
                            "
                        />
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <Label
                                    class="text-sm font-medium text-foreground"
                                >
                                    {{ permission.label }}
                                </Label>
                                <code
                                    class="rounded bg-muted px-1.5 py-0.5 text-[11px] text-muted-foreground"
                                >
                                    {{ permission.name }}
                                </code>
                            </div>
                            <p
                                class="mt-1 text-sm leading-5 text-muted-foreground"
                            >
                                {{ permission.description }}
                            </p>
                        </div>
                    </label>
                </div>
            </section>
        </div>
    </section>
</template>
