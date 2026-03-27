<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { NavItem } from '@/types';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profile',
        href: editProfile(),
    },
    {
        title: 'Security',
        href: editSecurity(),
    },
    {
        title: 'Appearance',
        href: editAppearance(),
    },
];

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <div
        class="mx-auto flex w-full max-w-[84rem] flex-col gap-8 px-4 py-6 md:px-6 lg:px-8"
    >
        <Heading
            title="Account"
            description="Manage your profile, connected providers, security, and appearance."
        />

        <div class="flex flex-col gap-8 lg:flex-row lg:items-start lg:gap-12">
            <aside class="w-full lg:max-w-[220px]">
                <nav
                    class="crm-panel crm-panel-body flex flex-col space-y-1 space-x-0"
                    aria-label="Settings"
                >
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="toUrl(item.href)"
                        variant="ghost"
                        :class="[
                            'h-auto w-full justify-start rounded-2xl px-4 py-3 text-left',
                            {
                                'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-100':
                                    isCurrentOrParentUrl(item.href),
                            },
                        ]"
                        as-child
                    >
                        <Link :href="item.href">
                            <component :is="item.icon" class="h-4 w-4" />
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div class="min-w-0 flex-1 md:max-w-3xl">
                <section class="max-w-3xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
