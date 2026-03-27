<script setup lang="ts">
import type { LucideIcon } from 'lucide-vue-next';
import { Card, CardContent } from '@/components/ui/card';

defineProps<{
    title: string;
    value: number | string;
    description: string;
    icon: LucideIcon;
}>();
</script>

<template>
    <Card
        class="overflow-hidden border-slate-200/80 bg-[linear-gradient(180deg,rgba(255,255,255,0.96),rgba(248,250,252,0.92))] shadow-[0_18px_40px_-30px_rgba(15,23,42,0.22)] dark:border-slate-800 dark:bg-[linear-gradient(180deg,rgba(15,23,42,0.88),rgba(2,6,23,0.92))]"
    >
        <CardContent class="flex min-h-[11.5rem] flex-col gap-4 p-5">
            <div class="flex items-start gap-4">
                <div
                    class="inline-flex size-13 shrink-0 items-center justify-center rounded-[1.15rem] bg-slate-950 text-white shadow-sm dark:bg-white dark:text-slate-950"
                >
                    <component :is="icon" class="size-5" />
                </div>

                <div class="min-w-0 space-y-1">
                    <p
                        class="text-4xl font-semibold tracking-[-0.05em] text-slate-950 dark:text-white"
                    >
                        {{ value }}
                    </p>
                    <p
                        class="text-sm font-medium text-slate-500 dark:text-slate-400"
                    >
                        {{ title }}
                    </p>
                </div>
            </div>

            <div
                class="mt-auto border-t border-slate-200/70 pt-3 text-sm leading-6 text-slate-500 dark:border-slate-800 dark:text-slate-400"
            >
                {{ description }}
            </div>
        </CardContent>
    </Card>
</template>
