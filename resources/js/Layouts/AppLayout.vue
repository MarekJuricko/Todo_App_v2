<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';

const page = usePage();

function logout() {
    router.post('/logout');
}
</script>

<template>
    <div class="min-h-screen bg-[#0F1117] text-[#EDEDEF] selection:bg-[#635BFF] selection:text-white">
        <!-- Header -->
        <header class="border-b border-[#222634] bg-[#0F1117]/80 backdrop-blur-md sticky top-0 z-30">
            <div class="mx-auto flex max-w-3xl items-center justify-between px-6 py-4">
                <Link href="/"
                    class="flex items-center gap-2 font-semibold tracking-tight text-white hover:opacity-80 transition-opacity">
                    <span
                        class="flex h-6 w-6 items-center justify-center rounded-md bg-[#635BFF] text-white text-xs font-bold shadow-sm shadow-[#635BFF]/30">T</span>
                    <span>ToDo</span>
                </Link>

                <div v-if="page.props.auth.user" class="flex items-center gap-4">
                    <span class="text-sm font-medium text-[#9BA1AE]">{{ page.props.auth.user.name }}</span>
                    <button @click="logout"
                        class="inline-flex items-center justify-center rounded-lg px-3 py-1.5 text-sm font-medium text-[#9BA1AE] hover:bg-[#1D212C] hover:text-white transition-colors cursor-pointer">
                        Odhlásiť sa
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="mx-auto max-w-3xl px-6 py-10">
            <slot />
        </main>
    </div>
</template>