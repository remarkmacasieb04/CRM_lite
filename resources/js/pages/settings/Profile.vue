<script setup lang="ts">
import { Form, Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import SocialProviderIcon from '@/components/crm/SocialProviderIcon.vue';
import UserRoleBadge from '@/components/crm/UserRoleBadge.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/profile';
import updateReminderPreferences from '@/routes/profile/reminders';
import social from '@/routes/social';
import { send } from '@/routes/verification';
import type { SocialProviderOption, User } from '@/types';

type Props = {
    mustVerifyEmail: boolean;
    status?: string;
};

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Account profile',
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user as User);
const socialProviders = computed(
    () => (page.props.socialProviders ?? []) as SocialProviderOption[],
);

const connectedProviderNames = computed(() =>
    user.value.connected_providers.map((provider) => provider.provider),
);

const canDisconnectProviders = computed(
    () =>
        user.value.email_verified_at !== null ||
        user.value.connected_providers.length > 1,
);

function disconnectProvider(providerName: string, label: string): void {
    if (
        !window.confirm(
            `Disconnect ${label} from your account? You can reconnect it later.`,
        )
    ) {
        return;
    }

    router.delete(social.destroy.url(providerName), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Account profile" />

    <h1 class="sr-only">Account profile</h1>

    <div class="space-y-10">
        <section class="space-y-4">
            <Heading
                variant="small"
                title="Account overview"
                description="Manage your profile details and connected sign-in methods."
            />

            <div class="grid gap-4 md:grid-cols-[1.1fr_0.9fr]">
                <div class="crm-panel crm-panel-body">
                    <p
                        class="text-sm font-medium text-slate-500 dark:text-slate-400"
                    >
                        Workspace identity
                    </p>
                    <div class="mt-4 flex items-start gap-4">
                        <div
                            class="inline-flex size-14 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-950 via-slate-800 to-emerald-500 text-lg font-semibold text-white dark:from-white dark:via-slate-100 dark:to-emerald-400 dark:text-slate-950"
                        >
                            {{ user.name.slice(0, 1) }}
                        </div>
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-3">
                                <p
                                    class="text-lg font-semibold text-slate-950 dark:text-white"
                                >
                                    {{ user.name }}
                                </p>
                                <UserRoleBadge
                                    :role="user.role"
                                    :label="user.role_label"
                                    :show-prefix="true"
                                />
                            </div>
                            <p
                                class="text-sm text-slate-500 dark:text-slate-400"
                            >
                                {{ user.email }}
                            </p>
                            <p
                                class="text-xs tracking-[0.22em] text-slate-400 uppercase"
                            >
                                {{
                                    user.email_verified_at
                                        ? 'Verified email'
                                        : 'Verification pending'
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="crm-panel crm-panel-body">
                    <p
                        class="text-sm font-medium text-slate-500 dark:text-slate-400"
                    >
                        Connected providers
                    </p>
                    <p
                        class="mt-2 text-3xl font-semibold text-slate-950 dark:text-white"
                    >
                        {{ user.connected_providers.length }}
                    </p>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        Sign in methods currently attached to this account.
                    </p>
                </div>
            </div>
        </section>

        <div class="space-y-6">
            <Heading
                variant="small"
                title="Profile information"
                description="Update your name and primary email address."
            />

            <Form
                :action="ProfileController.update.url()"
                method="patch"
                class="crm-panel crm-panel-body space-y-6"
                v-slot="{ errors, processing, recentlySuccessful }"
            >
                <div class="grid gap-5 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="name">Full name</Label>
                        <Input
                            id="name"
                            class="h-11 rounded-xl"
                            name="name"
                            :default-value="user.name"
                            required
                            autocomplete="name"
                            placeholder="Full name"
                        />
                        <InputError class="mt-1" :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            class="h-11 rounded-xl"
                            name="email"
                            :default-value="user.email"
                            required
                            autocomplete="username"
                            placeholder="Email address"
                        />
                        <InputError class="mt-1" :message="errors.email" />
                    </div>
                </div>

                <div
                    v-if="mustVerifyEmail && !user.email_verified_at"
                    class="rounded-[1.4rem] border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-800 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-100"
                >
                    <p>
                        Your email address is not verified yet.
                        <Link
                            :href="send()"
                            as="button"
                            class="font-semibold underline underline-offset-4"
                        >
                            Resend verification email
                        </Link>
                    </p>

                    <p
                        v-if="status === 'verification-link-sent'"
                        class="mt-2 font-medium"
                    >
                        A fresh verification link has been sent.
                    </p>
                </div>

                <div class="flex items-center gap-4">
                    <Button
                        :disabled="processing"
                        data-test="update-profile-button"
                    >
                        Save profile
                    </Button>

                    <Transition
                        enter-active-class="transition ease-in-out"
                        enter-from-class="opacity-0"
                        leave-active-class="transition ease-in-out"
                        leave-to-class="opacity-0"
                    >
                        <p
                            v-show="recentlySuccessful"
                            class="text-sm text-slate-500 dark:text-slate-400"
                        >
                            Saved.
                        </p>
                    </Transition>
                </div>
            </Form>
        </div>

        <div class="space-y-6">
            <Heading
                variant="small"
                title="Social sign-in"
                description="Connect available providers for faster login on future visits."
            />

            <div class="grid gap-4">
                <div
                    v-for="provider in socialProviders"
                    :key="provider.name"
                    class="crm-panel crm-panel-body flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                >
                    <div class="flex items-center gap-4">
                        <SocialProviderIcon
                            :provider="provider.name"
                            class="h-10 min-w-10 text-sm"
                        />
                        <div>
                            <p
                                class="font-semibold text-slate-950 dark:text-white"
                            >
                                {{ provider.label }}
                            </p>
                            <p
                                v-if="
                                    connectedProviderNames.includes(
                                        provider.name,
                                    )
                                "
                                class="text-sm text-slate-500 dark:text-slate-400"
                            >
                                {{
                                    user.connected_providers.find(
                                        (item) =>
                                            item.provider === provider.name,
                                    )?.provider_email ||
                                    'Connected to this account'
                                }}
                            </p>
                            <p
                                v-else
                                class="text-sm text-slate-500 dark:text-slate-400"
                            >
                                Not connected yet.
                            </p>
                            <p
                                v-if="
                                    connectedProviderNames.includes(
                                        provider.name,
                                    ) && !canDisconnectProviders
                                "
                                class="mt-2 text-xs font-medium text-amber-700 dark:text-amber-200"
                            >
                                Verify your email or connect another provider
                                before disconnecting this one.
                            </p>
                        </div>
                    </div>

                    <Button
                        v-if="!connectedProviderNames.includes(provider.name)"
                        as-child
                    >
                        <Link :href="social.redirect(provider.name)">
                            Connect {{ provider.label }}
                        </Link>
                    </Button>

                    <div
                        v-else
                        class="flex flex-col items-start gap-2 md:items-end"
                    >
                        <div
                            class="rounded-full bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-100"
                        >
                            Connected
                        </div>

                        <Button
                            variant="outline"
                            :disabled="!canDisconnectProviders"
                            @click="
                                disconnectProvider(
                                    provider.name,
                                    provider.label,
                                )
                            "
                        >
                            Disconnect
                        </Button>
                    </div>
                </div>

                <div
                    v-if="socialProviders.length === 0"
                    class="crm-subtle-panel border-dashed text-sm text-slate-500 dark:text-slate-400"
                >
                    Social login buttons stay hidden until provider keys are
                    configured in your environment.
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <Heading
                variant="small"
                title="Reminder emails"
                description="Choose whether CRM Lite should email you a follow-up digest when clients need attention."
            />

            <Form
                :action="updateReminderPreferences.update.url()"
                method="patch"
                class="space-y-5 rounded-[1.6rem] border border-slate-200/80 bg-white/90 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950/80"
                v-slot="{ processing }"
            >
                <label class="flex items-start gap-3">
                    <input
                        type="checkbox"
                        name="receives_follow_up_reminders"
                        value="1"
                        :checked="user.receives_follow_up_reminders"
                        class="mt-1 size-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                    />
                    <span class="space-y-1">
                        <span class="block font-medium text-slate-950 dark:text-white">
                            Send me daily follow-up reminders
                        </span>
                        <span class="block text-sm text-slate-500 dark:text-slate-400">
                            Best for staying on top of overdue and upcoming follow-ups without logging in first.
                        </span>
                    </span>
                </label>

                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Last digest sent:
                    {{ user.last_follow_up_digest_sent_at || 'No digest has been sent yet.' }}
                </p>

                <Button
                    :disabled="processing"
                    class="rounded-xl bg-slate-950 hover:bg-slate-800 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-100"
                >
                    Save reminder preference
                </Button>
            </Form>
        </div>
    </div>

    <DeleteUser />
</template>
