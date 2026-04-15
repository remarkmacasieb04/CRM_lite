<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    ChartColumnIncreasing,
    Clock3,
    NotebookPen,
    ShieldCheck,
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
    <Head title="CRM Lite">
        <link rel="icon" href="/CRM-lite-logo.ico" sizes="any" />
        <link rel="icon" href="/CRM-lite-logo.svg" type="image/svg+xml" />
    </Head>

    <div
        class="relative min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.35),_transparent_28%),radial-gradient(circle_at_85%_15%,_rgba(20,184,166,0.18),_transparent_20%),linear-gradient(160deg,_#0f172a,_#020617_58%,_#022c22)] text-white"
    >
        <div
            class="absolute top-10 left-1/2 h-72 w-72 -translate-x-1/2 rounded-full bg-emerald-400/10 blur-3xl"
        />
        <div
            class="absolute -right-20 bottom-10 h-80 w-80 rounded-full bg-cyan-400/10 blur-3xl"
        />

        <header
            class="relative z-10 mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-6 lg:px-8"
        >
            <Link
                :href="home()"
                class="flex items-center gap-4 rounded-[1.75rem] border border-white/10 bg-white/8 px-4 py-3 shadow-lg shadow-black/20 backdrop-blur-md"
            >
                <div class="rounded-2xl bg-white/10 p-2.5 ring-1 ring-white/10">
                    <img
                        src="/CRM-lite-logo.svg"
                        alt="CRM Lite logo"
                        class="size-10 rounded-xl object-contain"
                    />
                </div>
                <div>
                    <p
                        class="text-sm font-semibold tracking-[0.22em] text-emerald-300 uppercase"
                    >
                        CRM Lite
                    </p>
                    <p class="text-sm text-slate-300">
                        Client relationships, simplified
                    </p>
                </div>
            </Link>

            <nav class="flex items-center gap-3">
                <template v-if="isAuthenticated">
                    <Button
                        as-child
                        class="rounded-full border border-white/10 bg-white/10 px-5 text-white hover:bg-white/15"
                    >
                        <Link :href="dashboard()">
                            Open dashboard
                            <ArrowRight class="size-4" />
                        </Link>
                    </Button>
                </template>
                <template v-else>
                    <Button
                        variant="ghost"
                        as-child
                        class="rounded-full px-4 text-white hover:bg-white/10 hover:text-white"
                    >
                        <Link :href="login()">Log in</Link>
                    </Button>
                    <Button
                        v-if="canRegister"
                        as-child
                        class="rounded-full bg-emerald-500 px-5 text-slate-950 hover:bg-emerald-400"
                    >
                        <Link :href="register()">Start free</Link>
                    </Button>
                </template>
            </nav>
        </header>

        <main
            class="relative z-10 mx-auto grid max-w-7xl gap-16 px-6 pt-8 pb-16 lg:grid-cols-[1.02fr_0.98fr] lg:px-8 lg:pt-14"
        >
            <section class="max-w-2xl">
                <span
                    class="inline-flex rounded-full border border-white/15 bg-white/8 px-4 py-1.5 text-xs font-semibold tracking-[0.28em] text-emerald-100 uppercase backdrop-blur"
                >
                    Freelance CRM
                </span>
                <h1
                    class="mt-6 text-5xl font-semibold tracking-tight text-white sm:text-6xl xl:text-7xl"
                >
                    Look sharper, follow up faster, and keep every client
                    moving.
                </h1>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-300">
                    CRM Lite gives freelancers and small service teams a calm,
                    premium-looking workspace for leads, proposals, follow-ups,
                    notes, and delivery. Spend less time chasing details and
                    more time looking organized in front of clients.
                </p>

                <div
                    class="mt-8 grid gap-3 text-sm text-slate-200 sm:grid-cols-2"
                >
                    <div
                        class="rounded-2xl border border-white/10 bg-white/6 px-4 py-3 backdrop-blur"
                    >
                        Follow every inquiry from first message to paid work.
                    </div>
                    <div
                        class="rounded-2xl border border-white/10 bg-white/6 px-4 py-3 backdrop-blur"
                    >
                        Keep proposals, next steps, and notes in one place.
                    </div>
                </div>

                <div class="mt-10 flex flex-wrap gap-4">
                    <Button
                        v-if="!isAuthenticated && canRegister"
                        as-child
                        size="lg"
                        class="rounded-full bg-emerald-500 px-6 text-slate-950 hover:bg-emerald-400"
                    >
                        <Link :href="register()">
                            Start your client workspace
                            <ArrowRight class="size-4" />
                        </Link>
                    </Button>
                    <Button
                        v-else
                        as-child
                        size="lg"
                        class="rounded-full bg-emerald-500 px-6 text-slate-950 hover:bg-emerald-400"
                    >
                        <Link :href="dashboard()">Go to dashboard</Link>
                    </Button>
                    <Button
                        variant="outline"
                        as-child
                        size="lg"
                        class="rounded-full border-white/15 bg-white/8 px-6 text-white hover:bg-white/12 hover:text-white"
                    >
                        <Link :href="login()">See the sign-in flow</Link>
                    </Button>
                </div>

                <div class="mt-12 grid gap-4 sm:grid-cols-3">
                    <div
                        class="rounded-[1.75rem] border border-white/10 bg-white/8 p-5 backdrop-blur"
                    >
                        <p class="text-sm font-medium text-slate-300">
                            Response window
                        </p>
                        <p class="mt-3 text-3xl font-semibold text-white">
                            Due today
                        </p>
                        <p class="mt-2 text-sm text-slate-300">
                            See follow-ups before they go cold and miss your
                            next opportunity.
                        </p>
                    </div>
                    <div
                        class="rounded-[1.75rem] border border-white/10 bg-white/8 p-5 backdrop-blur"
                    >
                        <p class="text-sm font-medium text-slate-300">
                            Cleaner handoff
                        </p>
                        <p class="mt-3 text-3xl font-semibold text-white">
                            Notes + status
                        </p>
                        <p class="mt-2 text-sm text-slate-300">
                            Keep context on every client so you always know what
                            was promised.
                        </p>
                    </div>
                    <div
                        class="rounded-[1.75rem] border border-white/10 bg-white/8 p-5 backdrop-blur"
                    >
                        <p class="text-sm font-medium text-slate-300">
                            Professional trust
                        </p>
                        <p class="mt-3 text-3xl font-semibold text-white">
                            Secure access
                        </p>
                        <p class="mt-2 text-sm text-slate-300">
                            Verified accounts and safer defaults built in from
                            day one.
                        </p>
                    </div>
                </div>
            </section>

            <section class="relative">
                <div
                    class="absolute inset-x-12 top-0 h-32 rounded-full bg-emerald-500/20 blur-3xl"
                />
                <div
                    class="relative rounded-[2rem] border border-white/10 bg-white/8 p-6 shadow-2xl shadow-black/25 backdrop-blur-xl"
                >
                    <div
                        class="absolute inset-0 rounded-[2rem] bg-[linear-gradient(180deg,_rgba(255,255,255,0.05),_rgba(255,255,255,0.02))]"
                    />
                    <div class="grid gap-4 md:grid-cols-2">
                        <div
                            class="relative rounded-[1.6rem] bg-[linear-gradient(180deg,_#020617,_#0b1120)] p-5 text-white ring-1 ring-white/6"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-sm font-medium text-slate-300"
                                >
                                    Studio pipeline
                                </span>
                                <ChartColumnIncreasing
                                    class="size-4 text-emerald-300"
                                />
                            </div>
                            <p class="mt-8 text-4xl font-semibold">24</p>
                            <p class="mt-2 text-sm text-slate-300">
                                conversations and active jobs tracked without
                                sticky notes or spreadsheets.
                            </p>
                            <div
                                class="mt-6 grid grid-cols-3 gap-3 text-center text-xs tracking-[0.2em] text-slate-400 uppercase"
                            >
                                <div class="rounded-2xl bg-white/5 px-3 py-3">
                                    <p class="text-lg font-semibold text-white">
                                        8
                                    </p>
                                    Leads
                                </div>
                                <div class="rounded-2xl bg-white/5 px-3 py-3">
                                    <p class="text-lg font-semibold text-white">
                                        11
                                    </p>
                                    Active
                                </div>
                                <div class="rounded-2xl bg-white/5 px-3 py-3">
                                    <p class="text-lg font-semibold text-white">
                                        3
                                    </p>
                                    Awaiting reply
                                </div>
                            </div>
                        </div>

                        <div
                            class="relative rounded-[1.6rem] border border-white/10 bg-white/8 p-5 backdrop-blur"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-sm font-medium text-slate-300"
                                >
                                    Recent activity
                                </span>
                                <NotebookPen class="size-4 text-slate-400" />
                            </div>
                            <div class="mt-5 space-y-4">
                                <div
                                    class="rounded-2xl border border-white/10 bg-white/95 p-4 text-slate-950 shadow-sm"
                                >
                                    <p class="font-semibold">
                                        Northshore Studio
                                    </p>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Proposal delivered. Follow-up is queued
                                        for Thursday morning.
                                    </p>
                                </div>
                                <div
                                    class="rounded-2xl border border-white/10 bg-white/95 p-4 text-slate-950 shadow-sm"
                                >
                                    <p class="font-semibold">Acme Electric</p>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Kickoff notes saved right after the
                                        onboarding call.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="relative rounded-[1.6rem] border border-white/10 bg-white/8 p-5 backdrop-blur"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="rounded-2xl bg-emerald-500/12 p-3 text-emerald-300"
                                >
                                    <Clock3 class="size-5" />
                                </div>
                                <div>
                                    <p class="font-semibold text-white">
                                        Follow-up rhythm
                                    </p>
                                    <p class="text-sm text-slate-300">
                                        Build trust with replies that feel
                                        timely, not rushed.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="relative rounded-[1.6rem] border border-white/10 bg-white/8 p-5 backdrop-blur"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="rounded-2xl bg-sky-500/12 p-3 text-sky-300"
                                >
                                    <ShieldCheck class="size-5" />
                                </div>
                                <div>
                                    <p class="font-semibold text-white">
                                        Secure by default
                                    </p>
                                    <p class="text-sm text-slate-300">
                                        Verified accounts, safer resets, and
                                        throttled sign-in built in.
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
