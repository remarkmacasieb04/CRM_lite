<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';

const props = defineProps<{
    status: string | null;
    label?: string | null;
    archivedAt?: string | null;
    class?: string;
}>();

const tone = computed(() => {
    if (props.archivedAt) {
        return 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-200';
    }

    return (
        {
            lead: 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-200',
            proposal_sent:
                'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-200',
            active: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200',
            waiting:
                'bg-orange-100 text-orange-700 dark:bg-orange-500/15 dark:text-orange-200',
            completed:
                'bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-200',
        }[props.status ?? ''] ??
        'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200'
    );
});

const text = computed(() => {
    if (props.archivedAt) {
        return 'Archived';
    }

    return props.label ?? 'Unknown';
});
</script>

<template>
    <Badge
        variant="secondary"
        :class="
            cn(
                'rounded-full border-0 px-3 py-1 text-xs font-semibold shadow-none',
                tone,
                props.class,
            )
        "
    >
        {{ text }}
    </Badge>
</template>
