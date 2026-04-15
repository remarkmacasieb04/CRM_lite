<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    CalendarClock,
    CircleDashed,
    FileText,
    Mail,
    Phone,
} from 'lucide-vue-next';
import EmptyState from '@/components/crm/EmptyState.vue';
import StatusBadge from '@/components/crm/StatusBadge.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatCurrency, formatDate, formatDateTime } from '@/lib/formatters';

defineProps<{
    portal: {
        workspace: string | null;
        client: {
            name: string;
            company: string | null;
            email: string | null;
            phone: string | null;
            status_label: string | null;
            follow_up_at: string | null;
        };
        documents: Array<{
            id: number;
            type_label: string | null;
            title: string;
            document_number: string;
            status_label: string | null;
            amount: string | null;
            currency: string;
            issued_at: string | null;
            due_at: string | null;
            notes: string | null;
        }>;
        expires_at: string | null;
        last_viewed_at: string | null;
    };
}>();
</script>

<template>
    <Head :title="`Client portal | ${portal.client.name}`" />

    <div
        class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(16,185,129,0.14),_transparent_28%),linear-gradient(180deg,_#f8fafc,_#eef2ff)] px-4 py-10 dark:bg-[radial-gradient(circle_at_top,_rgba(16,185,129,0.18),_transparent_28%),linear-gradient(180deg,_#020617,_#0f172a)]"
    >
        <div class="mx-auto flex max-w-5xl flex-col gap-6">
            <div
                class="rounded-[2rem] border border-slate-200/80 bg-white/90 p-8 shadow-[0_30px_80px_-45px_rgba(15,23,42,0.35)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75"
            >
                <p
                    class="text-sm font-semibold tracking-[0.2em] text-emerald-600 uppercase dark:text-emerald-300"
                >
                    {{ portal.workspace || 'CRM Lite' }}
                </p>
                <h1
                    class="mt-4 text-3xl font-semibold tracking-tight text-slate-950 dark:text-white"
                >
                    {{ portal.client.name }}
                </h1>
                <p class="mt-2 text-base text-slate-600 dark:text-slate-300">
                    {{ portal.client.company || 'Client portal overview' }}
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <StatusBadge
                        :status="null"
                        :label="portal.client.status_label || 'Shared portal'"
                    />
                    <span
                        class="rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                    >
                        Expires {{ formatDateTime(portal.expires_at) }}
                    </span>
                    <span
                        class="rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                    >
                        Last viewed {{ formatDateTime(portal.last_viewed_at) }}
                    </span>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
                <Card>
                    <CardHeader>
                        <CardTitle class="text-xl">Client details</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="crm-subtle-panel flex items-center gap-3">
                            <Mail
                                class="size-5 text-emerald-600 dark:text-emerald-300"
                            />
                            <div>
                                <p
                                    class="text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Email
                                </p>
                                <p
                                    class="font-semibold text-slate-950 dark:text-white"
                                >
                                    {{ portal.client.email || 'Not shared' }}
                                </p>
                            </div>
                        </div>
                        <div class="crm-subtle-panel flex items-center gap-3">
                            <Phone
                                class="size-5 text-sky-600 dark:text-sky-300"
                            />
                            <div>
                                <p
                                    class="text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Phone
                                </p>
                                <p
                                    class="font-semibold text-slate-950 dark:text-white"
                                >
                                    {{ portal.client.phone || 'Not shared' }}
                                </p>
                            </div>
                        </div>
                        <div class="crm-subtle-panel flex items-center gap-3">
                            <CalendarClock
                                class="size-5 text-amber-600 dark:text-amber-300"
                            />
                            <div>
                                <p
                                    class="text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Next follow-up
                                </p>
                                <p
                                    class="font-semibold text-slate-950 dark:text-white"
                                >
                                    {{ formatDate(portal.client.follow_up_at) }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-xl">Shared documents</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div
                            v-if="portal.documents.length > 0"
                            class="space-y-3"
                        >
                            <div
                                v-for="document in portal.documents"
                                :key="document.id"
                                class="crm-list-item"
                            >
                                <div
                                    class="flex items-start justify-between gap-3"
                                >
                                    <div class="space-y-2">
                                        <div
                                            class="flex flex-wrap items-center gap-2"
                                        >
                                            <span
                                                class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                            >
                                                {{ document.type_label }}
                                            </span>
                                            <p
                                                class="font-semibold text-slate-950 dark:text-white"
                                            >
                                                {{ document.title }}
                                            </p>
                                        </div>
                                        <div
                                            class="flex flex-wrap gap-4 text-sm text-slate-500 dark:text-slate-400"
                                        >
                                            <span>{{
                                                document.document_number
                                            }}</span>
                                            <span>{{
                                                document.status_label
                                            }}</span>
                                            <span
                                                >{{ document.currency }}
                                                {{
                                                    formatCurrency(
                                                        document.amount,
                                                    )
                                                }}</span
                                            >
                                            <span
                                                >Issued
                                                {{
                                                    formatDate(
                                                        document.issued_at,
                                                    )
                                                }}</span
                                            >
                                            <span
                                                >Due
                                                {{
                                                    formatDate(document.due_at)
                                                }}</span
                                            >
                                        </div>
                                        <p
                                            v-if="document.notes"
                                            class="text-sm text-slate-600 dark:text-slate-300"
                                        >
                                            {{ document.notes }}
                                        </p>
                                    </div>
                                    <FileText class="size-5 text-slate-400" />
                                </div>
                            </div>
                        </div>
                        <EmptyState
                            v-else
                            title="No shared documents yet"
                            description="Documents will appear here when the workspace marks them as visible in the portal."
                            :icon="CircleDashed"
                        />
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
