<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    tasks: Object,
    tags: Array,
    filters: Object,
});
</script>

<template>
    <AppLayout>
        <!-- Page Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-white">Moje úlohy</h1>
                <p class="text-sm text-[#9BA1AE] mt-0.5">Prehľad a správa vašich denných úloh.</p>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="tasks.data.length === 0"
            class="rounded-2xl border border-dashed border-[#222634] bg-[#161922] p-12 text-center">
            <div
                class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-[#1D212C] text-[#9BA1AE] mb-4">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012-2m-6 9l2 2 4-4" />
                </svg>
            </div>
            <h3 class="text-sm font-medium text-white">Zatiaľ žiadne úlohy</h3>
            <p class="text-xs text-[#9BA1AE] mt-1">Vytvorte si svoju prvú úlohu a začnite pracovať.</p>
        </div>

        <!-- Task List Items -->
        <ul v-else class="space-y-2.5">
            <li v-for="task in tasks.data" :key="task.id"
                class="group relative flex items-start justify-between rounded-xl border border-[#222634] bg-[#161922] p-4 shadow-sm hover:border-[#635BFF]/50 transition-all">
                <div class="flex items-start gap-3.5">
                    <!-- Checkbox placeholder -->
                    <div
                        class="mt-0.5 h-4 w-4 rounded border border-[#222634] bg-[#0F1117] flex items-center justify-center">
                        <div v-if="task.is_completed" class="h-2 w-2 rounded-xs bg-[#635BFF]"></div>
                    </div>

                    <div>
                        <strong class="text-sm font-medium text-white block"
                            :class="{ 'text-[#9BA1AE] line-through font-normal': task.is_completed }">
                            {{ task.title }}
                        </strong>
                        <p v-if="task.description" class="mt-1 text-xs text-[#9BA1AE] leading-relaxed">
                            {{ task.description }}
                        </p>

                        <!-- Tags -->
                        <div v-if="task.tags.length" class="mt-2.5 flex flex-wrap gap-1.5">
                            <span v-for="tag in task.tags" :key="tag.id"
                                class="inline-flex items-center rounded-md bg-[#1D212C] px-2.5 py-0.5 text-[11px] font-medium text-[#9BA1AE]">
                                {{ tag.name }}
                            </span>
                        </div>
                    </div>
                </div>
            </li>
        </ul>

        <!-- Pagination -->
        <div v-if="tasks.links && tasks.links.length > 3" class="mt-6 flex items-center justify-center gap-1">
            <Link v-for="link in tasks.links" :key="link.label" :href="link.url ?? '#'"
                class="rounded-lg border px-3 py-1.5 text-xs font-medium transition-colors" :class="link.active
                    ? 'border-[#635BFF] bg-[#635BFF] text-white shadow-sm shadow-[#635BFF]/20'
                    : 'border-[#222634] bg-[#161922] text-[#9BA1AE] hover:bg-[#1D212C] hover:text-white'"
                :style="{ opacity: link.url ? 1 : 0.3, pointerEvents: link.url ? 'auto' : 'none' }"
                v-html="link.label" />
        </div>
    </AppLayout>
</template>