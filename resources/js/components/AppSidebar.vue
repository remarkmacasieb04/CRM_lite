<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, Settings, ShieldCheck, UsersRound } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import admin from '@/routes/admin';
import { index as clientsIndex } from '@/routes/clients';
import { edit as editProfile } from '@/routes/profile';
import type { NavItem, User } from '@/types';

const page = usePage();

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
        {
            title: 'Clients',
            href: clientsIndex(),
            icon: UsersRound,
        },
    ];
    const user = page.props.auth.user as User | null;

    if (user?.can_access_admin) {
        items.push({
            title: 'Admin',
            href: admin.users.index(),
            icon: ShieldCheck,
        });
    }

    items.push({
        title: 'Account',
        href: editProfile(),
        icon: Settings,
    });

    return items;
});
</script>

<template>
    <Sidebar
        collapsible="icon"
        variant="inset"
        class="border-r border-slate-200/70 bg-[radial-gradient(circle_at_top,_rgba(16,185,129,0.08),_transparent_35%),linear-gradient(180deg,_rgba(255,255,255,0.98),_rgba(248,250,252,0.98))] shadow-[18px_0_40px_-36px_rgba(15,23,42,0.2)] dark:border-slate-800 dark:bg-[radial-gradient(circle_at_top,_rgba(16,185,129,0.12),_transparent_35%),linear-gradient(180deg,_rgba(2,6,23,0.96),_rgba(15,23,42,0.98))] dark:shadow-[18px_0_40px_-36px_rgba(2,6,23,0.9)]"
    >
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>

            <div
                class="px-3 pt-3 text-xs leading-5 text-slate-500 group-data-[collapsible=icon]:hidden dark:text-slate-400"
            >
                Stay on top of leads, follow-ups, and account settings without
                losing context.
            </div>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
