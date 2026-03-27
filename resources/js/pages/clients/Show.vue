<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    Archive,
    BadgeCheck,
    CalendarClock,
    Mail,
    MessageSquareText,
    Phone,
    RotateCcw,
    Trash2,
} from 'lucide-vue-next';
import { computed } from 'vue';
import EmptyState from '@/components/crm/EmptyState.vue';
import PageHeader from '@/components/crm/PageHeader.vue';
import StatusBadge from '@/components/crm/StatusBadge.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
import notes from '@/routes/clients/notes';
import type { ClientDetail } from '@/types';

const props = defineProps<{
    client: ClientDetail;
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

const archiveForm = useForm({});
const restoreForm = useForm({});
const deleteForm = useForm({});
const contactForm = useForm({});

const isArchived = computed(() => props.client.archived_at !== null);

const submitNote = () => {
    noteForm.post(notes.store.url(props.client.id), {
        preserveScroll: true,
        onSuccess: () => noteForm.reset(),
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
    });
};

const markClientContacted = () => {
    contactForm.patch(markContacted.url(props.client.id), {
        preserveScroll: true,
    });
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

                <Dialog v-if="isArchived">
                    <DialogTrigger as-child>
                        <Button variant="destructive">
                            <Trash2 class="size-4" />
                            Delete
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle
                                >Permanently delete this client?</DialogTitle
                            >
                            <DialogDescription>
                                This removes the client and every attached note.
                                This action cannot be undone.
                            </DialogDescription>
                        </DialogHeader>
                        <DialogFooter>
                            <DialogClose as-child>
                                <Button variant="outline">Cancel</Button>
                            </DialogClose>
                            <Button
                                variant="destructive"
                                :disabled="deleteForm.processing"
                                @click="deleteClient"
                            >
                                Permanently delete
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
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
                                Last contacted
                            </p>
                            <p class="mt-2 text-lg font-semibold">
                                {{ formatDate(client.last_contacted_at) }}
                            </p>
                        </div>
                        <div class="crm-subtle-panel">
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
            </div>

            <div class="space-y-6">
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
    </div>
</template>
