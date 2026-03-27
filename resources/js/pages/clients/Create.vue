<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ClientForm from '@/components/crm/ClientForm.vue';
import PageHeader from '@/components/crm/PageHeader.vue';
import { create, index, store } from '@/routes/clients';
import type { ClientStatusOption } from '@/types';

defineProps<{
    statusOptions: ClientStatusOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Clients',
                href: index(),
            },
            {
                title: 'New client',
                href: create(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Add client" />

    <div class="crm-page">
        <PageHeader
            eyebrow="New client"
            title="Capture a new relationship"
            description="Save the essentials now, then build the timeline with follow-ups and notes as the work progresses."
        />

        <section class="crm-panel crm-panel-body">
            <ClientForm
                :action="{ url: store.url(), method: 'post' }"
                :cancel-href="index.url()"
                :status-options="statusOptions"
                submit-label="Create client"
            />
        </section>
    </div>
</template>
