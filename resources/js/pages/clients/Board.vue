<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import PageHeader from '@/components/crm/PageHeader.vue';
import StatusBadge from '@/components/crm/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { formatDate, formatRelativeTime } from '@/lib/formatters';
import {
    board,
    index,
    show as showClient,
} from '@/routes/clients';
import clientStatusRoutes from '@/routes/clients/status';
import type { ClientStatusOption } from '@/types';

type BoardClient = {
    id: number;
    name: string;
    company: string | null;
    status: string | null;
    status_label: string | null;
    follow_up_at: string | null;
    updated_at: string | null;
};

defineProps<{
    columns: Array<{
        value: string;
        label: string;
        clients: BoardClient[];
    }>;
    statusOptions: ClientStatusOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Clients',
                href: index(),
            },
            {
                title: 'Pipeline board',
                href: board(),
            },
        ],
    },
});

const moveClient = (client: BoardClient, status: string) => {
    router.patch(
        clientStatusRoutes.update.url(client.id),
        { status },
        { preserveScroll: true },
    );
};
</script>

<template>
    <Head title="Client board" />

    <div class="crm-page">
        <PageHeader
            eyebrow="Pipeline"
            title="See the client journey as a board"
            description="Use the board to move opportunities and active work through each relationship stage."
        >
            <template #actions>
                <Button variant="outline" as-child>
                    <Link :href="index()">List view</Link>
                </Button>
            </template>
        </PageHeader>

        <div class="grid gap-5 xl:grid-cols-5">
            <section
                v-for="column in columns"
                :key="column.value"
                class="crm-panel crm-panel-body flex min-h-[24rem] flex-col gap-4"
            >
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">
                            {{ column.label }}
                        </h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            {{ column.clients.length }} client{{
                                column.clients.length === 1 ? '' : 's'
                            }}
                        </p>
                    </div>
                </div>

                <div class="space-y-3">
                    <div
                        v-for="client in column.clients"
                        :key="client.id"
                        class="rounded-[1.4rem] border border-slate-200/80 bg-white/85 p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950/65"
                    >
                        <div class="space-y-3">
                            <div>
                                <Link
                                    :href="showClient(client.id)"
                                    class="font-semibold text-slate-950 hover:text-emerald-600 dark:text-white dark:hover:text-emerald-300"
                                >
                                    {{ client.name }}
                                </Link>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    {{ client.company || 'Independent client' }}
                                </p>
                            </div>

                            <StatusBadge
                                :status="client.status"
                                :label="client.status_label"
                            />

                            <div class="space-y-1 text-sm text-slate-500 dark:text-slate-400">
                                <p>Follow-up: {{ formatDate(client.follow_up_at) }}</p>
                                <p>Updated {{ formatRelativeTime(client.updated_at) }}</p>
                            </div>

                            <div class="grid gap-2">
                                <label class="text-xs font-semibold tracking-[0.14em] text-slate-400 uppercase">
                                    Move to
                                </label>
                                <select
                                    class="crm-field"
                                    :value="client.status ?? column.value"
                                    @change="moveClient(client, ($event.target as HTMLSelectElement).value)"
                                >
                                    <option
                                        v-for="option in statusOptions"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
