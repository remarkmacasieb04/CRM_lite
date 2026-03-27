<script setup lang="ts">
type Props = {
    title: string;
    description?: string;
    variant?: 'default' | 'small';
};

withDefaults(defineProps<Props>(), {
    variant: 'default',
});
</script>

<template>
    <header :class="variant === 'small' ? 'space-y-1.5' : 'mb-8 space-y-2'">
        <h2
            :class="
                variant === 'small'
                    ? 'text-lg font-semibold tracking-tight text-slate-950 dark:text-white'
                    : 'text-2xl font-semibold tracking-tight text-slate-950 dark:text-white'
            "
        >
            {{ title }}
        </h2>
        <p
            v-if="description"
            class="max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400"
        >
            {{ description }}
        </p>
    </header>
</template>
