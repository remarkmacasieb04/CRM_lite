<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import type { ClientDetail, ClientStatusOption, ClientTag } from '@/types';

type ClientFormAction = {
    url: string;
    method: 'post' | 'patch';
};

const props = defineProps<{
    action: ClientFormAction;
    cancelHref: string;
    client?: Partial<ClientDetail>;
    statusOptions: ClientStatusOption[];
    availableTags: ClientTag[];
    submitLabel: string;
}>();

const form = useForm({
    name: props.client?.name ?? '',
    company: props.client?.company ?? '',
    email: props.client?.email ?? '',
    phone: props.client?.phone ?? '',
    status: props.client?.status ?? props.statusOptions[0]?.value ?? 'lead',
    budget: props.client?.budget ?? '',
    source: props.client?.source ?? '',
    last_contacted_at: props.client?.last_contacted_at ?? '',
    follow_up_at: props.client?.follow_up_at ?? '',
    tags: props.client?.tags?.map((tag) => tag.name).join(', ') ?? '',
});

const submit = () => {
    form.submit(props.action.method, props.action.url, {
        preserveScroll: true,
    });
};
</script>

<template>
    <form class="space-y-7" @submit.prevent="submit">
        <section class="grid gap-5 md:grid-cols-2">
            <div class="grid gap-2 md:col-span-2">
                <Label for="name">Client name</Label>
                <Input
                    id="name"
                    v-model="form.name"
                    class="h-11"
                    placeholder="Alex Carter"
                />
                <InputError :message="form.errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="company">Company</Label>
                <Input
                    id="company"
                    v-model="form.company"
                    placeholder="Northshore Studio"
                />
                <InputError :message="form.errors.company" />
            </div>

            <div class="grid gap-2">
                <Label for="status">Status</Label>
                <select id="status" v-model="form.status" class="crm-field">
                    <option
                        v-for="option in statusOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
                <InputError :message="form.errors.status" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="h-11"
                    placeholder="alex@example.com"
                />
                <InputError :message="form.errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="phone">Phone</Label>
                <Input
                    id="phone"
                    v-model="form.phone"
                    type="tel"
                    class="h-11"
                    placeholder="+1 (555) 123-4567"
                />
                <InputError :message="form.errors.phone" />
            </div>

            <div class="grid gap-2">
                <Label for="budget">Budget</Label>
                <Input
                    id="budget"
                    v-model="form.budget"
                    inputmode="decimal"
                    class="h-11"
                    placeholder="2500.00"
                />
                <InputError :message="form.errors.budget" />
            </div>

            <div class="grid gap-2">
                <Label for="source">Source</Label>
                <Input
                    id="source"
                    v-model="form.source"
                    class="h-11"
                    placeholder="Referral, website, LinkedIn..."
                />
                <InputError :message="form.errors.source" />
            </div>

            <div class="grid gap-2">
                <Label for="last_contacted_at">Last contacted</Label>
                <Input
                    id="last_contacted_at"
                    v-model="form.last_contacted_at"
                    type="date"
                    class="h-11"
                />
                <InputError :message="form.errors.last_contacted_at" />
            </div>

            <div class="grid gap-2">
                <Label for="follow_up_at">Follow-up date</Label>
                <Input
                    id="follow_up_at"
                    v-model="form.follow_up_at"
                    type="date"
                    class="h-11"
                />
                <InputError :message="form.errors.follow_up_at" />
            </div>

            <div class="grid gap-2 md:col-span-2">
                <Label for="tags">Tags</Label>
                <Input
                    id="tags"
                    v-model="form.tags"
                    list="client-tag-options"
                    class="h-11"
                    placeholder="Referral, Retainer, High value"
                />
                <datalist id="client-tag-options">
                    <option
                        v-for="tag in availableTags"
                        :key="tag.id"
                        :value="tag.name"
                    />
                </datalist>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Separate tags with commas so you can build flexible filters
                    like <strong>Referral</strong> or
                    <strong>Urgent</strong>.
                </p>
                <InputError :message="form.errors.tags" />
            </div>
        </section>

        <div class="flex flex-wrap items-center gap-3">
            <Button type="submit" class="px-5" :disabled="form.processing">
                <Spinner v-if="form.processing" />
                {{ submitLabel }}
            </Button>

            <Button variant="outline" as-child>
                <Link :href="cancelHref">Cancel</Link>
            </Button>
        </div>
    </form>
</template>
