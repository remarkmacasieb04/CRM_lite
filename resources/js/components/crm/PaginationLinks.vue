<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { PaginationLink } from '@/types';
import { cn } from '@/lib/utils';

defineProps<{
    links: PaginationLink[];
}>();
</script>

<template>
    <nav
        v-if="links.length > 3"
        class="flex flex-wrap items-center gap-2"
        aria-label="Pagination"
    >
        <component
            :is="link.url ? Link : 'span'"
            v-for="link in links"
            :key="`${link.label}-${link.url}`"
            :href="link.url ?? undefined"
            preserve-scroll
            preserve-state
            :class="
                cn(
                    'inline-flex min-h-10 min-w-10 items-center justify-center rounded-xl border px-3 text-sm font-medium transition',
                    link.active
                        ? 'border-emerald-400 bg-emerald-50 text-emerald-700 dark:border-emerald-500/60 dark:bg-emerald-500/10 dark:text-emerald-100'
                        : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:text-slate-950 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-200 dark:hover:border-slate-700',
                    !link.url && 'cursor-not-allowed opacity-50',
                )
            "
        >
            <span v-html="link.label" />
        </component>
    </nav>
</template>
