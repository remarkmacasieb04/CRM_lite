<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        role?: string | null;
        label?: string | null;
        showPrefix?: boolean;
    }>(),
    {
        role: null,
        label: null,
        showPrefix: false,
    },
);

const badgeClasses = computed(() =>
    props.role === 'admin'
        ? 'border-emerald-200/80 bg-emerald-50 text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-100'
        : 'border-slate-200/80 bg-slate-100 text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200',
);

const resolvedLabel = computed(() => props.label ?? 'User');
</script>

<template>
    <span
        class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold tracking-[0.14em] uppercase"
        :class="badgeClasses"
    >
        <span v-if="showPrefix" class="mr-1.5 opacity-70">Role</span>
        {{ resolvedLabel }}
    </span>
</template>
