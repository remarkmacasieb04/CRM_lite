<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { useVModel } from '@vueuse/core';
import { cn } from '@/lib/utils';

const props = defineProps<{
    defaultValue?: string;
    modelValue?: string;
    class?: HTMLAttributes['class'];
}>();

const emits = defineEmits<{
    (e: 'update:modelValue', payload: string): void;
}>();

const modelValue = useVModel(props, 'modelValue', emits, {
    passive: true,
    defaultValue: props.defaultValue ?? '',
});
</script>

<template>
    <textarea
        v-model="modelValue"
        data-slot="textarea"
        :class="
            cn(
                'flex min-h-28 w-full rounded-xl border border-input bg-white/75 px-3.5 py-3 text-sm shadow-sm outline-none transition-[color,box-shadow,border-color,background-color] placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 dark:bg-input/45',
                props.class,
            )
        "
    />
</template>
