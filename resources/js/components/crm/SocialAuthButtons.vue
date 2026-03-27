<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import SocialProviderIcon from '@/components/crm/SocialProviderIcon.vue';
import type { SocialProviderOption } from '@/types';

type Props = {
    compact?: boolean;
    prefix?: string;
};

const props = withDefaults(defineProps<Props>(), {
    compact: false,
    prefix: 'Continue with',
});

const page = usePage();
const socialProviders = computed(
    () => (page.props.socialProviders ?? []) as SocialProviderOption[],
);
</script>

<template>
    <div v-if="socialProviders.length > 0" class="space-y-3">
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <span
                    class="w-full border-t border-slate-200/80 dark:border-slate-800"
                />
            </div>
            <div
                class="relative flex justify-center text-xs tracking-[0.28em] text-slate-500 uppercase"
            >
                <span class="bg-white px-4 dark:bg-slate-950">
                    Or sign in with
                </span>
            </div>
        </div>

        <div :class="compact ? 'grid gap-2' : 'grid gap-3 sm:grid-cols-3'">
            <Link
                v-for="provider in socialProviders"
                :key="provider.name"
                :href="provider.href"
                class="inline-flex items-center justify-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:text-slate-950 hover:shadow-lg hover:shadow-emerald-100 focus-visible:ring-2 focus-visible:ring-emerald-500/40 focus-visible:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:hover:border-emerald-500/60 dark:hover:shadow-none"
            >
                <SocialProviderIcon :provider="provider.name" />
                <span>
                    {{
                        compact ? `${prefix} ${provider.label}` : provider.label
                    }}
                </span>
            </Link>
        </div>
    </div>
</template>
