<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ChartColumnIncreasing, ShieldCheck, Sparkles } from 'lucide-vue-next';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

const page = usePage();
const name = page.props.name;

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative min-h-dvh overflow-hidden bg-[linear-gradient(180deg,_#f8fafc,_#ecfeff_55%,_#ffffff)] lg:grid lg:grid-cols-[1.1fr_0.9fr] dark:bg-[linear-gradient(180deg,_#020617,_#0f172a_55%,_#020617)]"
    >
        <div
            class="relative hidden h-full overflow-hidden p-12 text-white lg:flex lg:p-14"
        >
            <div
                class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.45),_transparent_32%),radial-gradient(circle_at_bottom_left,_rgba(20,184,166,0.26),_transparent_38%),linear-gradient(160deg,_#0f172a,_#020617_62%)]"
            />
            <div
                class="absolute top-16 -right-20 h-72 w-72 rounded-full bg-emerald-400/20 blur-3xl"
            />
            <div
                class="absolute right-0 bottom-0 left-0 h-56 bg-[linear-gradient(180deg,_transparent,_rgba(15,23,42,0.6))]"
            />
            <Link
                :href="home()"
                class="relative z-20 flex items-center text-lg font-semibold"
            >
                <div class="mr-3 rounded-2xl bg-white/10 p-2 backdrop-blur">
                    <AppLogoIcon class="size-8 fill-current text-white" />
                </div>
                {{ name }}
            </Link>
            <div class="relative z-20 mt-auto max-w-xl">
                <span
                    class="inline-flex rounded-full border border-white/15 bg-white/10 px-4 py-1 text-xs font-semibold tracking-[0.32em] text-emerald-100 uppercase backdrop-blur"
                >
                    Freelance CRM
                </span>
                <h1
                    class="mt-6 text-5xl leading-tight font-semibold tracking-tight"
                >
                    Keep clients moving without turning your workflow into
                    chaos.
                </h1>
                <p class="mt-5 max-w-lg text-base leading-7 text-slate-300">
                    CRM Lite gives solo operators a calm, fast home for leads,
                    follow-ups, notes, and account activity with the polish of a
                    modern SaaS product.
                </p>

                <div class="mt-10 grid gap-4 text-sm text-slate-200">
                    <div
                        class="flex items-start gap-3 rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur"
                    >
                        <ChartColumnIncreasing
                            class="mt-0.5 size-5 text-emerald-300"
                        />
                        <div>
                            <p class="font-semibold text-white">
                                Pipeline clarity
                            </p>
                            <p class="mt-1 text-slate-300">
                                Track leads, active work, and follow-ups in one
                                place.
                            </p>
                        </div>
                    </div>
                    <div
                        class="flex items-start gap-3 rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur"
                    >
                        <Sparkles class="mt-0.5 size-5 text-cyan-300" />
                        <div>
                            <p class="font-semibold text-white">
                                Smooth onboarding
                            </p>
                            <p class="mt-1 text-slate-300">
                                Email sign-in plus social login for Google,
                                Facebook, and GitHub.
                            </p>
                        </div>
                    </div>
                    <div
                        class="flex items-start gap-3 rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur"
                    >
                        <ShieldCheck class="mt-0.5 size-5 text-sky-300" />
                        <div>
                            <p class="font-semibold text-white">
                                Safer defaults
                            </p>
                            <p class="mt-1 text-slate-300">
                                Verified accounts, throttled auth, and clear
                                recovery flows built in.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div
            class="relative flex items-center justify-center px-6 py-10 lg:px-10 xl:px-14"
        >
            <div class="w-full max-w-xl">
                <div
                    class="crm-panel rounded-[2rem] border-white/70 bg-white/92 p-6 xl:p-10 dark:border-slate-800 dark:bg-slate-950/88"
                >
                    <Link
                        :href="home()"
                        class="mb-8 inline-flex items-center gap-3 text-left lg:hidden"
                    >
                        <div
                            class="rounded-2xl bg-slate-950 p-2 text-white dark:bg-white dark:text-slate-950"
                        >
                            <AppLogoIcon class="size-6 fill-current" />
                        </div>
                        <div>
                            <p
                                class="font-semibold text-slate-950 dark:text-white"
                            >
                                {{ name }}
                            </p>
                            <p
                                class="text-sm text-slate-500 dark:text-slate-400"
                            >
                                Client relationships, simplified
                            </p>
                        </div>
                    </Link>

                    <div class="flex flex-col space-y-2 text-left">
                        <p
                            class="text-sm font-semibold tracking-[0.24em] text-emerald-600 uppercase dark:text-emerald-300"
                        >
                            Welcome back
                        </p>
                        <h1
                            class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white"
                            v-if="title"
                        >
                            {{ title }}
                        </h1>
                        <p
                            class="text-sm leading-6 text-slate-500 dark:text-slate-400"
                            v-if="description"
                        >
                            {{ description }}
                        </p>
                    </div>
                    <div class="mt-8">
                        <slot />
                    </div>
                </div>
                <p
                    class="mt-6 text-center text-xs tracking-[0.24em] text-slate-400 uppercase"
                >
                    Organize clients. Follow through faster.
                </p>
            </div>
        </div>
    </div>
</template>
