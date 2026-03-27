<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { home } from '@/routes';

const props = defineProps<{
    status: number;
}>();

const goBack = () => {
    window.history.back();
};

const copy = {
    403: {
        title: 'Access denied',
        description:
            'You do not have permission to open this page. If this seems unexpected, head back to safety and try again from your account.',
    },
    404: {
        title: 'Page not found',
        description:
            'That page is not available anymore, or the address is incorrect.',
    },
    500: {
        title: 'Something went wrong',
        description:
            'The app hit an unexpected issue. Try again in a moment or return to the dashboard.',
    },
    503: {
        title: 'Temporarily unavailable',
        description:
            'CRM Lite is briefly unavailable while the application recovers or updates.',
    },
}[props.status] ?? {
    title: 'Unexpected error',
    description: 'An unexpected issue interrupted the request.',
};
</script>

<template>
    <Head :title="copy.title" />

    <div class="flex min-h-screen items-center justify-center px-6 py-12">
        <div
            class="w-full max-w-2xl rounded-[2rem] border border-slate-200/80 bg-white/90 p-8 text-center shadow-[0_32px_80px_-48px_rgba(15,23,42,0.55)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/88"
        >
            <p
                class="text-sm font-semibold tracking-[0.24em] text-emerald-600 uppercase dark:text-emerald-300"
            >
                Error {{ status }}
            </p>
            <h1
                class="mt-4 text-4xl font-semibold tracking-tight text-slate-950 dark:text-white"
            >
                {{ copy.title }}
            </h1>
            <p
                class="mx-auto mt-4 max-w-xl text-base leading-7 text-slate-500 dark:text-slate-400"
            >
                {{ copy.description }}
            </p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <Button
                    as-child
                    class="rounded-xl bg-slate-950 px-5 hover:bg-slate-800 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-100"
                >
                    <Link :href="home()">Return home</Link>
                </Button>
                <Button variant="outline" class="rounded-xl" @click="goBack">
                    Go back
                </Button>
            </div>
        </div>
    </div>
</template>
