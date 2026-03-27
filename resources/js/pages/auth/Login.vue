<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import SocialAuthButtons from '@/components/crm/SocialAuthButtons.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Log in to your workspace',
        description:
            'Pick up where you left off with your client pipeline, notes, and follow-ups.',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <Head title="Log in" />

    <div
        v-if="status"
        class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-100"
    >
        {{ status }}
    </div>

    <div class="space-y-6">
        <Form
            :action="store.url()"
            method="post"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-5">
                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="email@example.com"
                        class="h-11 rounded-xl"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Password</Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-sm text-emerald-700 dark:text-emerald-300"
                            :tabindex="5"
                        >
                            Forgot password?
                        </TextLink>
                    </div>
                    <PasswordInput
                        id="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="Password"
                        class="h-11 rounded-xl"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <Label
                        for="remember"
                        class="flex items-center space-x-3 text-sm text-slate-600 dark:text-slate-300"
                    >
                        <Checkbox id="remember" name="remember" :tabindex="3" />
                        <span>Remember me for this device</span>
                    </Label>
                </div>

                <Button
                    type="submit"
                    class="mt-2 h-11 w-full rounded-xl bg-slate-950 hover:bg-slate-800 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-100"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <Spinner v-if="processing" />
                    Log in
                </Button>
            </div>
        </Form>

        <SocialAuthButtons />

        <div
            class="text-center text-sm text-muted-foreground"
            v-if="canRegister"
        >
            Don't have an account?
            <TextLink
                :href="register()"
                class="text-emerald-700 dark:text-emerald-300"
                :tabindex="5"
                >Sign up</TextLink
            >
        </div>
    </div>
</template>
