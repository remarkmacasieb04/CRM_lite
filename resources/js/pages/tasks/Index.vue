<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { CheckSquare } from 'lucide-vue-next';
import { computed, reactive } from 'vue';
import EmptyState from '@/components/crm/EmptyState.vue';
import PageHeader from '@/components/crm/PageHeader.vue';
import UserRoleBadge from '@/components/crm/UserRoleBadge.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { formatDateTime } from '@/lib/formatters';
import { destroy, index, store, toggle, update } from '@/routes/tasks';
import type { TaskItem, TaskPriorityOption, TaskStatusOption } from '@/types';

const props = defineProps<{
    tasks: TaskItem[];
    filters: {
        status: string | null;
        priority: string | null;
        assigned_to: number | null;
        client_id: number | null;
    };
    statusOptions: TaskStatusOption[];
    priorityOptions: TaskPriorityOption[];
    clientOptions: Array<{ id: number; name: string }>;
    memberOptions: Array<{ id: number; name: string; email: string | null }>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Tasks',
                href: index(),
            },
        ],
    },
});

const filters = reactive({
    status: props.filters.status ?? '',
    priority: props.filters.priority ?? '',
    assigned_to: props.filters.assigned_to
        ? String(props.filters.assigned_to)
        : '',
    client_id: props.filters.client_id ? String(props.filters.client_id) : '',
});

const form = useForm({
    client_id: '',
    assigned_to_user_id: '',
    title: '',
    description: '',
    status: props.statusOptions[0]?.value ?? 'todo',
    priority: props.priorityOptions[1]?.value ?? 'medium',
    due_at: '',
});

const hasOpenTasks = computed(() =>
    props.tasks.some((task) => task.status !== 'done'),
);

const submitFilters = () => {
    router.get(
        index.url({
            query: {
                status: filters.status || undefined,
                priority: filters.priority || undefined,
                assigned_to: filters.assigned_to || undefined,
                client_id: filters.client_id || undefined,
            },
        }),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
};

watchDebounced(
    () => [
        filters.status,
        filters.priority,
        filters.assigned_to,
        filters.client_id,
    ],
    submitFilters,
    {
        debounce: 250,
        maxWait: 750,
    },
);

const createTask = () => {
    form.transform((data) => ({
        ...data,
        client_id: data.client_id || null,
        assigned_to_user_id: data.assigned_to_user_id || null,
        due_at: data.due_at || null,
    })).post(store.url(), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form.clearErrors();
            form.status = props.statusOptions[0]?.value ?? 'todo';
            form.priority = props.priorityOptions[1]?.value ?? 'medium';
        },
    });
};

const toggleTask = (taskId: number) => {
    router.patch(toggle.url(taskId), {}, { preserveScroll: true });
};

const deleteTask = (taskId: number, title: string) => {
    if (!window.confirm(`Delete "${title}"?`)) {
        return;
    }

    router.delete(destroy.url(taskId), {
        preserveScroll: true,
    });
};

const updateTaskStatus = (task: TaskItem, status: string) => {
    router.patch(
        update.url(task.id),
        {
            client_id: task.client.id,
            assigned_to_user_id: task.assignee.id,
            title: task.title,
            description: task.description,
            status,
            priority: task.priority,
            due_at: task.due_at ? task.due_at.slice(0, 10) : null,
        },
        { preserveScroll: true },
    );
};
</script>

<template>
    <Head title="Tasks" />

    <div class="crm-page">
        <PageHeader
            eyebrow="Tasks"
            title="Track the next action, not just the last note"
            description="Use tasks to turn client context into concrete work with priorities, due dates, and assignees."
        />

        <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <section class="crm-panel crm-panel-body space-y-5">
                <div>
                    <h2
                        class="text-lg font-semibold text-slate-950 dark:text-white"
                    >
                        Create a task
                    </h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Great for follow-ups, proposals, revisions, or internal
                        handoff work.
                    </p>
                </div>

                <form class="space-y-5" @submit.prevent="createTask">
                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="grid gap-2 md:col-span-2">
                            <Label for="task-title">Title</Label>
                            <Input
                                id="task-title"
                                v-model="form.title"
                                class="h-11"
                                placeholder="Send revised proposal"
                            />
                            <InputError :message="form.errors.title" />
                        </div>

                        <div class="grid gap-2 md:col-span-2">
                            <Label for="task-description">Description</Label>
                            <Textarea
                                id="task-description"
                                v-model="form.description"
                                rows="3"
                                placeholder="Include updated scope, timeline, and payment schedule."
                            />
                            <InputError :message="form.errors.description" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="task-client">Client</Label>
                            <select
                                id="task-client"
                                v-model="form.client_id"
                                class="crm-field"
                            >
                                <option value="">No client</option>
                                <option
                                    v-for="client in clientOptions"
                                    :key="client.id"
                                    :value="String(client.id)"
                                >
                                    {{ client.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.client_id" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="task-assignee">Assign to</Label>
                            <select
                                id="task-assignee"
                                v-model="form.assigned_to_user_id"
                                class="crm-field"
                            >
                                <option value="">Unassigned</option>
                                <option
                                    v-for="member in memberOptions"
                                    :key="member.id"
                                    :value="String(member.id)"
                                >
                                    {{ member.name }}
                                </option>
                            </select>
                            <InputError
                                :message="form.errors.assigned_to_user_id"
                            />
                        </div>

                        <div class="grid gap-2">
                            <Label for="task-status">Status</Label>
                            <select
                                id="task-status"
                                v-model="form.status"
                                class="crm-field"
                            >
                                <option
                                    v-for="option in statusOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                            <InputError :message="form.errors.status" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="task-priority">Priority</Label>
                            <select
                                id="task-priority"
                                v-model="form.priority"
                                class="crm-field"
                            >
                                <option
                                    v-for="option in priorityOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                            <InputError :message="form.errors.priority" />
                        </div>

                        <div class="grid gap-2 md:col-span-2">
                            <Label for="task-due-at">Due date</Label>
                            <Input
                                id="task-due-at"
                                v-model="form.due_at"
                                type="date"
                                class="h-11"
                            />
                            <InputError :message="form.errors.due_at" />
                        </div>
                    </div>

                    <Button :disabled="form.processing">Create task</Button>
                </form>
            </section>

            <section class="crm-panel crm-panel-body space-y-5">
                <div>
                    <h2
                        class="text-lg font-semibold text-slate-950 dark:text-white"
                    >
                        Filter tasks
                    </h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Narrow the list to one assignee, client, or work stage.
                    </p>
                </div>

                <div class="grid gap-4">
                    <div class="grid gap-2">
                        <Label for="filter-status">Status</Label>
                        <select
                            id="filter-status"
                            v-model="filters.status"
                            class="crm-field"
                        >
                            <option value="">All statuses</option>
                            <option
                                v-for="option in statusOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                    </div>

                    <div class="grid gap-2">
                        <Label for="filter-priority">Priority</Label>
                        <select
                            id="filter-priority"
                            v-model="filters.priority"
                            class="crm-field"
                        >
                            <option value="">All priorities</option>
                            <option
                                v-for="option in priorityOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                    </div>

                    <div class="grid gap-2">
                        <Label for="filter-assignee">Assignee</Label>
                        <select
                            id="filter-assignee"
                            v-model="filters.assigned_to"
                            class="crm-field"
                        >
                            <option value="">Anyone</option>
                            <option
                                v-for="member in memberOptions"
                                :key="member.id"
                                :value="String(member.id)"
                            >
                                {{ member.name }}
                            </option>
                        </select>
                    </div>

                    <div class="grid gap-2">
                        <Label for="filter-client">Client</Label>
                        <select
                            id="filter-client"
                            v-model="filters.client_id"
                            class="crm-field"
                        >
                            <option value="">Any client</option>
                            <option
                                v-for="client in clientOptions"
                                :key="client.id"
                                :value="String(client.id)"
                            >
                                {{ client.name }}
                            </option>
                        </select>
                    </div>
                </div>
            </section>
        </div>

        <section class="crm-panel overflow-hidden">
            <div
                class="border-b border-slate-200/70 px-5 py-5 sm:px-6 dark:border-slate-800"
            >
                <p class="font-semibold text-slate-950 dark:text-white">
                    {{ tasks.length }} task{{ tasks.length === 1 ? '' : 's' }}
                </p>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    {{
                        hasOpenTasks
                            ? 'Stay on top of open work across the current workspace.'
                            : 'You are caught up. New work will appear here as tasks are created.'
                    }}
                </p>
            </div>

            <div
                v-if="tasks.length > 0"
                class="divide-y divide-slate-200/70 dark:divide-slate-800"
            >
                <div
                    v-for="task in tasks"
                    :key="task.id"
                    class="px-5 py-5 sm:px-6"
                >
                    <div
                        class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(17rem,22rem)]"
                    >
                        <div class="min-w-0 space-y-3">
                            <div class="flex flex-wrap items-center gap-3">
                                <p
                                    class="text-lg font-semibold break-words text-slate-950 dark:text-white"
                                >
                                    {{ task.title }}
                                </p>
                                <UserRoleBadge
                                    :role="task.priority"
                                    :label="task.priority_label"
                                />
                                <UserRoleBadge
                                    :role="
                                        task.status === 'done'
                                            ? 'admin'
                                            : 'user'
                                    "
                                    :label="task.status_label"
                                />
                            </div>
                            <p
                                v-if="task.description"
                                class="text-sm leading-6 text-slate-600 dark:text-slate-300"
                            >
                                {{ task.description }}
                            </p>
                            <div
                                class="grid gap-2 text-sm text-slate-500 sm:grid-cols-2 dark:text-slate-400"
                            >
                                <span
                                    v-if="task.client.name"
                                    class="min-w-0 break-words"
                                >
                                    Client: {{ task.client.name }}
                                </span>
                                <span class="min-w-0 break-words">
                                    Assignee:
                                    {{ task.assignee.name || 'Unassigned' }}
                                </span>
                                <span class="min-w-0 break-words">
                                    Created by:
                                    {{ task.creator.name || 'Unknown' }}
                                </span>
                                <span
                                    >Due:
                                    {{
                                        formatDateTime(
                                            task.due_at,
                                            'Not scheduled',
                                        )
                                    }}</span
                                >
                            </div>
                        </div>

                        <div
                            class="flex flex-wrap items-center gap-2 xl:justify-end"
                        >
                            <Button
                                variant="outline"
                                size="sm"
                                class="justify-center"
                                @click="toggleTask(task.id)"
                            >
                                {{
                                    task.status === 'done'
                                        ? 'Reopen'
                                        : 'Mark done'
                                }}
                            </Button>

                            <select
                                class="crm-compact-select"
                                :value="task.status ?? 'todo'"
                                @change="
                                    updateTaskStatus(
                                        task,
                                        ($event.target as HTMLSelectElement)
                                            .value,
                                    )
                                "
                            >
                                <option
                                    v-for="option in statusOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>

                            <Button
                                variant="outline"
                                size="sm"
                                class="justify-center"
                                @click="deleteTask(task.id, task.title)"
                            >
                                Delete
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="p-6">
                <EmptyState
                    title="No tasks yet"
                    description="Create the first task so your workspace has a clear next action, not just client history."
                    :icon="CheckSquare"
                />
            </div>
        </section>
    </div>
</template>
