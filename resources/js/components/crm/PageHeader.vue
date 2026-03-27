<script setup lang="ts">
defineProps<{
    title: string;
    description?: string;
    eyebrow?: string;
}>();
</script>

<template>
    <section class="crm-page-header">
        <div class="crm-page-heading">
            <div
                v-if="$slots.meta || eyebrow"
                class="flex flex-wrap items-center gap-3"
            >
                <slot name="meta">
                    <p class="crm-eyebrow">
                        {{ eyebrow }}
                    </p>
                </slot>
            </div>

            <h1 class="crm-page-title">
                {{ title }}
            </h1>

            <p v-if="description" class="crm-page-description">
                {{ description }}
            </p>
        </div>

        <div v-if="$slots.actions" class="crm-page-actions">
            <slot name="actions" />
        </div>
    </section>
</template>
