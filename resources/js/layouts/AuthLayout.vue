<script setup lang="ts">
import FlashMessages from '@/components/crm/FlashMessages.vue';
import AuthLayout from '@/layouts/auth/AuthSplitLayout.vue';

const { title = '', description = '' } = defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <AuthLayout :title="title" :description="description">
        <div class="space-y-6">
            <FlashMessages />
            <slot />
        </div>
    </AuthLayout>
</template>
