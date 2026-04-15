<script setup lang="ts">
import { AlertTriangle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const props = withDefaults(
    defineProps<{
        open: boolean;
        title: string;
        description: string;
        confirmLabel?: string;
        cancelLabel?: string;
        loading?: boolean;
    }>(),
    {
        confirmLabel: 'Confirm',
        cancelLabel: 'Cancel',
        loading: false,
    },
);

const emit = defineEmits<{
    'update:open': [value: boolean];
    confirm: [];
}>();
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="overflow-hidden rounded-[1.75rem] border-rose-200/80 p-0 shadow-[0_30px_90px_-40px_rgba(127,29,29,0.45)] dark:border-rose-500/25">
            <div class="bg-[radial-gradient(circle_at_top_left,_rgba(244,63,94,0.16),_transparent_34%),linear-gradient(180deg,_#fff7f7,_#ffffff)] p-6 dark:bg-[radial-gradient(circle_at_top_left,_rgba(244,63,94,0.18),_transparent_34%),linear-gradient(180deg,_#1e0b12,_#020617)]">
                <DialogHeader class="space-y-4">
                    <div class="flex items-start gap-4">
                        <div class="rounded-2xl bg-rose-100 p-3 text-rose-700 ring-8 ring-rose-100/45 dark:bg-rose-500/15 dark:text-rose-200 dark:ring-rose-500/10">
                            <AlertTriangle class="size-6" />
                        </div>
                        <div class="min-w-0">
                            <DialogTitle class="text-xl font-semibold tracking-tight text-slate-950 dark:text-white">
                                {{ title }}
                            </DialogTitle>
                            <DialogDescription class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                                {{ description }}
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                <div class="mt-5 rounded-2xl border border-rose-200/80 bg-white/70 p-4 text-sm leading-6 text-rose-700 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-100">
                    Please review this carefully. Once you confirm, this change may not be easy to undo.
                </div>
            </div>

            <DialogFooter class="gap-2 border-t border-slate-200/80 bg-white px-6 py-4 dark:border-slate-800 dark:bg-slate-950">
                <DialogClose as-child>
                    <Button variant="outline" :disabled="props.loading">
                        {{ cancelLabel }}
                    </Button>
                </DialogClose>
                <Button
                    variant="destructive"
                    :disabled="props.loading"
                    @click="emit('confirm')"
                >
                    {{ confirmLabel }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
