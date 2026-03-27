<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

defineOptions({
    layout: {
        title: 'Verify your email address',
        description:
            'Email verification keeps your account secure and unlocks the full CRM workspace.',
    },
});

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head title="Email verification" />

    <div
        v-if="status === 'verification-link-sent'"
        class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-100"
    >
        A new verification link has been sent to the email address you provided
        during registration.
    </div>

    <div
        class="space-y-4 rounded-[1.6rem] border border-slate-200 bg-slate-50/70 p-5 dark:border-slate-800 dark:bg-slate-900/70"
    >
        <p class="text-sm leading-6 text-slate-600 dark:text-slate-300">
            Before you continue, please confirm your email address. If the
            message has not arrived yet, you can resend it below.
        </p>

        <Form
            :action="send.url()"
            method="post"
            class="space-y-4"
            v-slot="{ processing }"
        >
            <Button
                :disabled="processing"
                class="h-11 w-full rounded-xl bg-slate-950 hover:bg-slate-800 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-100"
            >
                <Spinner v-if="processing" />
                Resend verification email
            </Button>

            <TextLink
                :href="logout()"
                as="button"
                class="mx-auto block text-sm text-emerald-700 dark:text-emerald-300"
            >
                Log out
            </TextLink>
        </Form>
    </div>
</template>
