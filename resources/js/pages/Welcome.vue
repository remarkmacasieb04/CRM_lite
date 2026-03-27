<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    ChartColumnIncreasing,
    Clock3,
    NotebookPen,
    ShieldCheck,
    UsersRound,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { dashboard, home, login, register } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);

const page = usePage();
const isAuthenticated = computed(() => page.props.auth.user !== null);
</script>

<template>
    <Head title="CRM Lite" />

    <div class="min-h-screen bg-transparent text-slate-950 dark:text-white">
        <header
            class="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-6 lg:px-8"
        >
            <Link :href="home()" class="flex items-center gap-3">
                <div
                    class="rounded-2xl bg-gradient-to-br from-slate-950 via-slate-800 to-emerald-500 p-3 text-white shadow-lg shadow-emerald-500/15 dark:from-white dark:via-slate-100 dark:to-emerald-400 dark:text-slate-950"
                >
                    <UsersRound class="size-5" />
                </div>
                <div>
                    <p
                        class="text-sm font-semibold tracking-[0.22em] text-emerald-600 uppercase dark:text-emerald-300"
                    >
                        CRM Lite
                    </p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Built for freelancers and small service teams
                    </p>
                </div>
            </Link>

            <nav class="flex items-center gap-3">
                <template v-if="isAuthenticated">
                    <Button
                        as-child
                        class="rounded-full bg-slate-950 px-5 hover:bg-slate-800 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-100"
                    >
                        <Link :href="dashboard()">
                            Open dashboard
                            <ArrowRight class="size-4" />
                        </Link>
                    </Button>
                </template>
                <template v-else>
                    <Button variant="ghost" as-child class="rounded-full px-4">
                        <Link :href="login()">Log in</Link>
                    </Button>
                    <Button
                        v-if="canRegister"
                        as-child
                        class="rounded-full bg-emerald-600 px-5 hover:bg-emerald-500"
                    >
                        <Link :href="register()">Start free</Link>
                    </Button>
                </template>
            </nav>
        </header>

        <main
            class="mx-auto grid max-w-7xl gap-16 px-6 pt-8 pb-16 lg:grid-cols-[1.05fr_0.95fr] lg:px-8 lg:pt-14"
        >
            <section class="max-w-2xl">
                <span
                    class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-4 py-1 text-xs font-semibold tracking-[0.28em] text-emerald-700 uppercase dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200"
                >
                    Calm pipeline control
                </span>
                <h1
                    class="mt-6 text-5xl font-semibold tracking-tight text-slate-950 sm:text-6xl dark:text-white"
                >
                    Client management that feels like a product, not a
                    spreadsheet.
                </h1>
                <p
                    class="mt-6 max-w-xl text-lg leading-8 text-slate-600 dark:text-slate-300"
                >
                    Track leads, active clients, follow-ups, and notes in one
                    polished workspace with secure sign-in, flexible filtering,
                    and the kind of clarity solo operators actually need.
                </p>

                <div class="mt-10 flex flex-wrap gap-4">
                    <Button
                        v-if="!isAuthenticated && canRegister"
                        as-child
                        size="lg"
                        class="rounded-full bg-slate-950 px-6 hover:bg-slate-800 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-100"
                    >
                        <Link :href="register()">
                            Create your workspace
                            <ArrowRight class="size-4" />
                        </Link>
                    </Button>
                    <Button
                        v-else
                        as-child
                        size="lg"
                        class="rounded-full bg-slate-950 px-6 hover:bg-slate-800 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-100"
                    >
                        <Link :href="dashboard()">Go to dashboard</Link>
                    </Button>
                    <Button
                        variant="outline"
                        as-child
                        size="lg"
                        class="rounded-full px-6"
                    >
                        <Link :href="login()">See the sign-in flow</Link>
                    </Button>
                </div>

                <div class="mt-12 grid gap-4 sm:grid-cols-3">
                    <div class="crm-subtle-panel">
                        <p
                            class="text-sm font-medium text-slate-500 dark:text-slate-400"
                        >
                            Follow-ups
                        </p>
                        <p class="mt-3 text-3xl font-semibold">7 days</p>
                        <p
                            class="mt-2 text-sm text-slate-500 dark:text-slate-400"
                        >
                            Spot what needs attention before it slips.
                        </p>
                    </div>
                    <div class="crm-subtle-panel">
                        <p
                            class="text-sm font-medium text-slate-500 dark:text-slate-400"
                        >
                            Social login
                        </p>
                        <p class="mt-3 text-3xl font-semibold">3 providers</p>
                        <p
                            class="mt-2 text-sm text-slate-500 dark:text-slate-400"
                        >
                            Google, Facebook, and GitHub ready when configured.
                        </p>
                    </div>
                    <div class="crm-subtle-panel">
                        <p
                            class="text-sm font-medium text-slate-500 dark:text-slate-400"
                        >
                            Deployment
                        </p>
                        <p class="mt-3 text-3xl font-semibold">SQLite first</p>
                        <p
                            class="mt-2 text-sm text-slate-500 dark:text-slate-400"
                        >
                            Simple today, portable to larger databases later.
                        </p>
                    </div>
                </div>
            </section>

            <section class="relative">
                <div
                    class="absolute inset-x-12 top-0 h-32 rounded-full bg-emerald-500/20 blur-3xl"
                />
                <div class="crm-panel relative rounded-[2rem] p-6">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div
                            class="rounded-[1.4rem] bg-slate-950 p-5 text-white dark:bg-slate-900"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-sm font-medium text-slate-300"
                                >
                                    Pipeline overview
                                </span>
                                <ChartColumnIncreasing
                                    class="size-4 text-emerald-300"
                                />
                            </div>
                            <p class="mt-8 text-4xl font-semibold">42</p>
                            <p class="mt-2 text-sm text-slate-300">
                                clients across lead, active, and completed work.
                            </p>
                            <div
                                class="mt-6 grid grid-cols-3 gap-3 text-center text-xs tracking-[0.2em] text-slate-400 uppercase"
                            >
                                <div class="rounded-2xl bg-white/5 px-3 py-3">
                                    <p class="text-lg font-semibold text-white">
                                        12
                                    </p>
                                    Leads
                                </div>
                                <div class="rounded-2xl bg-white/5 px-3 py-3">
                                    <p class="text-lg font-semibold text-white">
                                        18
                                    </p>
                                    Active
                                </div>
                                <div class="rounded-2xl bg-white/5 px-3 py-3">
                                    <p class="text-lg font-semibold text-white">
                                        5
                                    </p>
                                    Due soon
                                </div>
                            </div>
                        </div>

                        <div class="crm-subtle-panel">
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-sm font-medium text-slate-500 dark:text-slate-400"
                                >
                                    Recent activity
                                </span>
                                <NotebookPen class="size-4 text-slate-400" />
                            </div>
                            <div class="mt-5 space-y-4">
                                <div
                                    class="rounded-2xl bg-white p-4 shadow-sm dark:bg-slate-950"
                                >
                                    <p class="font-semibold">
                                        Northshore Studio
                                    </p>
                                    <p
                                        class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        Proposal sent. Follow-up scheduled for
                                        Thursday.
                                    </p>
                                </div>
                                <div
                                    class="rounded-2xl bg-white p-4 shadow-sm dark:bg-slate-950"
                                >
                                    <p class="font-semibold">Acme Electric</p>
                                    <p
                                        class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        New note added after onboarding call.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="crm-subtle-panel bg-white/90 dark:bg-slate-900/70"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="rounded-2xl bg-emerald-50 p-3 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-200"
                                >
                                    <Clock3 class="size-5" />
                                </div>
                                <div>
                                    <p class="font-semibold">
                                        Follow-up rhythm
                                    </p>
                                    <p
                                        class="text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        Stay proactive with due-soon visibility.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="crm-subtle-panel bg-white/90 dark:bg-slate-900/70"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="rounded-2xl bg-sky-50 p-3 text-sky-700 dark:bg-sky-500/10 dark:text-sky-200"
                                >
                                    <ShieldCheck class="size-5" />
                                </div>
                                <div>
                                    <p class="font-semibold">
                                        Secure by default
                                    </p>
                                    <p
                                        class="text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        Verified accounts, reset flows, and
                                        throttled login.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
