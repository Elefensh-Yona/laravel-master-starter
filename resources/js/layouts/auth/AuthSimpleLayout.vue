<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

const page = usePage();
const appName = (page.props.name as string) ?? 'Laravel Boilerplate';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative flex min-h-svh flex-col items-center justify-center overflow-hidden bg-[radial-gradient(circle_at_top_left,#fff0c2_0%,#f7f2e8_34%,#efe6d6_68%,#e7dcc9_100%)] p-6 md:p-10"
    >
        <div class="absolute inset-0 -z-10 overflow-hidden">
            <div
                class="absolute top-[-8rem] left-[-5rem] h-72 w-72 rounded-full bg-amber-300/35 blur-3xl"
            />
            <div
                class="absolute top-16 right-[-6rem] h-80 w-80 rounded-full bg-emerald-300/20 blur-3xl"
            />
            <div
                class="absolute bottom-[-9rem] left-1/3 h-72 w-72 rounded-full bg-rose-300/20 blur-3xl"
            />
        </div>

        <div class="w-full max-w-md">
            <div
                class="rounded-[2rem] border border-stone-900/10 bg-white/80 p-8 shadow-[0_30px_90px_rgba(68,37,10,0.10)] backdrop-blur lg:p-10"
            >
                <div class="flex flex-col gap-8">
                    <div class="flex flex-col items-center gap-4">
                        <Link
                            :href="home()"
                            class="flex flex-col items-center gap-3 font-medium text-stone-900"
                        >
                            <div
                                class="flex size-12 items-center justify-center rounded-2xl bg-stone-900 text-stone-50 shadow-sm"
                            >
                                <AppLogoIcon class="size-7 fill-current" />
                            </div>
                            <div class="space-y-1 text-center">
                                <div
                                    class="text-[0.68rem] font-semibold tracking-[0.22em] text-stone-500 uppercase"
                                >
                                    Secure access
                                </div>
                                <div class="text-sm font-medium">
                                    {{ appName }}
                                </div>
                            </div>
                        </Link>

                        <div class="space-y-2 text-center">
                            <h1
                                class="text-2xl font-semibold tracking-tight text-stone-950"
                            >
                                {{ title }}
                            </h1>
                            <p class="text-sm leading-6 text-stone-600">
                                {{ description }}
                            </p>
                        </div>
                    </div>

                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
