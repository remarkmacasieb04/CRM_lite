<script setup lang="ts">
import type { LucideIcon } from 'lucide-vue-next';

defineProps<{
    title: string;
    description: string;
    icon: LucideIcon;
}>();
</script>

<template>
    <div class="crm-subtle-panel border-dashed px-6 py-12 text-center">
        <div
            class="mx-auto mb-4 inline-flex size-14 items-center justify-center rounded-2xl bg-white text-slate-700 shadow-sm dark:bg-slate-950 dark:text-slate-200"
        >
            <component :is="icon" class="size-6" />
        </div>
        <h3 class="text-lg font-semibold text-slate-950 dark:text-white">
            {{ title }}
        </h3>
        <p
            class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
        >
            {{ description }}
        </p>
        <div class="mt-6">
            <slot />
        </div>
    </div>
</template>
