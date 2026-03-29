<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import {
    Archive,
    CalendarClock,
    Download,
    FileUp,
    FilterX,
    Search,
    SquarePen,
    UserPlus2,
    UsersRound,
} from 'lucide-vue-next';
import { reactive, ref } from 'vue';
import EmptyState from '@/components/crm/EmptyState.vue';
import PageHeader from '@/components/crm/PageHeader.vue';
import PaginationLinks from '@/components/crm/PaginationLinks.vue';
import StatusBadge from '@/components/crm/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import {
    formatCurrency,
    formatDate,
    formatRelativeTime,
} from '@/lib/formatters';
import {
    create as createClient,
    edit as editClient,
    exportMethod as exportClients,
    importMethod as importClients,
    index,
    show as showClient,
} from '@/routes/clients';
import type {
    ClientListFilters,
    ClientListItem,
    ClientStatusOption,
    Paginated,
} from '@/types';

const props = defineProps<{
    clients: Paginated<ClientListItem>;
    filters: ClientListFilters;
    statusOptions: ClientStatusOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Clients',
                href: index(),
            },
        ],
    },
});

const filters = reactive({
    search: props.filters.search ?? '',
    status: props.filters.status ?? '',
    archived: props.filters.archived ?? '',
});
const importDialogOpen = ref(false);
const importForm = useForm<{
    file: File | null;
}>({
    file: null,
});

const submitFilters = () => {
    const query = {
        search: filters.search || undefined,
        status: filters.status || undefined,
        archived: filters.archived || undefined,
    };

    router.get(
        index.url({ query }),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
};

watchDebounced(
    () => [filters.search, filters.status, filters.archived],
    submitFilters,
    {
        debounce: 250,
        maxWait: 750,
    },
);

const clearFilters = () => {
    filters.search = '';
    filters.status = '';
    filters.archived = '';
    submitFilters();
};

const exportUrl = () =>
    exportClients.url({
        query: {
            search: filters.search || undefined,
            status: filters.status || undefined,
            archived: filters.archived || undefined,
        },
    });

const submitImport = () => {
    importForm.post(importClients.url(), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            importDialogOpen.value = false;
            importForm.reset();
            importForm.clearErrors();
        },
    });
};
</script>

<template>
    <Head title="Clients" />

    <div class="crm-page">
        <PageHeader
            eyebrow="Client directory"
            title="Keep every relationship moving forward"
            description="Search by person, company, phone, or email. Use the archive view to safely restore or permanently remove old records."
        >
            <template #actions>
                <Dialog v-model:open="importDialogOpen">
                    <DialogTrigger as-child>
                        <Button variant="outline" class="px-5">
                            <FileUp class="size-4" />
                            Import CSV
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-xl">
                        <DialogHeader>
                            <DialogTitle>Import clients from CSV</DialogTitle>
                            <DialogDescription>
                                Use a spreadsheet export with headers like
                                <code
                                    class="rounded bg-slate-100 px-1.5 py-0.5 text-xs dark:bg-slate-800"
                                    >name</code
                                >,
                                <code
                                    class="rounded bg-slate-100 px-1.5 py-0.5 text-xs dark:bg-slate-800"
                                    >email</code
                                >,
                                <code
                                    class="rounded bg-slate-100 px-1.5 py-0.5 text-xs dark:bg-slate-800"
                                    >status</code
                                >,
                                <code
                                    class="rounded bg-slate-100 px-1.5 py-0.5 text-xs dark:bg-slate-800"
                                    >company</code
                                >, and
                                <code
                                    class="rounded bg-slate-100 px-1.5 py-0.5 text-xs dark:bg-slate-800"
                                    >follow_up_at</code
                                >. The
                                <code
                                    class="rounded bg-slate-100 px-1.5 py-0.5 text-xs dark:bg-slate-800"
                                    >name</code
                                >
                                column is required, and missing statuses default
                                to
                                <strong>Lead</strong>.
                            </DialogDescription>
                        </DialogHeader>

                        <form class="space-y-4" @submit.prevent="submitImport">
                            <div class="space-y-2">
                                <label
                                    for="client-import-file"
                                    class="text-sm font-medium text-slate-700 dark:text-slate-200"
                                >
                                    CSV file
                                </label>
                                <Input
                                    id="client-import-file"
                                    type="file"
                                    accept=".csv,text/csv,.txt"
                                    class="h-11 rounded-xl"
                                    @input="
                                        importForm.file =
                                            ($event.target as HTMLInputElement)
                                                .files?.[0] ?? null
                                    "
                                />
                                <p
                                    class="text-xs leading-5 text-slate-500 dark:text-slate-400"
                                >
                                    If an imported row matches one of your
                                    existing client emails, CRM Lite updates
                                    that client instead of creating a duplicate.
                                </p>
                                <p
                                    v-if="importForm.errors.file"
                                    class="text-sm text-rose-600 dark:text-rose-300"
                                >
                                    {{ importForm.errors.file }}
                                </p>
                            </div>

                            <DialogFooter class="gap-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    @click="importDialogOpen = false"
                                >
                                    Cancel
                                </Button>
                                <Button
                                    type="submit"
                                    :disabled="
                                        importForm.processing ||
                                        !importForm.file
                                    "
                                >
                                    Import clients
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>

                <Button variant="outline" as-child class="px-5">
                    <a :href="exportUrl()">
                        <Download class="size-4" />
                        Export CSV
                    </a>
                </Button>

                <Button as-child class="px-5">
                    <Link :href="createClient()">
                        <UserPlus2 class="size-4" />
                        Add client
                    </Link>
                </Button>
            </template>
        </PageHeader>

        <section class="crm-panel crm-panel-body">
            <div class="grid gap-4 lg:grid-cols-[1.3fr_0.7fr_0.45fr_auto]">
                <div class="grid gap-2">
                    <label
                        for="search"
                        class="text-sm font-medium text-slate-700 dark:text-slate-200"
                    >
                        Search clients
                    </label>
                    <div class="relative">
                        <Search
                            class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                        />
                        <Input
                            id="search"
                            v-model="filters.search"
                            class="h-11 rounded-xl pl-9"
                            placeholder="Search name, company, email, or phone"
                        />
                    </div>
                </div>

                <div class="grid gap-2">
                    <label
                        for="status"
                        class="text-sm font-medium text-slate-700 dark:text-slate-200"
                    >
                        Status
                    </label>
                    <select
                        id="status"
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
                    <span
                        class="text-sm font-medium text-slate-700 dark:text-slate-200"
                    >
                        View
                    </span>
                    <div
                        class="grid grid-cols-2 rounded-2xl bg-slate-100/90 p-1 dark:bg-slate-900/90"
                    >
                        <button
                            type="button"
                            class="rounded-xl px-3 py-2 text-sm font-medium transition"
                            :class="
                                !filters.archived
                                    ? 'bg-white text-slate-950 shadow-sm dark:bg-slate-950 dark:text-white'
                                    : 'text-slate-500 dark:text-slate-400'
                            "
                            @click="filters.archived = ''"
                        >
                            Active
                        </button>
                        <button
                            type="button"
                            class="rounded-xl px-3 py-2 text-sm font-medium transition"
                            :class="
                                filters.archived === 'only'
                                    ? 'bg-white text-slate-950 shadow-sm dark:bg-slate-950 dark:text-white'
                                    : 'text-slate-500 dark:text-slate-400'
                            "
                            @click="filters.archived = 'only'"
                        >
                            Archived
                        </button>
                    </div>
                </div>

                <div class="flex items-end">
                    <Button
                        variant="outline"
                        class="h-11 w-full"
                        @click="clearFilters"
                    >
                        <FilterX class="size-4" />
                        Clear
                    </Button>
                </div>
            </div>
        </section>

        <section class="crm-panel overflow-hidden">
            <div
                class="flex items-center justify-between border-b border-slate-200/70 px-6 py-5 dark:border-slate-800"
            >
                <div>
                    <p class="font-semibold text-slate-950 dark:text-white">
                        {{ clients.total }} client{{
                            clients.total === 1 ? '' : 's'
                        }}
                    </p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{
                            filters.archived === 'only'
                                ? 'Archived records you can restore or remove.'
                                : 'Fresh search results update as you type.'
                        }}
                    </p>
                </div>
            </div>

            <div v-if="clients.data.length > 0">
                <div
                    class="hidden grid-cols-[minmax(18rem,2.4fr)_minmax(8rem,0.9fr)_minmax(8rem,0.8fr)_minmax(10rem,1fr)_minmax(7rem,0.8fr)_auto] gap-6 border-b border-slate-200/70 px-10 py-4 pr-24 text-xs font-semibold tracking-[0.18em] text-slate-400 uppercase lg:grid dark:border-slate-800"
                >
                    <span class="self-center justify-self-start">Client</span>
                    <span class="self-center justify-self-start">Status</span>
                    <span class="self-center justify-self-start">Budget</span>
                    <span class="self-center justify-self-start">Follow-up</span>
                    <span class="self-center justify-self-start">Updated</span>
                    <span class="self-center justify-self-end text-right">Actions</span>
                </div>

                <div
                    v-for="client in clients.data"
                    :key="client.id"
                    class="grid gap-4 border-b border-slate-200/70 px-6 py-5 transition last:border-0 hover:bg-white/35 lg:grid-cols-[minmax(18rem,2.4fr)_minmax(8rem,0.9fr)_minmax(8rem,0.8fr)_minmax(10rem,1fr)_minmax(7rem,0.8fr)_auto] lg:items-center lg:gap-6 dark:border-slate-800 dark:hover:bg-slate-900/30"
                >
                    <div class="min-w-0 self-center justify-self-start">
                        <Link
                            :href="showClient(client.id)"
                            class="text-base font-semibold text-slate-950 transition hover:text-emerald-600 dark:text-white dark:hover:text-emerald-300"
                        >
                            {{ client.name }}
                        </Link>
                        <div
                            class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-slate-500 dark:text-slate-400"
                        >
                            <span>{{
                                client.company || 'Independent client'
                            }}</span>
                            <span v-if="client.email">{{ client.email }}</span>
                            <span v-if="client.phone">{{ client.phone }}</span>
                            <span
                                >{{ client.notes_count }} note{{
                                    client.notes_count === 1 ? '' : 's'
                                }}</span
                            >
                        </div>
                    </div>

                    <div class="min-w-0 self-center justify-self-start">
                        <StatusBadge
                            :status="client.status"
                            :label="client.status_label"
                            :archived-at="client.archived_at"
                        />
                    </div>

                    <div
                        class="min-w-0 self-center justify-self-start text-sm text-slate-600 dark:text-slate-300"
                    >
                        {{ formatCurrency(client.budget) }}
                    </div>

                    <div
                        class="flex min-w-0 items-center gap-2 self-center justify-self-start text-sm text-slate-600 dark:text-slate-300"
                    >
                        <CalendarClock
                            class="size-4 shrink-0 text-slate-400"
                        />
                        <span class="truncate">{{
                            formatDate(client.follow_up_at)
                        }}</span>
                    </div>

                    <div
                        class="min-w-0 self-center justify-self-start text-sm text-slate-500 dark:text-slate-400"
                    >
                        {{ formatRelativeTime(client.updated_at) }}
                    </div>

                    <div
                        class="flex items-center justify-start gap-2 self-center justify-self-end lg:justify-end"
                    >
                        <Button variant="ghost" size="sm" as-child>
                            <Link :href="showClient(client.id)">Open</Link>
                        </Button>
                        <Button variant="outline" size="sm" as-child>
                            <Link :href="editClient(client.id)">
                                <SquarePen class="size-4" />
                                Edit
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>

            <div v-else class="p-6">
                <EmptyState
                    :icon="filters.archived === 'only' ? Archive : UsersRound"
                    :title="
                        filters.archived === 'only'
                            ? 'No archived clients'
                            : 'No clients match these filters'
                    "
                    :description="
                        filters.archived === 'only'
                            ? 'Archive a client from its detail page to keep your active workspace tidy without losing history.'
                            : 'Start by adding a client or clear your filters to widen the search.'
                    "
                >
                    <Button
                        v-if="
                            filters.search || filters.status || filters.archived
                        "
                        variant="outline"
                        @click="clearFilters"
                    >
                        Clear filters
                    </Button>
                    <Button v-else as-child>
                        <Link :href="createClient()"
                            >Add your first client</Link
                        >
                    </Button>
                </EmptyState>
            </div>
        </section>

        <PaginationLinks :links="clients.links" />
    </div>
</template>
