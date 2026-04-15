<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    AlertTriangle,
    BriefcaseBusiness,
    CalendarClock,
    CheckSquare,
    CircleDashed,
    Clock3,
    Mail,
    MessageCircleMore,
    NotebookText,
    Sparkles,
    TrendingUp,
    UsersRound,
    Phone,
} from 'lucide-vue-next';
import EmptyState from '@/components/crm/EmptyState.vue';
import PageHeader from '@/components/crm/PageHeader.vue';
import StatCard from '@/components/crm/StatCard.vue';
import StatusBadge from '@/components/crm/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    formatDate,
    formatDateTime,
    formatRelativeTime,
} from '@/lib/formatters';
import { dashboard } from '@/routes';
import { show as showClient } from '@/routes/clients';
import type {
    DashboardActivityItem,
    DashboardCommunicationItem,
    DashboardRecentClient,
    DashboardRecentNote,
    DashboardReminderGroup,
    DashboardStats,
    DashboardTaskItem,
} from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

defineProps<{
    stats: DashboardStats;
    recentlyUpdatedClients: DashboardRecentClient[];
    recentNotes: DashboardRecentNote[];
    followUpReminders: DashboardReminderGroup;
    recentActivity: DashboardActivityItem[];
    upcomingTasks: DashboardTaskItem[];
    recentCommunications: DashboardCommunicationItem[];
}>();
</script>

<template>
    <Head title="Dashboard" />

    <div class="crm-page">
        <PageHeader
            eyebrow="Dashboard"
            title="Your client pulse in one glance"
            description="See who is active, which leads need momentum, and where the next follow-up should happen."
        />

        <div
            class="grid gap-4 min-[1700px]:grid-cols-6 md:grid-cols-2 xl:grid-cols-3"
        >
            <StatCard
                title="Total clients"
                :value="stats.totalClients"
                description="Current non-archived client records in your workspace."
                :icon="UsersRound"
            />
            <StatCard
                title="Active clients"
                :value="stats.activeClients"
                description="Work already in motion and actively being managed."
                :icon="BriefcaseBusiness"
            />
            <StatCard
                title="Leads"
                :value="stats.leads"
                description="Early-stage opportunities still warming up."
                :icon="Sparkles"
            />
            <StatCard
                title="Open tasks"
                :value="stats.openTasks"
                description="Next actions still in progress across your workspace."
                :icon="CheckSquare"
            />
            <StatCard
                title="Overdue"
                :value="stats.overdueFollowUps"
                description="Follow-ups that should already have happened."
                :icon="AlertTriangle"
            />
            <StatCard
                title="Due soon"
                :value="stats.followUpsDueSoon"
                description="Follow-ups scheduled in the next seven days."
                :icon="CalendarClock"
            />
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle class="text-xl">Overdue follow-ups</CardTitle>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        These clients need attention first so nothing slips
                        through.
                    </p>
                </CardHeader>
                <CardContent>
                    <div
                        v-if="followUpReminders.overdue.length > 0"
                        class="space-y-3"
                    >
                        <div
                            v-for="client in followUpReminders.overdue"
                            :key="client.id"
                            class="crm-list-item border-rose-200/80 bg-rose-50/70 dark:border-rose-500/20 dark:bg-rose-500/10"
                        >
                            <div
                                class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between"
                            >
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <a
                                            :href="showClient.url(client.id)"
                                            class="font-semibold text-slate-950 hover:text-emerald-600 dark:text-white dark:hover:text-emerald-300"
                                        >
                                            {{ client.name }}
                                        </a>
                                        <StatusBadge
                                            :status="client.status"
                                            :label="client.status_label"
                                        />
                                    </div>
                                    <p
                                        class="text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        {{
                                            client.company ||
                                            'Independent client'
                                        }}
                                    </p>
                                    <div
                                        class="flex flex-wrap gap-4 text-sm text-slate-600 dark:text-slate-300"
                                    >
                                        <span
                                            >Due
                                            {{
                                                formatDate(client.follow_up_at)
                                            }}</span
                                        >
                                        <span
                                            >Last contacted
                                            {{
                                                formatDate(
                                                    client.last_contacted_at,
                                                )
                                            }}</span
                                        >
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        as-child
                                    >
                                        <a :href="showClient.url(client.id)"
                                            >Open</a
                                        >
                                    </Button>
                                    <Button
                                        v-if="client.email"
                                        variant="outline"
                                        size="sm"
                                        as-child
                                    >
                                        <a :href="`mailto:${client.email}`">
                                            <Mail class="size-4" />
                                            Email
                                        </a>
                                    </Button>
                                    <Button
                                        v-if="client.phone"
                                        variant="outline"
                                        size="sm"
                                        as-child
                                    >
                                        <a :href="`tel:${client.phone}`">
                                            <Phone class="size-4" />
                                            Call
                                        </a>
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <EmptyState
                        v-else
                        title="No overdue follow-ups"
                        description="You are caught up. Overdue reminders will show here when a follow-up date passes."
                        :icon="AlertTriangle"
                    />
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="text-xl">Upcoming this week</CardTitle>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        A quick view of who should hear from you next.
                    </p>
                </CardHeader>
                <CardContent>
                    <div
                        v-if="followUpReminders.upcoming.length > 0"
                        class="space-y-3"
                    >
                        <div
                            v-for="client in followUpReminders.upcoming"
                            :key="client.id"
                            class="crm-list-item"
                        >
                            <div
                                class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between"
                            >
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <a
                                            :href="showClient.url(client.id)"
                                            class="font-semibold text-slate-950 hover:text-emerald-600 dark:text-white dark:hover:text-emerald-300"
                                        >
                                            {{ client.name }}
                                        </a>
                                        <StatusBadge
                                            :status="client.status"
                                            :label="client.status_label"
                                        />
                                    </div>
                                    <p
                                        class="text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        {{
                                            client.company ||
                                            'Independent client'
                                        }}
                                    </p>
                                    <div
                                        class="flex flex-wrap gap-4 text-sm text-slate-600 dark:text-slate-300"
                                    >
                                        <span
                                            >Due
                                            {{
                                                formatDate(client.follow_up_at)
                                            }}</span
                                        >
                                        <span
                                            >Last contacted
                                            {{
                                                formatDate(
                                                    client.last_contacted_at,
                                                )
                                            }}</span
                                        >
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        as-child
                                    >
                                        <a :href="showClient.url(client.id)"
                                            >Open</a
                                        >
                                    </Button>
                                    <Button
                                        v-if="client.email"
                                        variant="outline"
                                        size="sm"
                                        as-child
                                    >
                                        <a :href="`mailto:${client.email}`">
                                            <Mail class="size-4" />
                                            Email
                                        </a>
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <EmptyState
                        v-else
                        title="No follow-ups scheduled this week"
                        description="Once you add follow-up dates, they will appear here in deadline order."
                        :icon="Clock3"
                    />
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle class="text-xl">Upcoming tasks</CardTitle>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Work that still needs to move before the client
                        relationship stalls.
                    </p>
                </CardHeader>
                <CardContent>
                    <div v-if="upcomingTasks.length > 0" class="space-y-3">
                        <div
                            v-for="task in upcomingTasks"
                            :key="task.id"
                            class="crm-list-item"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="space-y-2">
                                    <div
                                        class="flex flex-wrap items-center gap-2"
                                    >
                                        <p
                                            class="font-semibold text-slate-950 dark:text-white"
                                        >
                                            {{ task.title }}
                                        </p>
                                        <span
                                            class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                        >
                                            {{
                                                task.priority_label ||
                                                'Priority'
                                            }}
                                        </span>
                                    </div>
                                    <p
                                        v-if="task.description"
                                        class="text-sm text-slate-600 dark:text-slate-300"
                                    >
                                        {{ task.description }}
                                    </p>
                                    <div
                                        class="flex flex-wrap gap-4 text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        <span
                                            >Client
                                            {{
                                                task.client.name ||
                                                'General task'
                                            }}</span
                                        >
                                        <span
                                            >Due
                                            {{ formatDate(task.due_at) }}</span
                                        >
                                        <span
                                            >Assignee
                                            {{
                                                task.assignee.name ||
                                                'Unassigned'
                                            }}</span
                                        >
                                    </div>
                                </div>
                                <StatusBadge
                                    :status="task.status"
                                    :label="task.status_label"
                                />
                            </div>
                        </div>
                    </div>
                    <EmptyState
                        v-else
                        title="No open tasks"
                        description="Create tasks from your task board or client pages to turn follow-up intent into clear work."
                        :icon="CheckSquare"
                    />
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="text-xl">Recent communications</CardTitle>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        A lightweight communication log so you can see the
                        latest outreach at a glance.
                    </p>
                </CardHeader>
                <CardContent>
                    <div
                        v-if="recentCommunications.length > 0"
                        class="space-y-3"
                    >
                        <div
                            v-for="communication in recentCommunications"
                            :key="communication.id"
                            class="crm-list-item"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="space-y-2">
                                    <div
                                        class="flex flex-wrap items-center gap-2"
                                    >
                                        <p
                                            class="font-semibold text-slate-950 dark:text-white"
                                        >
                                            {{
                                                communication.client?.name ||
                                                'Unknown client'
                                            }}
                                        </p>
                                        <span
                                            class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-200"
                                        >
                                            {{
                                                communication.channel_label ||
                                                'Touchpoint'
                                            }}
                                        </span>
                                        <span
                                            class="text-xs font-semibold tracking-[0.16em] text-slate-400 uppercase"
                                        >
                                            {{
                                                communication.direction_label ||
                                                'Logged'
                                            }}
                                        </span>
                                    </div>
                                    <p
                                        v-if="communication.subject"
                                        class="text-sm font-medium text-slate-700 dark:text-slate-200"
                                    >
                                        {{ communication.subject }}
                                    </p>
                                    <p
                                        class="text-sm text-slate-600 dark:text-slate-300"
                                    >
                                        {{ communication.summary }}
                                    </p>
                                </div>
                                <div
                                    class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    <MessageCircleMore class="size-4" />
                                    {{
                                        formatDateTime(
                                            communication.happened_at,
                                        )
                                    }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <EmptyState
                        v-else
                        title="No communications logged yet"
                        description="Log calls, emails, meetings, or messages from a client page and they will show up here."
                        :icon="MessageCircleMore"
                    />
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between gap-3"
                >
                    <div>
                        <CardTitle class="text-xl"
                            >Recently updated clients</CardTitle
                        >
                        <p
                            class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                        >
                            The latest records you touched or moved forward.
                        </p>
                    </div>
                </CardHeader>
                <CardContent>
                    <div
                        v-if="recentlyUpdatedClients.length > 0"
                        class="space-y-3"
                    >
                        <a
                            v-for="client in recentlyUpdatedClients"
                            :key="client.id"
                            :href="showClient.url(client.id)"
                            class="crm-list-item flex flex-col gap-3"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p
                                        class="font-semibold text-slate-950 dark:text-white"
                                    >
                                        {{ client.name }}
                                    </p>
                                    <p
                                        class="text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        {{
                                            client.company ||
                                            'Independent client'
                                        }}
                                    </p>
                                </div>
                                <StatusBadge
                                    :status="client.status"
                                    :label="client.status_label"
                                />
                            </div>
                            <div
                                class="flex flex-wrap gap-4 text-sm text-slate-500 dark:text-slate-400"
                            >
                                <span
                                    >Updated
                                    {{
                                        formatRelativeTime(client.updated_at)
                                    }}</span
                                >
                                <span
                                    >Follow-up
                                    {{
                                        formatDate(
                                            client.follow_up_at,
                                            'Not scheduled',
                                        )
                                    }}</span
                                >
                            </div>
                        </a>
                    </div>
                    <EmptyState
                        v-else
                        title="No recent client activity yet"
                        description="Once you add clients and start updating records, your most recent work will show up here."
                        :icon="CircleDashed"
                    />
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="text-xl">Recent notes</CardTitle>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        A quick read on the most recent client conversations.
                    </p>
                </CardHeader>
                <CardContent>
                    <div v-if="recentNotes.length > 0" class="space-y-4">
                        <div
                            v-for="note in recentNotes"
                            :key="note.id"
                            class="crm-list-item"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p
                                        class="font-semibold text-slate-950 dark:text-white"
                                    >
                                        {{
                                            note.client.name || 'Unknown client'
                                        }}
                                    </p>
                                    <p
                                        class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-300"
                                    >
                                        {{ note.content }}
                                    </p>
                                </div>
                                <NotebookText
                                    class="mt-1 size-5 text-slate-400"
                                />
                            </div>
                            <div
                                class="mt-3 flex items-center gap-3 text-xs tracking-[0.18em] text-slate-400 uppercase"
                            >
                                <span>{{
                                    note.client.status_label || 'No status'
                                }}</span>
                                <span>{{
                                    formatDateTime(note.created_at)
                                }}</span>
                            </div>
                        </div>
                    </div>
                    <EmptyState
                        v-else
                        title="No notes yet"
                        description="Add a note from any client record to build a timeline of conversations and follow-ups."
                        :icon="NotebookText"
                    />
                </CardContent>
            </Card>
        </div>

        <Card>
            <CardHeader>
                <CardTitle class="text-xl">Recent activity</CardTitle>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    A simple timeline of the important changes happening inside
                    your CRM.
                </p>
            </CardHeader>
            <CardContent>
                <div v-if="recentActivity.length > 0" class="space-y-3">
                    <div
                        v-for="activity in recentActivity"
                        :key="activity.id"
                        class="crm-list-item flex flex-col gap-3 md:flex-row md:items-center md:justify-between"
                    >
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p
                                    class="font-semibold text-slate-950 dark:text-white"
                                >
                                    {{ activity.type_label || 'Activity' }}
                                </p>
                                <StatusBadge
                                    v-if="activity.client.status"
                                    :status="activity.client.status"
                                    :label="activity.client.status_label"
                                />
                            </div>
                            <p
                                class="text-sm text-slate-600 dark:text-slate-300"
                            >
                                {{ activity.description }}
                            </p>
                            <p
                                class="text-sm text-slate-500 dark:text-slate-400"
                            >
                                {{ activity.client.name || 'Deleted client' }}
                            </p>
                        </div>
                        <div
                            class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400"
                        >
                            <TrendingUp class="size-4" />
                            {{ formatDateTime(activity.created_at) }}
                        </div>
                    </div>
                </div>
                <EmptyState
                    v-else
                    title="No activity yet"
                    description="As you create, update, and follow up with clients, a readable activity history will build here."
                    :icon="TrendingUp"
                />
            </CardContent>
        </Card>
    </div>
</template>
