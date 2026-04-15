<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import UserRoleBadge from '@/components/crm/UserRoleBadge.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import workspaceRoutes, { edit, store } from '@/routes/workspace';
import members from '@/routes/workspace/members';
import type { CurrentWorkspace, User, WorkspaceOption } from '@/types';

type WorkspaceMember = {
    id: number;
    name: string;
    email: string;
    role: string | null;
    role_label: string | null;
    is_current_user: boolean;
};

type RoleOption = {
    value: string;
    label: string;
};

const props = defineProps<{
    workspace: {
        id: number;
        name: string;
        slug: string;
        is_personal: boolean;
        owner: {
            id: number | null;
            name: string | null;
            email: string | null;
        };
        members: WorkspaceMember[];
    };
    memberRoleOptions: RoleOption[];
    canManageMembers: boolean;
    canCreateWorkspace: boolean;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Workspace',
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user as User | null);
const currentWorkspace = computed(
    () => page.props.currentWorkspace as CurrentWorkspace | null,
);
const availableWorkspaces = computed(
    () => (page.props.availableWorkspaces ?? []) as WorkspaceOption[],
);

const memberForm = useForm({
    email: '',
    role: props.memberRoleOptions[0]?.value ?? 'member',
});
const workspaceForm = useForm({
    name: '',
});

const submitMember = () => {
    memberForm.post(members.store.url(), {
        preserveScroll: true,
        onSuccess: () => {
            memberForm.reset('email');
            memberForm.clearErrors();
        },
    });
};

const submitWorkspace = () => {
    workspaceForm.post(store.url(), {
        preserveScroll: true,
        onSuccess: () => {
            workspaceForm.reset();
            workspaceForm.clearErrors();
        },
    });
};
</script>

<template>
    <Head title="Workspace" />

    <div class="space-y-8">
        <section class="space-y-5">
            <Heading
                variant="small"
                title="Workspace"
                description="Manage the active workspace, member access, and switch between workspaces you belong to."
            />

            <Card>
                <CardHeader>
                    <CardTitle class="text-xl">Current workspace</CardTitle>
                </CardHeader>
                <CardContent class="space-y-5">
                    <div
                        class="flex flex-col gap-4 rounded-[1.5rem] border border-slate-200/80 bg-slate-50/80 p-5 sm:flex-row sm:items-start sm:justify-between dark:border-slate-800 dark:bg-slate-900/50"
                    >
                        <div class="min-w-0 space-y-2">
                            <p
                                class="text-2xl font-semibold tracking-tight break-words text-slate-950 dark:text-white"
                            >
                                {{ workspace.name }}
                            </p>
                            <p
                                class="text-sm break-words text-slate-500 dark:text-slate-400"
                            >
                                Slug: {{ workspace.slug }}
                            </p>
                        </div>

                        <UserRoleBadge
                            :role="currentWorkspace?.role"
                            :label="currentWorkspace?.role_label"
                            :show-prefix="true"
                        />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="crm-subtle-panel min-w-0">
                            <p
                                class="text-sm font-medium text-slate-500 dark:text-slate-400"
                            >
                                Owner
                            </p>
                            <p
                                class="mt-2 font-semibold break-words text-slate-950 dark:text-white"
                            >
                                {{ workspace.owner.name || 'Unknown' }}
                            </p>
                            <p
                                class="text-sm break-words text-slate-500 dark:text-slate-400"
                            >
                                {{ workspace.owner.email || 'No email' }}
                            </p>
                        </div>

                        <div class="crm-subtle-panel min-w-0">
                            <p
                                class="text-sm font-medium text-slate-500 dark:text-slate-400"
                            >
                                Members
                            </p>
                            <p
                                class="mt-2 font-semibold text-slate-950 dark:text-white"
                            >
                                {{ workspace.members.length }}
                            </p>
                            <p
                                class="text-sm text-slate-500 dark:text-slate-400"
                            >
                                {{
                                    workspace.is_personal
                                        ? 'Personal workspace'
                                        : 'Shared workspace'
                                }}
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="grid gap-5">
                <Card>
                    <CardHeader>
                        <CardTitle class="text-xl">Switch workspace</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div
                            v-for="item in availableWorkspaces"
                            :key="item.id"
                            class="crm-list-item grid gap-4 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center"
                        >
                            <div class="min-w-0">
                                <p
                                    class="font-semibold break-words text-slate-950 dark:text-white"
                                >
                                    {{ item.name }}
                                </p>
                                <p
                                    class="text-sm break-words text-slate-500 dark:text-slate-400"
                                >
                                    {{ item.slug }}
                                </p>
                            </div>

                            <div
                                class="flex flex-wrap items-center gap-2 sm:justify-end"
                            >
                                <UserRoleBadge
                                    :role="item.role"
                                    :label="item.role ?? 'member'"
                                />
                                <Button
                                    v-if="currentWorkspace?.id !== item.id"
                                    variant="outline"
                                    as-child
                                >
                                    <Link
                                        :href="workspaceRoutes.switch(item.id)"
                                        method="patch"
                                    >
                                        Switch
                                    </Link>
                                </Button>
                                <span
                                    v-else
                                    class="text-sm font-medium text-emerald-700 dark:text-emerald-200"
                                >
                                    Active
                                </span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card v-if="canCreateWorkspace">
                    <CardHeader>
                        <CardTitle class="text-xl">Create workspace</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Create a new shared workspace when you want a
                            separate client base for another brand, team, or
                            business unit.
                        </p>
                        <form
                            class="space-y-3"
                            @submit.prevent="submitWorkspace"
                        >
                            <div class="grid gap-2">
                                <Label for="workspace-name"
                                    >Workspace name</Label
                                >
                                <Input
                                    id="workspace-name"
                                    v-model="workspaceForm.name"
                                    class="h-11 rounded-xl"
                                    placeholder="Northwind Studio"
                                />
                                <InputError
                                    :message="workspaceForm.errors.name"
                                />
                            </div>

                            <div
                                class="flex flex-col gap-3 sm:flex-row sm:items-center"
                            >
                                <Button :disabled="workspaceForm.processing">
                                    Create workspace
                                </Button>
                                <p
                                    class="text-sm leading-6 text-slate-500 dark:text-slate-400"
                                >
                                    You will switch into it right away.
                                </p>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </section>

        <section class="space-y-6">
            <Heading
                variant="small"
                title="Members"
                description="Anyone added here can access the active workspace and see its shared CRM records."
            />

            <Card>
                <CardContent class="space-y-4 pt-6">
                    <div
                        v-for="member in workspace.members"
                        :key="member.id"
                        class="crm-list-item grid gap-3 md:grid-cols-[minmax(0,1fr)_auto] md:items-center"
                    >
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p
                                    class="font-semibold break-words text-slate-950 dark:text-white"
                                >
                                    {{ member.name }}
                                </p>
                                <span
                                    v-if="member.is_current_user"
                                    class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                >
                                    You
                                </span>
                            </div>
                            <p
                                class="text-sm break-all text-slate-500 dark:text-slate-400"
                            >
                                {{ member.email }}
                            </p>
                        </div>

                        <UserRoleBadge
                            :role="member.role"
                            :label="member.role_label"
                        />
                    </div>
                </CardContent>
            </Card>
        </section>

        <section v-if="canManageMembers" class="space-y-6">
            <Heading
                variant="small"
                title="Add an existing user"
                description="Invite someone who already has an account in this CRM Lite install into the current workspace."
            />

            <form
                class="crm-panel crm-panel-body space-y-5"
                @submit.prevent="submitMember"
            >
                <div class="grid gap-5 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="workspace-member-email">User email</Label>
                        <Input
                            id="workspace-member-email"
                            v-model="memberForm.email"
                            class="h-11 rounded-xl"
                            type="email"
                            placeholder="user@example.com"
                        />
                        <InputError :message="memberForm.errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="workspace-member-role"
                            >Workspace role</Label
                        >
                        <select
                            id="workspace-member-role"
                            v-model="memberForm.role"
                            class="crm-field"
                        >
                            <option
                                v-for="option in memberRoleOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                        <InputError :message="memberForm.errors.role" />
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <Button :disabled="memberForm.processing">
                        Add to workspace
                    </Button>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Members can switch into this workspace after they are
                        added.
                    </p>
                </div>
            </form>
        </section>
    </div>
</template>
