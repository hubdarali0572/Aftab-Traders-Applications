<script setup>
defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        required: true,
    },
    message: {
        type: String,
        required: true,
    },
    confirmLabel: {
        type: String,
        default: 'Confirm',
    },
    cancelLabel: {
        type: String,
        default: 'Cancel',
    },
    badge: {
        type: String,
        default: null,
    },
    badgeInitial: {
        type: String,
        default: null,
    },
    icon: {
        type: String,
        default: 'delete',
        validator: (value) => ['delete', 'warning'].includes(value),
    },
});

const emit = defineEmits(['close', 'confirm']);
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="theme-modal-overlay">
                <div class="theme-modal-backdrop" @click="emit('close')"></div>

                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-4"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-4"
                >
                    <div v-if="show" class="theme-modal-panel" role="dialog" aria-modal="true">
                        <!-- Header -->
                        <div class="theme-modal-header">
                            <h3 class="theme-modal-title">{{ title }}</h3>
                            <button
                                type="button"
                                class="theme-modal-close"
                                aria-label="Close"
                                @click="emit('close')"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="theme-modal-body">
                            <div
                                class="theme-modal-icon"
                                :class="icon === 'warning' ? 'theme-modal-icon-warning' : 'theme-modal-icon-danger'"
                            >
                                <svg v-if="icon === 'delete'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                <svg v-else class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>

                            <p class="theme-modal-message">{{ message }}</p>

                            <div v-if="badge" class="theme-modal-badge">
                                <div
                                    v-if="badgeInitial"
                                    class="w-7 h-7 rounded-full bg-slate-800 text-[10px] text-white flex items-center justify-center font-bold mr-2.5 uppercase shrink-0"
                                >
                                    {{ badgeInitial }}
                                </div>
                                <span class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ badge }}</span>
                            </div>

                            <p class="theme-modal-warning">This action is irreversible</p>
                        </div>

                        <!-- Footer -->
                        <div class="theme-modal-footer">
                            <button type="button" class="theme-modal-btn-cancel" @click="emit('close')">
                                {{ cancelLabel }}
                            </button>
                            <button type="button" class="theme-modal-btn-danger" @click="emit('confirm')">
                                {{ confirmLabel }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
