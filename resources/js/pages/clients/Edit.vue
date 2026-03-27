<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ClientForm from '@/components/crm/ClientForm.vue';
import PageHeader from '@/components/crm/PageHeader.vue';
import { edit, index, show, update } from '@/routes/clients';
import type { ClientDetail, ClientStatusOption } from '@/types';

defineProps<{
    client: ClientDetail;
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
                title: 'Edit client',
                href: edit(1),
            },
        ],
    },
});
</script>

<template>
    <Head :title="`Edit ${client.name}`" />

    <div class="crm-page">
        <PageHeader
            eyebrow="Edit client"
            :title="`Update ${client.name}`"
            description="Refine contact details, budget, and follow-up timing without losing the timeline attached to this client."
        />

        <section class="crm-panel crm-panel-body">
            <ClientForm
                :action="{ url: update.url(client.id), method: 'patch' }"
                :cancel-href="show.url(client.id)"
                :client="client"
                :status-options="statusOptions"
                submit-label="Save changes"
            />
        </section>
    </div>
</template>
