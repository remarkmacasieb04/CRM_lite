<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
    alt?: string;
    src?: string;
};

withDefaults(defineProps<Props>(), {
    alt: 'GreenTech Clientflow logo',
    src: '/CRM-lite-logo.svg',
});
</script>

<template>
    <img
        :src="src"
        :alt="alt"
        class="block shrink-0 object-contain"
        :class="className"
        v-bind="$attrs"
    />
</template>
