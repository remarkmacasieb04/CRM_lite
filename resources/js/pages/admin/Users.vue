<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { BellRing, MailCheck, ShieldCheck, UsersRound } from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';
import PageHeader from '@/components/crm/PageHeader.vue';
import StatCard from '@/components/crm/StatCard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import admin from '@/routes/admin';
import type { AdminUserListItem, RoleOption, User } from '@/types';

const props = defineProps<{
    users: AdminUserListItem[];
    roleOptions: RoleOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Admin',
                href: admin.users.index(),
            },
            {
                title: 'Users',
                href: admin.users.index(),
            },
        ],
    },
});

const page = usePage();
const authUser = computed(() => page.props.auth.user as User | null);
const savingUserId = ref<number | null>(null);
const selectedRoles = reactive<Record<number, string>>(
    Object.fromEntries(
        props.users.map((user) => [user.id, user.role ?? 'user']),
    ),
);

const adminCount = computed(
    () => props.users.filter((user) => user.role === 'admin').length,
);
const verifiedCount = computed(
    () => props.users.filter((user) => user.email_verified_at !== null).length,
);
const reminderCount = computed(
    () =>
        props.users.filter((user) => user.receives_follow_up_reminders).length,
);

const saveRole = (user: AdminUserListItem) => {
    if (selectedRoles[user.id] === (user.role ?? 'user')) {
        return;
    }

    savingUserId.value = user.id;

    router.patch(
        admin.users.update.url(user.id),
        {
            role: selectedRoles[user.id],
        },
        {
            preserveScroll: true,
            onFinish: () => {
                savingUserId.value = null;
            },
        },
    );
};
</script>

<template>
    <Head title="Admin users" />

    <div class="crm-page">
        <PageHeader
            eyebrow="Admin"
            title="Manage workspace access"
            description="Keep role changes simple and safe. Admins can manage who has elevated access without changing client ownership rules."
        />

        <div class="grid gap-4 lg:grid-cols-3">
            <StatCard
                title="Total users"
                :value="users.length"
                description="Everyone with an account in this CRM Lite install."
                :icon="UsersRound"
            />
            <StatCard
                title="Admins"
                :value="adminCount"
                description="Users who can manage roles and workspace access."
                :icon="ShieldCheck"
            />
            <StatCard
                title="Reminder-ready"
                :value="reminderCount"
                description="Verified users who can receive reminder emails."
                :icon="BellRing"
            />
        </div>

        <Card>
            <CardHeader class="space-y-2">
                <CardTitle class="text-xl">Users and roles</CardTitle>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    {{ verifiedCount }} verified account{{
                        verifiedCount === 1 ? '' : 's'
                    }}
                    can receive reminder digests. Role changes take effect
                    immediately after you save them.
                </p>
            </CardHeader>
            <CardContent class="space-y-4">
                <div
                    v-for="user in users"
                    :key="user.id"
                    class="grid gap-4 rounded-[1.5rem] border border-slate-200/70 bg-slate-50/70 p-5 lg:grid-cols-[minmax(0,1.35fr)_minmax(10rem,0.6fr)_minmax(9rem,0.45fr)_auto] lg:items-end dark:border-slate-800 dark:bg-slate-900/50"
                >
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-3">
                            <p
                                class="text-base font-semibold text-slate-950 dark:text-white"
                            >
                                {{ user.name }}
                            </p>
                            <span
                                v-if="authUser?.id === user.id"
                                class="rounded-full bg-slate-950 px-3 py-1 text-xs font-semibold text-white dark:bg-white dark:text-slate-950"
                            >
                                You
                            </span>
                            <span
                                v-if="user.role === 'admin'"
                                class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-100"
                            >
                                Admin
                            </span>
                        </div>
                        <div
                            class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-slate-500 dark:text-slate-400"
                        >
                            <span>{{ user.email }}</span>
                            <span
                                >{{ user.clients_count }} client{{
                                    user.clients_count === 1 ? '' : 's'
                                }}</span
                            >
                            <span>{{
                                user.email_verified_at
                                    ? 'Email verified'
                                    : 'Verification pending'
                            }}</span>
                            <span>{{
                                user.receives_follow_up_reminders
                                    ? 'Reminder emails on'
                                    : 'Reminder emails off'
                            }}</span>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label :for="`role-${user.id}`">Role</Label>
                        <select
                            :id="`role-${user.id}`"
                            v-model="selectedRoles[user.id]"
                            class="crm-field"
                        >
                            <option
                                v-for="option in roleOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                    </div>

                    <div class="grid gap-2">
                        <Label :for="`digest-${user.id}`">Last digest</Label>
                        <Input
                            :id="`digest-${user.id}`"
                            :model-value="
                                user.last_follow_up_digest_sent_at ??
                                'Not sent yet'
                            "
                            readonly
                            class="h-11"
                        />
                    </div>

                    <div class="flex items-center gap-2">
                        <Button
                            :disabled="
                                savingUserId === user.id ||
                                selectedRoles[user.id] === (user.role ?? 'user')
                            "
                            @click="saveRole(user)"
                        >
                            Save role
                        </Button>
                    </div>
                </div>

                <div
                    class="rounded-[1.4rem] border border-dashed border-slate-300/80 bg-slate-50/80 px-5 py-4 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-400"
                >
                    <div class="flex items-start gap-3">
                        <MailCheck class="mt-0.5 size-4 shrink-0" />
                        <p>
                            Admin access only controls workspace management.
                            Users still only see their own clients, notes,
                            attachments, and activity history.
                        </p>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
