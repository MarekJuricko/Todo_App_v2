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
                    class="group flex items-center gap-2.5 font-medium tracking-tight text-white transition-opacity">
                    <!-- Modern SaaS Logo: Minimalist shape with subtle glow -->
                    <div
                        class="relative flex h-6 w-6 items-center justify-center rounded-md bg-[#635BFF]/10 border border-[#635BFF]/30 text-[#635BFF] shadow-[0_0_12px_rgba(99,91,255,0.2)] group-hover:border-[#635BFF] transition-colors">
                        <div class="h-2 w-2 rounded-xs bg-[#635BFF]"></div>
                    </div>
                    <span class="text-sm font-semibold tracking-wide text-white">To<span
                            class="text-[#635BFF]">Do</span></span>
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