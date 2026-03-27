<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
    provider: string;
    class?: HTMLAttributes['class'];
}>();

const copy = computed(() => {
    return (
        {
            google: {
                glyph: 'G',
                classes:
                    'bg-white text-[#2563eb] ring-1 ring-slate-200 shadow-sm',
            },
            facebook: {
                glyph: 'f',
                classes: 'bg-[#1877f2] text-white',
            },
            github: {
                glyph: 'GH',
                classes: 'bg-slate-900 text-white',
            },
        }[props.provider] ?? {
            glyph: props.provider.slice(0, 1).toUpperCase(),
            classes: 'bg-slate-200 text-slate-700',
        }
    );
});
</script>

<template>
    <span
        :class="
            cn(
                'inline-flex h-6 min-w-6 items-center justify-center rounded-full px-1.5 text-[0.65rem] font-bold tracking-[0.16em]',
                copy.classes,
                props.class,
            )
        "
        aria-hidden="true"
    >
        {{ copy.glyph }}
    </span>
</template>
