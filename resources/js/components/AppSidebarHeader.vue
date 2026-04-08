<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import FlashMessages from '@/components/crm/FlashMessages.vue';
import UserRoleBadge from '@/components/crm/UserRoleBadge.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem, User } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const page = usePage();
const user = computed(() => page.props.auth.user as User | null);
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

            <div v-if="user" class="ml-auto flex items-center gap-3">
                <UserRoleBadge
                    :role="user.role"
                    :label="user.role_label"
                    :show-prefix="true"
                />
            </div>
        </header>
        <div class="mx-auto w-full max-w-[84rem] px-4 pb-4 md:px-6 lg:px-8">
            <FlashMessages />
        </div>
    </div>
</template>
