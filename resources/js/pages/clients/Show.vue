<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    Archive,
    BadgeCheck,
    CalendarClock,
    CheckSquare,
    ClipboardCopy,
    Download,
    FileText,
    Globe,
    Mail,
    MessageCircleMore,
    MessageSquareText,
    Paperclip,
    Phone,
    ReceiptText,
    RotateCcw,
    Trash2,
    Upload,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { ref } from 'vue';
import DangerConfirmDialog from '@/components/crm/DangerConfirmDialog.vue';
import EmptyState from '@/components/crm/EmptyState.vue';
import PageHeader from '@/components/crm/PageHeader.vue';
import StatusBadge from '@/components/crm/StatusBadge.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import {
    formatCurrency,
    formatDate,
    formatDateTime,
    formatFileSize,
    formatRelativeTime,
} from '@/lib/formatters';
import {
    archive,
    contacted as markContacted,
    destroy,
    edit,
    index,
    restore,
} from '@/routes/clients';
import attachments from '@/routes/clients/attachments';
import communications from '@/routes/clients/communications';
import documents from '@/routes/clients/documents';
import notes from '@/routes/clients/notes';
import portalShare from '@/routes/clients/portal-share';
import { destroy as destroyTask, store as storeTask, toggle as toggleTask } from '@/routes/tasks';
import type {
    ClientDetail,
    CommunicationChannelOption,
    CommunicationDirectionOption,
    DocumentStatusOption,
    DocumentTypeOption,
    TaskPriorityOption,
    TaskStatusOption,
} from '@/types';

const props = defineProps<{
    client: ClientDetail;
    taskStatusOptions: TaskStatusOption[];
    taskPriorityOptions: TaskPriorityOption[];
    communicationChannelOptions: CommunicationChannelOption[];
    communicationDirectionOptions: CommunicationDirectionOption[];
    documentTypeOptions: DocumentTypeOption[];
    documentStatusOptions: DocumentStatusOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Clients',
                href: index(),
            },
            {
                title: 'Client details',
                href: index(),
            },
        ],
    },
});

const noteForm = useForm({
    content: '',
});
const attachmentForm = useForm<{
    file: File | null;
}>({
    file: null,
});
const taskForm = useForm({
    client_id: String(props.client.id),
    assigned_to_user_id: '',
    title: '',
    description: '',
    status: props.taskStatusOptions[0]?.value ?? 'todo',
    priority: props.taskPriorityOptions[1]?.value ?? 'medium',
    due_at: '',
});
const communicationForm = useForm({
    channel: props.communicationChannelOptions[0]?.value ?? 'email',
    direction: props.communicationDirectionOptions[0]?.value ?? 'outbound',
    subject: '',
    summary: '',
    happened_at: '',
});
const documentForm = useForm({
    type: props.documentTypeOptions[0]?.value ?? 'proposal',
    title: '',
    status: props.documentStatusOptions[0]?.value ?? 'draft',
    amount: '',
    currency: 'USD',
    issued_at: '',
    due_at: '',
    notes: '',
    is_portal_visible: true,
});

const archiveForm = useForm({});
const restoreForm = useForm({});
const deleteForm = useForm({});
const contactForm = useForm({});
const attachmentDeleteForm = useForm({});
const portalShareForm = useForm({});
const portalRevokeForm = useForm({});
const documentDeleteForm = useForm({});
const taskDeleteForm = useForm({});

const isArchived = computed(() => props.client.archived_at !== null);
const clientDeleteDialogOpen = ref(false);
const portalRevokeDialogOpen = ref(false);
const pendingAttachment = ref<{
    id: number;
    originalName: string;
} | null>(null);
const pendingTask = ref<{
    id: number;
    title: string;
} | null>(null);
const pendingDocument = ref<{
    id: number;
    documentNumber: string;
} | null>(null);
const attachmentDeleteDialogOpen = computed({
    get: () => pendingAttachment.value !== null,
    set: (open: boolean) => {
        if (!open) {
            pendingAttachment.value = null;
        }
    },
});
const taskDeleteDialogOpen = computed({
    get: () => pendingTask.value !== null,
    set: (open: boolean) => {
        if (!open) {
            pendingTask.value = null;
        }
    },
});
const documentDeleteDialogOpen = computed({
    get: () => pendingDocument.value !== null,
    set: (open: boolean) => {
        if (!open) {
            pendingDocument.value = null;
        }
    },
});

const submitNote = () => {
    noteForm.post(notes.store.url(props.client.id), {
        preserveScroll: true,
        onSuccess: () => noteForm.reset(),
    });
};

const submitAttachment = () => {
    attachmentForm.post(attachments.store.url(props.client.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            attachmentForm.reset();
            attachmentForm.clearErrors();
        },
    });
};

const submitTask = () => {
    taskForm.transform((data) => ({
        ...data,
        assigned_to_user_id: data.assigned_to_user_id || null,
        due_at: data.due_at || null,
    })).post(storeTask.url(), {
        preserveScroll: true,
        onSuccess: () => {
            taskForm.reset();
            taskForm.client_id = String(props.client.id);
            taskForm.status = props.taskStatusOptions[0]?.value ?? 'todo';
            taskForm.priority = props.taskPriorityOptions[1]?.value ?? 'medium';
        },
    });
};

const submitCommunication = () => {
    communicationForm.post(communications.store.url(props.client.id), {
        preserveScroll: true,
        onSuccess: () => {
            communicationForm.reset();
            communicationForm.channel = props.communicationChannelOptions[0]?.value ?? 'email';
            communicationForm.direction = props.communicationDirectionOptions[0]?.value ?? 'outbound';
        },
    });
};

const submitDocument = () => {
    documentForm.transform((data) => ({
        ...data,
        amount: data.amount || null,
        issued_at: data.issued_at || null,
        due_at: data.due_at || null,
    })).post(documents.store.url(props.client.id), {
        preserveScroll: true,
        onSuccess: () => {
            documentForm.reset();
            documentForm.type = props.documentTypeOptions[0]?.value ?? 'proposal';
            documentForm.status = props.documentStatusOptions[0]?.value ?? 'draft';
            documentForm.currency = 'USD';
            documentForm.is_portal_visible = true;
        },
    });
};

const archiveClient = () => {
    archiveForm.patch(archive.url(props.client.id), {
        preserveScroll: true,
    });
};

const restoreClient = () => {
    restoreForm.patch(restore.url(props.client.id), {
        preserveScroll: true,
    });
};

const deleteClient = () => {
    deleteForm.delete(destroy.url(props.client.id), {
        preserveScroll: true,
        onFinish: () => {
            clientDeleteDialogOpen.value = false;
        },
    });
};

const markClientContacted = () => {
    contactForm.patch(markContacted.url(props.client.id), {
        preserveScroll: true,
    });
};

const deleteAttachment = (attachmentId: number, originalName: string) => {
    pendingAttachment.value = {
        id: attachmentId,
        originalName,
    };
};

const confirmDeleteAttachment = () => {
    if (!pendingAttachment.value) {
        return;
    }

    attachmentDeleteForm.delete(attachments.destroy.url(pendingAttachment.value.id), {
        preserveScroll: true,
        onFinish: () => {
            pendingAttachment.value = null;
        },
    });
};

const toggleClientTask = (taskId: number) => {
    useForm({}).patch(toggleTask.url(taskId), {
        preserveScroll: true,
    });
};

const deleteClientTask = (taskId: number, title: string) => {
    pendingTask.value = {
        id: taskId,
        title,
    };
};

const confirmDeleteClientTask = () => {
    if (!pendingTask.value) {
        return;
    }

    taskDeleteForm.delete(destroyTask.url(pendingTask.value.id), {
        preserveScroll: true,
        onFinish: () => {
            pendingTask.value = null;
        },
    });
};

const updateDocumentStatus = (documentId: number, status: string) => {
    useForm({ status }).patch(documents.status.update.url(documentId), {
        preserveScroll: true,
    });
};

const deleteDocument = (documentId: number, documentNumber: string) => {
    pendingDocument.value = {
        id: documentId,
        documentNumber,
    };
};

const confirmDeleteDocument = () => {
    if (!pendingDocument.value) {
        return;
    }

    documentDeleteForm.delete(documents.destroy.url(pendingDocument.value.id), {
        preserveScroll: true,
        onFinish: () => {
            pendingDocument.value = null;
        },
    });
};

const createPortalShare = () => {
    portalShareForm.post(portalShare.store.url(props.client.id), {
        preserveScroll: true,
    });
};

const revokePortalShare = () => {
    if (!props.client.portal_share) {
        return;
    }

    portalRevokeForm.delete(portalShare.destroy.url(props.client.portal_share.id), {
        preserveScroll: true,
        onFinish: () => {
            portalRevokeDialogOpen.value = false;
        },
    });
};

const copyPortalLink = async () => {
    if (!props.client.portal_share?.portal_url) {
        return;
    }

    await navigator.clipboard.writeText(props.client.portal_share.portal_url);
};
</script>

<template>
    <Head :title="client.name" />

    <div class="crm-page">
        <PageHeader
            :title="client.name"
            :description="client.company || 'Independent client record'"
        >
            <template #meta>
                <div class="flex flex-wrap items-center gap-3">
                    <StatusBadge
                        :status="client.status"
                        :label="client.status_label"
                        :archived-at="client.archived_at"
                    />
                    <div
                        v-if="client.tags.length > 0"
                        class="flex flex-wrap items-center gap-2"
                    >
                        <span
                            v-for="tag in client.tags"
                            :key="tag.id"
                            class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200"
                        >
                            {{ tag.name }}
                        </span>
                    </div>
                    <span class="text-sm text-slate-500 dark:text-slate-400">
                        Updated {{ formatRelativeTime(client.updated_at) }}
                    </span>
                </div>
            </template>

            <template #actions>
                <Button variant="outline" as-child>
                    <Link :href="edit(client.id)">Edit details</Link>
                </Button>

                <Dialog v-if="!isArchived">
                    <DialogTrigger as-child>
                        <Button variant="outline">
                            <Archive class="size-4" />
                            Archive
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Archive this client?</DialogTitle>
                            <DialogDescription>
                                The record will move out of your active list but
                                stay restorable from the archived view.
                            </DialogDescription>
                        </DialogHeader>
                        <DialogFooter>
                            <DialogClose as-child>
                                <Button variant="outline">Cancel</Button>
                            </DialogClose>
                            <Button
                                :disabled="archiveForm.processing"
                                @click="archiveClient"
                            >
                                Confirm archive
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>

                <Dialog v-else>
                    <DialogTrigger as-child>
                        <Button variant="outline">
                            <RotateCcw class="size-4" />
                            Restore
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Restore this client?</DialogTitle>
                            <DialogDescription>
                                The record will return to your active directory
                                and show up in dashboard statistics again.
                            </DialogDescription>
                        </DialogHeader>
                        <DialogFooter>
                            <DialogClose as-child>
                                <Button variant="outline">Cancel</Button>
                            </DialogClose>
                            <Button
                                :disabled="restoreForm.processing"
                                @click="restoreClient"
                            >
                                Restore client
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>

                <Button
                    v-if="isArchived"
                    variant="destructive"
                    @click="clientDeleteDialogOpen = true"
                >
                    <Trash2 class="size-4" />
                    Delete
                </Button>
            </template>
        </PageHeader>

        <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <div class="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle class="text-xl">Overview</CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-4 md:grid-cols-2">
                        <div class="crm-subtle-panel">
                            <p
                                class="text-sm font-medium text-slate-500 dark:text-slate-400"
                            >
                                Budget
                            </p>
                            <p class="mt-2 text-lg font-semibold">
                                {{ formatCurrency(client.budget) }}
                            </p>
                        </div>
                        <div class="crm-subtle-panel">
                            <p
                                class="text-sm font-medium text-slate-500 dark:text-slate-400"
                            >
                                Source
                            </p>
                            <p class="mt-2 text-lg font-semibold">
                                {{ client.source || 'Not recorded' }}
                            </p>
                        </div>
                        <div class="crm-subtle-panel">
                            <p
                                class="text-sm font-medium text-slate-500 dark:text-slate-400"
                            >
                                Tags
                            </p>
                            <p class="mt-2 text-lg font-semibold">
                                {{
                                    client.tags.length > 0
                                        ? client.tags
                                              .map((tag) => tag.name)
                                              .join(', ')
                                        : 'Not tagged'
                                }}
                            </p>
                        </div>
                        <div class="crm-subtle-panel">
                            <p
                                class="text-sm font-medium text-slate-500 dark:text-slate-400"
                            >
                                Last contacted
                            </p>
                            <p class="mt-2 text-lg font-semibold">
                                {{ formatDate(client.last_contacted_at) }}
                            </p>
                        </div>
                        <div class="crm-subtle-panel md:col-span-2">
                            <p
                                class="text-sm font-medium text-slate-500 dark:text-slate-400"
                            >
                                Follow-up date
                            </p>
                            <p class="mt-2 text-lg font-semibold">
                                {{ formatDate(client.follow_up_at) }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-xl">Tasks</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-5">
                        <form class="crm-subtle-panel space-y-3" @submit.prevent="submitTask">
                            <label
                                for="task-title"
                                class="text-sm font-medium text-slate-700 dark:text-slate-200"
                            >
                                Add a task
                            </label>
                            <Input
                                id="task-title"
                                v-model="taskForm.title"
                                class="h-11"
                                placeholder="Send revised proposal"
                            />
                            <InputError :message="taskForm.errors.title" />
                            <Textarea
                                v-model="taskForm.description"
                                rows="3"
                                placeholder="Capture the next clear action for this client."
                            />
                            <InputError :message="taskForm.errors.description" />
                            <div class="grid gap-3 md:grid-cols-3">
                                <select v-model="taskForm.status" class="crm-field">
                                    <option
                                        v-for="option in taskStatusOptions"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                                <select v-model="taskForm.priority" class="crm-field">
                                    <option
                                        v-for="option in taskPriorityOptions"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                                <Input v-model="taskForm.due_at" type="date" class="h-11" />
                            </div>
                            <div class="flex justify-end">
                                <Button type="submit" :disabled="taskForm.processing">
                                    <CheckSquare class="size-4" />
                                    Create task
                                </Button>
                            </div>
                        </form>

                        <div v-if="client.tasks.length > 0" class="space-y-3">
                            <div
                                v-for="task in client.tasks"
                                :key="task.id"
                                class="crm-list-item"
                            >
                                <div
                                    class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                                >
                                    <div class="min-w-0 space-y-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p
                                                class="break-words font-semibold text-slate-950 dark:text-white"
                                            >
                                                {{ task.title }}
                                            </p>
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                                {{ task.priority_label }}
                                            </span>
                                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-200">
                                                {{ task.status_label }}
                                            </span>
                                        </div>
                                        <p v-if="task.description" class="text-sm text-slate-600 dark:text-slate-300">
                                            {{ task.description }}
                                        </p>
                                        <div class="flex flex-wrap gap-4 text-sm text-slate-500 dark:text-slate-400">
                                            <span>Due {{ formatDate(task.due_at) }}</span>
                                            <span>Assigned {{ task.assignee.name || 'Unassigned' }}</span>
                                        </div>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-2">
                                        <Button variant="outline" size="sm" @click="toggleClientTask(task.id)">
                                            {{ task.completed_at ? 'Reopen' : 'Complete' }}
                                        </Button>
                                        <Button
                                            variant="outline"
                                            size="icon"
                                            :title="`Delete ${task.title}`"
                                            :aria-label="`Delete ${task.title}`"
                                            @click="deleteClientTask(task.id, task.title)"
                                        >
                                            <Trash2 class="size-4" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <EmptyState
                            v-else
                            title="No tasks yet"
                            description="Tasks are your concrete next steps for this relationship, like sending a proposal or checking in after delivery."
                            :icon="CheckSquare"
                        />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-xl">Notes timeline</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-5">
                        <form
                            class="crm-subtle-panel space-y-3"
                            @submit.prevent="submitNote"
                        >
                            <label
                                for="content"
                                class="text-sm font-medium text-slate-700 dark:text-slate-200"
                            >
                                Add a note
                            </label>
                            <Textarea
                                id="content"
                                v-model="noteForm.content"
                                rows="4"
                                placeholder="Capture call notes, next steps, or context for the next touchpoint."
                            />
                            <InputError :message="noteForm.errors.content" />
                            <Button
                                type="submit"
                                :disabled="noteForm.processing"
                            >
                                <MessageSquareText class="size-4" />
                                Save note
                            </Button>
                        </form>

                        <div v-if="client.notes.length > 0" class="space-y-4">
                            <div
                                v-for="note in client.notes"
                                :key="note.id"
                                class="crm-list-item"
                            >
                                <div
                                    class="flex items-start justify-between gap-3"
                                >
                                    <p
                                        class="text-sm leading-6 text-slate-700 dark:text-slate-200"
                                    >
                                        {{ note.content }}
                                    </p>
                                    <MessageSquareText
                                        class="mt-1 size-4 text-slate-400"
                                    />
                                </div>
                                <div
                                    class="mt-3 flex flex-wrap gap-3 text-xs tracking-[0.18em] text-slate-400 uppercase"
                                >
                                    <span>{{ note.author.name || 'You' }}</span>
                                    <span>{{
                                        formatDateTime(note.created_at)
                                    }}</span>
                                </div>
                            </div>
                        </div>

                        <EmptyState
                            v-else
                            title="No notes yet"
                            description="Add the first note so future follow-ups have context and the client timeline starts telling a story."
                            :icon="MessageSquareText"
                        />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-xl">Communication log</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-5">
                        <form class="crm-subtle-panel space-y-3" @submit.prevent="submitCommunication">
                            <label class="text-sm font-medium text-slate-700 dark:text-slate-200">
                                Log a touchpoint
                            </label>
                            <div class="grid gap-3 md:grid-cols-2">
                                <select v-model="communicationForm.channel" class="crm-field">
                                    <option
                                        v-for="option in communicationChannelOptions"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                                <select v-model="communicationForm.direction" class="crm-field">
                                    <option
                                        v-for="option in communicationDirectionOptions"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>
                            <Input
                                v-model="communicationForm.subject"
                                class="h-11"
                                placeholder="Subject or quick headline"
                            />
                            <Textarea
                                v-model="communicationForm.summary"
                                rows="3"
                                placeholder="Summarize what happened, what the client asked for, and the next step."
                            />
                            <Input v-model="communicationForm.happened_at" type="datetime-local" class="h-11" />
                            <InputError :message="communicationForm.errors.summary" />
                            <div class="flex justify-end">
                                <Button type="submit" :disabled="communicationForm.processing">
                                    <MessageCircleMore class="size-4" />
                                    Log communication
                                </Button>
                            </div>
                        </form>

                        <div v-if="client.communications.length > 0" class="space-y-3">
                            <div
                                v-for="communication in client.communications"
                                :key="communication.id"
                                class="crm-list-item"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="space-y-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700 dark:bg-sky-500/10 dark:text-sky-200">
                                                {{ communication.channel_label }}
                                            </span>
                                            <span class="text-xs font-semibold tracking-[0.16em] text-slate-400 uppercase">
                                                {{ communication.direction_label }}
                                            </span>
                                        </div>
                                        <p v-if="communication.subject" class="font-semibold text-slate-950 dark:text-white">
                                            {{ communication.subject }}
                                        </p>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">
                                            {{ communication.summary }}
                                        </p>
                                    </div>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">
                                        {{ formatDateTime(communication.happened_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <EmptyState
                            v-else
                            title="No communications logged yet"
                            description="Add email, call, meeting, or message history so everyone understands the latest conversation."
                            :icon="MessageCircleMore"
                        />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-xl">Proposals and invoices</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-5">
                        <form class="crm-subtle-panel space-y-3" @submit.prevent="submitDocument">
                            <label class="text-sm font-medium text-slate-700 dark:text-slate-200">
                                Add a business document
                            </label>
                            <div class="grid gap-3 md:grid-cols-2">
                                <select v-model="documentForm.type" class="crm-field">
                                    <option
                                        v-for="option in documentTypeOptions"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                                <select v-model="documentForm.status" class="crm-field">
                                    <option
                                        v-for="option in documentStatusOptions"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>
                            <Input v-model="documentForm.title" class="h-11" placeholder="Website redesign proposal" />
                            <div class="grid gap-3 md:grid-cols-3">
                                <Input v-model="documentForm.amount" type="number" step="0.01" min="0" class="h-11" placeholder="1500.00" />
                                <Input v-model="documentForm.currency" class="h-11" maxlength="3" placeholder="USD" />
                                <label class="flex items-center gap-3 rounded-2xl border border-slate-200/80 px-4 py-3 text-sm text-slate-600 dark:border-slate-800 dark:text-slate-300">
                                    <input v-model="documentForm.is_portal_visible" type="checkbox" class="rounded border-slate-300" />
                                    Visible in client portal
                                </label>
                            </div>
                            <div class="grid gap-3 md:grid-cols-2">
                                <Input v-model="documentForm.issued_at" type="date" class="h-11" />
                                <Input v-model="documentForm.due_at" type="date" class="h-11" />
                            </div>
                            <Textarea
                                v-model="documentForm.notes"
                                rows="3"
                                placeholder="Optional notes such as scope summary, payment terms, or approval context."
                            />
                            <div class="flex justify-end">
                                <Button type="submit" :disabled="documentForm.processing">
                                    <FileText class="size-4" />
                                    Save document
                                </Button>
                            </div>
                        </form>

                        <div v-if="client.documents.length > 0" class="space-y-3">
                            <div
                                v-for="document in client.documents"
                                :key="document.id"
                                class="crm-list-item"
                            >
                                <div
                                    class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                                >
                                    <div class="min-w-0 space-y-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                                {{ document.type_label }}
                                            </span>
                                            <p
                                                class="break-words font-semibold text-slate-950 dark:text-white"
                                            >
                                                {{ document.title }}
                                            </p>
                                        </div>
                                        <div class="flex flex-wrap gap-4 text-sm text-slate-500 dark:text-slate-400">
                                            <span>{{ document.document_number }}</span>
                                            <span>{{ document.currency }} {{ formatCurrency(document.amount) }}</span>
                                            <span>Issued {{ formatDate(document.issued_at) }}</span>
                                            <span>Due {{ formatDate(document.due_at) }}</span>
                                        </div>
                                        <p v-if="document.notes" class="text-sm text-slate-600 dark:text-slate-300">
                                            {{ document.notes }}
                                        </p>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-2">
                                        <select
                                            class="crm-compact-select"
                                            :value="document.status ?? 'draft'"
                                            @change="updateDocumentStatus(document.id, ($event.target as HTMLSelectElement).value)"
                                        >
                                            <option
                                                v-for="option in documentStatusOptions"
                                                :key="option.value"
                                                :value="option.value"
                                            >
                                                {{ option.label }}
                                            </option>
                                        </select>
                                        <Button
                                            variant="outline"
                                            size="icon"
                                            :title="`Remove ${document.document_number}`"
                                            :aria-label="`Remove ${document.document_number}`"
                                            @click="deleteDocument(document.id, document.document_number)"
                                        >
                                            <Trash2 class="size-4" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <EmptyState
                            v-else
                            title="No proposals or invoices yet"
                            description="Track client-facing business documents here so the relationship, delivery, and payment context live together."
                            :icon="ReceiptText"
                        />
                    </CardContent>
                </Card>
            </div>

            <div class="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle class="text-xl">Client portal</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="crm-subtle-panel space-y-3">
                            <p class="text-sm text-slate-600 dark:text-slate-300">
                                Generate a secure share link so the client can view their overview and any portal-visible proposals or invoices.
                            </p>

                            <div v-if="client.portal_share" class="space-y-3">
                                <div class="rounded-2xl border border-emerald-200/80 bg-emerald-50/80 p-4 dark:border-emerald-500/20 dark:bg-emerald-500/10">
                                    <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-200">
                                        Portal link is active
                                    </p>
                                    <p class="mt-2 break-all text-sm text-slate-700 dark:text-slate-200">
                                        {{ client.portal_share.portal_url }}
                                    </p>
                                    <div class="mt-3 flex flex-wrap gap-3 text-sm text-slate-500 dark:text-slate-400">
                                        <span>Created {{ formatDateTime(client.portal_share.created_at) }}</span>
                                        <span>Expires {{ formatDateTime(client.portal_share.expires_at) }}</span>
                                        <span>Last viewed {{ formatDateTime(client.portal_share.last_viewed_at) }}</span>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <Button variant="outline" @click="copyPortalLink">
                                        <ClipboardCopy class="size-4" />
                                        Copy link
                                    </Button>
                                    <Button variant="outline" as-child>
                                        <a :href="client.portal_share.portal_url" target="_blank" rel="noreferrer">
                                            <Globe class="size-4" />
                                            Open portal
                                        </a>
                                    </Button>
                                    <Button
                                        variant="outline"
                                        @click="portalRevokeDialogOpen = true"
                                    >
                                        <Trash2 class="size-4" />
                                        Revoke link
                                    </Button>
                                </div>
                            </div>

                            <Button v-else :disabled="portalShareForm.processing" @click="createPortalShare">
                                <Globe class="size-4" />
                                Generate portal link
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-xl">Attachments</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-5">
                        <form
                            class="crm-subtle-panel space-y-3"
                            @submit.prevent="submitAttachment"
                        >
                            <label
                                for="attachment-file"
                                class="text-sm font-medium text-slate-700 dark:text-slate-200"
                            >
                                Upload a file
                            </label>
                            <Input
                                id="attachment-file"
                                type="file"
                                accept=".pdf,.png,.jpg,.jpeg,.doc,.docx,.txt,.csv"
                                class="h-11 rounded-xl"
                                @input="
                                    attachmentForm.file =
                                        ($event.target as HTMLInputElement)
                                            .files?.[0] ?? null
                                "
                            />
                            <p
                                class="text-sm text-slate-500 dark:text-slate-400"
                            >
                                Great for proposals, contracts, screenshots, or
                                briefs. Files are stored privately and download
                                through the app.
                            </p>
                            <InputError
                                :message="attachmentForm.errors.file"
                            />
                            <Button
                                type="submit"
                                :disabled="
                                    attachmentForm.processing ||
                                    !attachmentForm.file
                                "
                            >
                                <Upload class="size-4" />
                                Upload attachment
                            </Button>
                        </form>

                        <div
                            v-if="client.attachments.length > 0"
                            class="space-y-3"
                        >
                            <div
                                v-for="attachment in client.attachments"
                                :key="attachment.id"
                                class="crm-list-item"
                            >
                                <div
                                    class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                                >
                                    <div class="flex min-w-0 items-start gap-3">
                                        <div
                                            class="shrink-0 rounded-2xl bg-slate-100 p-3 text-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                        >
                                            <Paperclip class="size-5" />
                                        </div>
                                        <div class="min-w-0 space-y-1">
                                            <p
                                                class="break-words font-semibold text-slate-950 dark:text-white"
                                            >
                                                {{
                                                    attachment.original_name
                                                }}
                                            </p>
                                            <div
                                                class="flex flex-wrap gap-3 text-sm text-slate-500 dark:text-slate-400"
                                            >
                                                <span>{{
                                                    attachment.mime_type ||
                                                    'Unknown type'
                                                }}</span>
                                                <span>{{
                                                    formatFileSize(
                                                        attachment.size,
                                                    )
                                                }}</span>
                                                <span>{{
                                                    formatDateTime(
                                                        attachment.created_at,
                                                    )
                                                }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="flex shrink-0 items-center gap-2 self-start"
                                    >
                                        <Button
                                            variant="outline"
                                            size="icon"
                                            as-child
                                            :title="`Download ${attachment.original_name}`"
                                        >
                                            <a
                                                :href="
                                                    attachments.download.url(
                                                        attachment.id,
                                                    )
                                                "
                                                :aria-label="`Download ${attachment.original_name}`"
                                            >
                                                <Download class="size-4" />
                                            </a>
                                        </Button>
                                        <Button
                                            variant="outline"
                                            size="icon"
                                            :disabled="
                                                attachmentDeleteForm.processing
                                            "
                                            :title="`Remove ${attachment.original_name}`"
                                            :aria-label="`Remove ${attachment.original_name}`"
                                            @click="
                                                deleteAttachment(
                                                    attachment.id,
                                                    attachment.original_name,
                                                )
                                            "
                                        >
                                            <Trash2 class="size-4" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <EmptyState
                            v-else
                            title="No attachments yet"
                            description="Upload proposal PDFs, signed agreements, or other client files so everything stays attached to the relationship."
                            :icon="Paperclip"
                        />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-xl">Contact info</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="crm-subtle-panel flex items-center gap-3">
                            <div
                                class="rounded-2xl bg-emerald-50 p-3 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-200"
                            >
                                <Mail class="size-5" />
                            </div>
                            <div>
                                <p
                                    class="text-sm font-medium text-slate-500 dark:text-slate-400"
                                >
                                    Email
                                </p>
                                <p class="font-semibold">
                                    {{ client.email || 'Not provided' }}
                                </p>
                            </div>
                        </div>
                        <div class="crm-subtle-panel flex items-center gap-3">
                            <div
                                class="rounded-2xl bg-sky-50 p-3 text-sky-700 dark:bg-sky-500/10 dark:text-sky-200"
                            >
                                <Phone class="size-5" />
                            </div>
                            <div>
                                <p
                                    class="text-sm font-medium text-slate-500 dark:text-slate-400"
                                >
                                    Phone
                                </p>
                                <p class="font-semibold">
                                    {{ client.phone || 'Not provided' }}
                                </p>
                            </div>
                        </div>
                        <div class="crm-subtle-panel flex items-center gap-3">
                            <div
                                class="rounded-2xl bg-amber-50 p-3 text-amber-700 dark:bg-amber-500/10 dark:text-amber-200"
                            >
                                <CalendarClock class="size-5" />
                            </div>
                            <div>
                                <p
                                    class="text-sm font-medium text-slate-500 dark:text-slate-400"
                                >
                                    Created
                                </p>
                                <p class="font-semibold">
                                    {{ formatDateTime(client.created_at) }}
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <Button
                                v-if="client.email"
                                variant="outline"
                                as-child
                            >
                                <a :href="`mailto:${client.email}`">
                                    <Mail class="size-4" />
                                    Email client
                                </a>
                            </Button>
                            <Button
                                v-if="client.phone"
                                variant="outline"
                                as-child
                            >
                                <a :href="`tel:${client.phone}`">
                                    <Phone class="size-4" />
                                    Call client
                                </a>
                            </Button>
                            <Button
                                class="sm:col-span-2"
                                :disabled="contactForm.processing"
                                @click="markClientContacted"
                            >
                                <BadgeCheck class="size-4" />
                                Mark contacted today
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-xl">Activity timeline</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div
                            v-if="client.activities.length > 0"
                            class="space-y-3"
                        >
                            <div
                                v-for="activity in client.activities"
                                :key="activity.id"
                                class="crm-list-item"
                            >
                                <div
                                    class="flex items-start justify-between gap-3"
                                >
                                    <div>
                                        <p
                                            class="font-semibold text-slate-950 dark:text-white"
                                        >
                                            {{
                                                activity.type_label ||
                                                'Activity'
                                            }}
                                        </p>
                                        <p
                                            class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-300"
                                        >
                                            {{ activity.description }}
                                        </p>
                                    </div>
                                    <BadgeCheck
                                        class="mt-1 size-4 text-slate-400"
                                    />
                                </div>
                                <div
                                    class="mt-3 flex flex-wrap gap-3 text-xs tracking-[0.18em] text-slate-400 uppercase"
                                >
                                    <span>{{
                                        activity.actor.name || 'System'
                                    }}</span>
                                    <span>{{
                                        formatDateTime(activity.created_at)
                                    }}</span>
                                </div>
                            </div>
                        </div>
                        <EmptyState
                            v-else
                            title="No activity yet"
                            description="As you update this client, the important actions will build into a readable history here."
                            :icon="BadgeCheck"
                        />
                    </CardContent>
                </Card>
            </div>
        </div>

        <DangerConfirmDialog
            v-model:open="clientDeleteDialogOpen"
            title="Permanently delete this client?"
            :description="`This will delete ${client.name}, including related notes and attached records. This action cannot be undone.`"
            confirm-label="Delete client"
            :loading="deleteForm.processing"
            @confirm="deleteClient"
        />

        <DangerConfirmDialog
            v-model:open="attachmentDeleteDialogOpen"
            title="Remove this attachment?"
            :description="`This will remove ${pendingAttachment?.originalName ?? 'this file'} from ${client.name}. You can upload it again later if needed.`"
            confirm-label="Remove attachment"
            :loading="attachmentDeleteForm.processing"
            @confirm="confirmDeleteAttachment"
        />

        <DangerConfirmDialog
            v-model:open="taskDeleteDialogOpen"
            title="Delete this task?"
            :description="`This will delete ${pendingTask?.title ?? 'this task'} from the client task list. Completed history remains in the activity timeline when available.`"
            confirm-label="Delete task"
            :loading="taskDeleteForm.processing"
            @confirm="confirmDeleteClientTask"
        />

        <DangerConfirmDialog
            v-model:open="documentDeleteDialogOpen"
            title="Remove this document?"
            :description="`This will remove ${pendingDocument?.documentNumber ?? 'this document'} from ${client.name}. It will also disappear from any active client portal view.`"
            confirm-label="Remove document"
            :loading="documentDeleteForm.processing"
            @confirm="confirmDeleteDocument"
        />

        <DangerConfirmDialog
            v-model:open="portalRevokeDialogOpen"
            title="Revoke this client portal link?"
            description="This will immediately stop the current shared portal link from working. You can generate a new link later."
            confirm-label="Revoke link"
            :loading="portalRevokeForm.processing"
            @confirm="revokePortalShare"
        />
    </div>
</template>
