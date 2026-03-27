<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div
        class="flex aspect-square size-9 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-950 via-slate-800 to-emerald-500 text-white shadow-lg shadow-emerald-500/15 dark:from-white dark:via-slate-200 dark:to-emerald-400 dark:text-slate-950"
    >
        <AppLogoIcon class="size-5 fill-current" />
    </div>
    <div class="ml-2 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold">
            CRM Lite
        </span>
        <span class="truncate text-xs text-slate-500 dark:text-slate-400">
            Freelancer workspace
        </span>
    </div>
</template>
