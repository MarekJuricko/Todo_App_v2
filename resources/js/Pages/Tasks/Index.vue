<script setup>
import { ref, watch } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    tasks: Object,
    tags: Array,
    filters: Object,
});

// --- Filtre a vyhľadávanie ---
const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const tag = ref(props.filters.tag ?? '');
let searchTimeout = null;

function applyFilters() {
    router.get('/', {
        search: search.value || undefined,
        status: status.value || undefined,
        tag: tag.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400);
});

watch([status, tag], applyFilters);

function clearFilters() {
    search.value = '';
    status.value = '';
    tag.value = '';
}

// --- Create new task ---
const showCreateForm = ref(false);

const createForm = useForm({
    title: '',
    description: '',
});

function submitCreate() {
    createForm.post('/tasks', {
        preserveScroll: true,
        onSuccess: () => {
            createForm.reset();
            showCreateForm.value = false;
        },
    });
}

// --- Edit existing task ---
const editingTaskId = ref(null);

const editForm = useForm({
    title: '',
    description: '',
});

function startEdit(task) {
    editingTaskId.value = task.id;
    editForm.title = task.title;
    editForm.description = task.description ?? '';
}

function cancelEdit() {
    editingTaskId.value = null;
}

function submitEdit(task) {
    editForm.put(`/tasks/${task.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingTaskId.value = null;
        },
    });
}

// --- Completion and deletion ---
function toggleComplete(task) {
    router.patch(`/tasks/${task.id}/complete`, {}, { preserveScroll: true });
}

function destroyTask(task) {
    if (confirm(`Naozaj chceš zmazať úlohu „${task.title}“?`)) {
        router.delete(`/tasks/${task.id}`, { preserveScroll: true });
    }
}

// --- Tags ---
const tagInputs = ref({}); // { [taskId]: 'text' }

function attachTag(task) {
    const name = (tagInputs.value[task.id] ?? '').trim();
    if (!name) return;

    router.post(`/tasks/${task.id}/tags`, { name }, {
        preserveScroll: true,
        onSuccess: () => {
            tagInputs.value[task.id] = '';
        },
    });
}

function detachTag(task, tag) {
    router.delete(`/tasks/${task.id}/tags/${tag.id}`, { preserveScroll: true });
}
</script>

<template>
    <AppLayout>
        <!-- Page Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-3">
                    Moje úlohy
                    <span
                        class="rounded-full bg-[#635BFF]/10 px-2.5 py-0.5 text-xs font-semibold text-[#635BFF] border border-[#635BFF]/20">
                        {{ tasks.total ?? 0 }}
                    </span>
                </h1>
                <p class="text-sm text-[#9BA1AE] mt-1">Prehľad a správa vašich denných úloh na jednom mieste.</p>
            </div>

            <button @click="showCreateForm = !showCreateForm"
                class="inline-flex items-center gap-2 rounded-xl bg-[#635BFF] px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[#635BFF]/25 hover:bg-[#5146E5] active:scale-[0.98] transition-all cursor-pointer">
                <span class="text-base leading-none">{{ showCreateForm ? '×' : '+' }}</span>
                <span>{{ showCreateForm ? 'Zrušiť' : 'Nová úloha' }}</span>
            </button>
        </div>

        <!-- Filters Bar -->
        <div
            class="mb-6 flex flex-wrap items-center gap-3 rounded-2xl border border-[#222634] bg-[#161922]/60 p-2 backdrop-blur-md">
            <div class="relative flex-1 min-w-[220px]">
                <span class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none text-[#9BA1AE]/50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input v-model="search" type="text" placeholder="Hľadať v názve alebo popise..."
                    class="w-full rounded-xl border border-[#222634] bg-[#0F1117] pl-10 pr-4 py-2.5 text-sm text-white placeholder:text-[#9BA1AE]/40 focus:border-[#635BFF] focus:ring-2 focus:ring-[#635BFF]/20 focus:outline-none transition-all">
            </div>

            <select v-model="status"
                class="rounded-xl border border-[#222634] bg-[#0F1117] px-3.5 py-2.5 text-sm text-[#9BA1AE] focus:text-white focus:border-[#635BFF] focus:ring-2 focus:ring-[#635BFF]/20 focus:outline-none transition-all cursor-pointer">
                <option value="">Všetky stavy</option>
                <option value="pending">Rozpracované</option>
                <option value="completed">Dokončené</option>
            </select>

            <select v-model="tag"
                class="rounded-xl border border-[#222634] bg-[#0F1117] px-3.5 py-2.5 text-sm text-[#9BA1AE] focus:text-white focus:border-[#635BFF] focus:ring-2 focus:ring-[#635BFF]/20 focus:outline-none transition-all cursor-pointer">
                <option value="">Všetky tagy</option>
                <option v-for="t in tags" :key="t.id" :value="t.id">{{ t.name }}</option>
            </select>

            <button v-if="search || status || tag" @click="clearFilters" type="button"
                class="rounded-xl px-3.5 py-2.5 text-sm font-medium text-[#9BA1AE] hover:bg-[#1D212C] hover:text-white transition-colors cursor-pointer">
                Vymazať filtre
            </button>
        </div>

        <!-- Create form -->
        <div v-if="showCreateForm"
            class="mb-6 rounded-2xl border border-[#635BFF]/30 bg-[#161922] p-5 shadow-2xl shadow-[#635BFF]/10 animate-in fade-in slide-in-from-top-2 duration-200">
            <h3 class="text-sm font-semibold text-white mb-3 flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-[#635BFF]"></span>
                Vytvoriť novú úlohu
            </h3>
            <form @submit.prevent="submitCreate" class="space-y-3.5">
                <div>
                    <input v-model="createForm.title" type="text" placeholder="Názov úlohy..."
                        class="w-full rounded-xl border border-[#222634] bg-[#0F1117] px-4 py-3 text-sm text-white placeholder:text-[#9BA1AE]/40 focus:border-[#635BFF] focus:ring-2 focus:ring-[#635BFF]/20 focus:outline-none transition-all">
                    <div v-if="createForm.errors.title" class="mt-1.5 text-xs text-red-400 font-medium">
                        {{ createForm.errors.title }}
                    </div>
                </div>

                <div>
                    <textarea v-model="createForm.description" placeholder="Popis úlohy (voliteľný)..." rows="2"
                        class="w-full rounded-xl border border-[#222634] bg-[#0F1117] px-4 py-3 text-sm text-white placeholder:text-[#9BA1AE]/40 focus:border-[#635BFF] focus:ring-2 focus:ring-[#635BFF]/20 focus:outline-none transition-all resize-none"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-1">
                    <button type="button" @click="showCreateForm = false"
                        class="rounded-xl px-4 py-2 text-sm font-medium text-[#9BA1AE] hover:bg-[#1D212C] hover:text-white transition-colors cursor-pointer">
                        Zrušiť
                    </button>
                    <button type="submit" :disabled="createForm.processing"
                        class="rounded-xl bg-[#635BFF] px-5 py-2 text-sm font-semibold text-white hover:bg-[#5146E5] disabled:opacity-50 transition-all cursor-pointer shadow-md shadow-[#635BFF]/20">
                        Uložiť úlohu
                    </button>
                </div>
            </form>
        </div>

        <!-- Empty State -->
        <div v-if="tasks.data.length === 0"
            class="rounded-2xl border border-dashed border-[#222634] bg-[#161922]/40 p-16 text-center">
            <div
                class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#1D212C] text-[#9BA1AE] mb-4 shadow-inner">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012-2m-6 9l2 2 4-4" />
                </svg>
            </div>
            <h3 class="text-base font-semibold text-white">
                {{ search || status || tag ? 'Žiadne úlohy nezodpovedajú filtru' : 'Zatiaľ žiadne úlohy' }}
            </h3>
            <p class="text-sm text-[#9BA1AE] mt-1 max-w-sm mx-auto">
                {{ search || status || tag ? 'Skús upraviť vyhľadávanie alebo zrušiť aktívne filtre.' : 'Vytvorte si svoju prvú úlohu a začnite organizovať svoj deň efektívne.' }}
            </p>
        </div>

        <!-- Task List Items -->
        <ul v-else class="space-y-3">
            <li v-for="task in tasks.data" :key="task.id"
                class="group relative rounded-2xl border border-[#222634] bg-[#161922] p-4.5 shadow-sm hover:border-[#635BFF]/40 hover:bg-[#181C26] transition-all duration-200">

                <!-- Edit mode -->
                <form v-if="editingTaskId === task.id" @submit.prevent="submitEdit(task)" class="space-y-3">
                    <input v-model="editForm.title" type="text"
                        class="w-full rounded-xl border border-[#222634] bg-[#0F1117] px-3.5 py-2.5 text-sm text-white focus:border-[#635BFF] focus:ring-2 focus:ring-[#635BFF]/20 focus:outline-none">
                    <div v-if="editForm.errors.title" class="text-xs text-red-400 font-medium">
                        {{ editForm.errors.title }}
                    </div>
                    <textarea v-model="editForm.description" rows="2"
                        class="w-full rounded-xl border border-[#222634] bg-[#0F1117] px-3.5 py-2.5 text-sm text-white focus:border-[#635BFF] focus:ring-2 focus:ring-[#635BFF]/20 focus:outline-none resize-none"></textarea>
                    <div class="flex gap-2 justify-end">
                        <button type="button" @click="cancelEdit"
                            class="rounded-xl px-3.5 py-1.5 text-xs font-medium text-[#9BA1AE] hover:bg-[#1D212C] hover:text-white transition-colors cursor-pointer">
                            Zrušiť
                        </button>
                        <button type="submit" :disabled="editForm.processing"
                            class="rounded-xl bg-[#635BFF] px-4 py-1.5 text-xs font-semibold text-white hover:bg-[#5146E5] disabled:opacity-50 transition-all cursor-pointer">
                            Uložiť zmeny
                        </button>
                    </div>
                </form>

                <!-- Display mode -->
                <div v-else class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4 flex-1 min-w-0">
                        <button @click="toggleComplete(task)" type="button"
                            class="mt-1 h-5 w-5 shrink-0 rounded-lg border border-[#222634] bg-[#0F1117] flex items-center justify-center cursor-pointer hover:border-[#635BFF] transition-all group-hover:border-[#3A4156]">
                            <div v-if="task.is_completed"
                                class="h-2.5 w-2.5 rounded-md bg-[#635BFF] animate-in zoom-in-50 duration-150"></div>
                        </button>

                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-medium transition-colors leading-relaxed break-words"
                                :class="task.is_completed ? 'text-[#9BA1AE]/60 line-through' : 'text-white'">
                                {{ task.title }}
                            </h3>
                            <p v-if="task.description" class="text-xs text-[#9BA1AE] mt-1 leading-relaxed break-words">
                                {{ task.description }}
                            </p>

                            <!-- Tags -->
                            <div class="mt-3 flex flex-wrap items-center gap-1.5">
                                <span v-for="tag in task.tags" :key="tag.id"
                                    class="group/tag inline-flex items-center gap-1.5 rounded-lg bg-[#1D212C] px-2.5 py-1 text-[11px] font-medium text-[#9BA1AE] border border-[#262B3B] hover:border-[#3A4156] transition-all">
                                    <span class="h-1.5 w-1.5 rounded-full bg-[#635BFF]/60"></span>
                                    {{ tag.name }}
                                    <button @click="detachTag(task, tag)" type="button"
                                        class="text-[#9BA1AE]/60 hover:text-red-400 hover:scale-125 transition-all cursor-pointer ml-0.5 font-bold"
                                        aria-label="Odobrať tag">
                                        ×
                                    </button>
                                </span>

                                <form @submit.prevent="attachTag(task)" class="inline-flex items-center">
                                    <input v-model="tagInputs[task.id]" type="text" placeholder="+ tag"
                                        class="w-16 rounded-lg border border-transparent bg-transparent px-2 py-1 text-[11px] text-[#9BA1AE] placeholder:text-[#9BA1AE]/40 hover:border-[#222634] focus:w-24 focus:border-[#635BFF] focus:bg-[#0F1117] focus:text-white focus:outline-none transition-all">
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="flex shrink-0 gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity self-center">
                        <button @click="startEdit(task)" type="button"
                            class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-[#9BA1AE] hover:bg-[#1D212C] hover:text-white transition-colors cursor-pointer">
                            Upraviť
                        </button>
                        <button @click="destroyTask(task)" type="button"
                            class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-red-400/80 hover:bg-red-500/10 hover:text-red-400 transition-colors cursor-pointer">
                            Zmazať
                        </button>
                    </div>
                </div>
            </li>
        </ul>

        <!-- Pagination -->
        <div v-if="tasks.links && tasks.links.length > 3" class="mt-8 flex items-center justify-center gap-1.5">
            <Link v-for="link in tasks.links" :key="link.label" :href="link.url ?? '#'"
                class="rounded-xl border px-3.5 py-2 text-xs font-medium transition-all"
                :class="link.active
                    ? 'border-[#635BFF] bg-[#635BFF] text-white shadow-md shadow-[#635BFF]/25'
                    : 'border-[#222634] bg-[#161922] text-[#9BA1AE] hover:bg-[#1D212C] hover:text-white hover:border-[#3A4156]'"
                :style="{ opacity: link.url ? 1 : 0.3, pointerEvents: link.url ? 'auto' : 'none' }"
                v-html="link.label.includes('Previous') || link.label.includes('&laquo;') ? 'Predchádzajúca' : (link.label.includes('Next') || link.label.includes('&raquo;') ? 'Nasledujúca' : link.label)" />
        </div>
    </AppLayout>
</template>