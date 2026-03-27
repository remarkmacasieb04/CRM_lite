<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import FlashMessages from '@/components/crm/FlashMessages.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);
</script>

<template>
    <div
        class="sticky top-0 z-20 border-b border-slate-200/70 bg-white/88 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-950/82"
    >
        <header
            class="mx-auto flex min-h-[4.5rem] w-full max-w-[84rem] shrink-0 items-center gap-2 px-4 py-4 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-6 lg:px-8"
        >
            <div class="flex items-center gap-2">
                <SidebarTrigger class="-ml-1" />
                <template v-if="breadcrumbs && breadcrumbs.length > 0">
                    <Breadcrumbs :breadcrumbs="breadcrumbs" />
                </template>
            </div>
        </header>
        <div class="mx-auto w-full max-w-[84rem] px-4 pb-4 md:px-6 lg:px-8">
            <FlashMessages />
        </div>
    </div>
</template>
