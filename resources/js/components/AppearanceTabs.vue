<script setup lang="ts">
import { Monitor, Moon, Sun } from 'lucide-vue-next';
import { useAppearance } from '@/composables/useAppearance';

const { appearance, updateAppearance } = useAppearance();

const tabs = [
    { value: 'light', Icon: Sun, label: 'Light' },
    { value: 'dark', Icon: Moon, label: 'Dark' },
    { value: 'system', Icon: Monitor, label: 'System' },
] as const;
</script>

<template>
    <div
        class="crm-panel crm-panel-body inline-flex gap-1 rounded-[1.4rem] bg-white/80 p-1.5 dark:bg-slate-950/80"
    >
        <button
            v-for="{ value, Icon, label } in tabs"
            :key="value"
            @click="updateAppearance(value)"
            :class="[
                'flex items-center rounded-xl px-4 py-2 text-sm font-medium transition-colors',
                appearance === value
                    ? 'bg-white text-slate-950 shadow-sm dark:bg-slate-900 dark:text-neutral-100'
                    : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-slate-900/70',
            ]"
        >
            <component :is="Icon" class="-ml-1 h-4 w-4" />
            <span class="ml-1.5">{{ label }}</span>
        </button>
    </div>
</template>
