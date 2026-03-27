<script setup lang="ts">
import { CheckCircle2, CircleAlert } from 'lucide-vue-next';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import type { FlashMessages } from '@/types';

const page = usePage();

const flash = computed(() => (page.props.flash ?? {}) as FlashMessages);
</script>

<template>
    <div
        v-if="flash.success || flash.error"
        class="space-y-3"
        role="status"
        aria-live="polite"
    >
        <Alert
            v-if="flash.success"
            class="border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-500/25 dark:bg-emerald-500/10 dark:text-emerald-100"
        >
            <CheckCircle2 class="size-4" />
            <AlertTitle>Success</AlertTitle>
            <AlertDescription>{{ flash.success }}</AlertDescription>
        </Alert>

        <Alert
            v-if="flash.error"
            variant="destructive"
            class="border-red-200 bg-red-50 text-red-900 dark:border-red-500/25 dark:bg-red-500/10 dark:text-red-100"
        >
            <CircleAlert class="size-4" />
            <AlertTitle>Something needs attention</AlertTitle>
            <AlertDescription>{{ flash.error }}</AlertDescription>
        </Alert>
    </div>
</template>
