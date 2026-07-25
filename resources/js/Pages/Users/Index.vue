<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

// users will now be an Object containing 'data' and 'links' if paginated
defineProps({ users: Object });

const page = usePage();
const showFlash = ref(false);
let timer = null;

const startTimer = () => {
    showFlash.value = true;
    if (timer) clearTimeout(timer);
    timer = setTimeout(() => {
        showFlash.value = false;
    }, 5000);
};

watch(
    () => [page.props.flash.success, page.props.flash.danger],
    ([newSuccess, newDanger]) => {
        if (newSuccess || newDanger) {
            startTimer();
        }
    },
    { immediate: true }
);

// Modal Logic
const isModalOpen = ref(false);
const selectedUser = ref(null); // Store the whole user object here
const brokenImages = ref({});

const markImageBroken = (userId) => {
    brokenImages.value[userId] = true;
};

const showUserImage = (user) => user.profile_image && !brokenImages.value[user.id];

const openDeleteModal = (user) => {
    selectedUser.value = user; // Pass the whole user object
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    // We clear the user after the modal closes so the animation stays smooth
    setTimeout(() => { selectedUser.value = null; }, 300);
};

const confirmDelete = () => {
    if (selectedUser.value) {
        router.delete(route('users.destroy', selectedUser.value.id), {
            onSuccess: () => closeModal(),
            onFinish: () => closeModal(),
        });
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="User Management" />

        <!-- Page Header -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-700 tracking-tight dark:text-slate-100">User Management</h2>
                <p class="text-sm text-slate-500 mt-1 font-medium dark:text-slate-400">Manage and monitor system access and roles.</p>
            </div>
            
            <Link :href="route('users.create')" class="theme-btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M12 5v14m7-7H5" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Create New User
            </Link>
        </div>

        <!-- Flash Messages -->
        <transition name="fade">
            <div v-if="showFlash && ($page.props.flash.success || $page.props.flash.danger)" 
                :class="[$page.props.flash.success ? 'bg-indigo-50 border-indigo-500 text-indigo-800 dark:bg-indigo-500/10 dark:text-indigo-200' : 'bg-slate-100 border-slate-400 text-slate-700 dark:bg-slate-700/80 dark:text-slate-200']"
                class="mb-6 flex items-center p-4 border-l-4 rounded-r-xl shadow-sm"
            >
                <div class="flex-shrink-0">
                    <svg v-if="$page.props.flash.success" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/></svg>
                    <svg v-else class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"/></svg>
                </div>
                <p class="ml-3 text-sm font-bold">{{ $page.props.flash.success || $page.props.flash.danger }}</p>
                <button @click="showFlash = false" class="ml-auto opacity-50 hover:opacity-100 transition-opacity">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>
        </transition>

        <!-- Professional Table Card -->
        <div class="theme-table-card">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="theme-table-header">
                            <th class="theme-table-header-cell">User Info</th>
                            <th class="theme-table-header-cell">User Role</th>
                            <th class="theme-table-header-cell text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="user in users.data" :key="user.id" class="theme-table-row group">
                            <td class="px-6 py-2">
                                <div class="flex items-center space-x-4">
                                  <div class="h-10 w-10 shrink-0 rounded-full bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center dark:bg-slate-700 dark:border-slate-600">
                                        <!-- Show profile_image if it exists -->
                                        <img
                                            v-if="showUserImage(user)"
                                            :src="user.profile_image"
                                            :alt="user.name"
                                            class="h-full w-full object-cover"
                                            @error="markImageBroken(user.id)"
                                        />
                                        
                                        <!-- Show default icon if no image -->
                                        <svg v-if="!showUserImage(user)" class="h-6 w-6 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-800 tracking-tight dark:text-slate-100">{{ user.name }}</div>
                                        <div class="text-xs text-slate-500 font-medium dark:text-slate-400">{{ user.email }}</div>
                                    </div>
                                </div>
                            </td>

                             <td class="px-6 py-2">
                             <div class="text-sm font-bold text-slate-800 tracking-tight dark:text-slate-100">{{ user.role.name }}</div>
                            </td>

                            <td class="px-6 py-2 whitespace-nowrap text-right">
                                <div class="theme-table-actions">
                                    <Link 
                                        :href="route('users.edit', user.id)" 
                                        class="theme-table-action-btn theme-table-action-edit"
                                        title="Edit User"
                                        aria-label="Edit User"
                                    >
                                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </Link>
                                    
                                    <button 
                                        @click="openDeleteModal(user)" 
                                        class="theme-table-action-btn theme-table-action-delete"
                                        title="Delete User"
                                        aria-label="Delete User"
                                    >
                                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="users.data.length === 0">
                            <td colspan="3" class="px-6 py-12 text-center text-slate-400 font-medium dark:text-slate-500">No users found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div class="theme-table-footer flex flex-col space-y-4 sm:space-y-0 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-widest text-center sm:text-left dark:text-slate-200">
                    Showing <span class="text-slate-900 dark:text-slate-200">{{ users.from || 0 }}</span> to <span class="text-slate-900 dark:text-slate-200">{{ users.to || 0 }}</span> of <span class="text-slate-900 dark:text-slate-200">{{ users.total }}</span> entries
                </div>

                <div class="flex flex-wrap justify-center items-center gap-1.5">
                    <template v-for="(link, k) in users.links" :key="k">
                        <Link 
                            v-if="link.url" 
                            :href="link.url" 
                            v-html="link.label"
                            class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border transition-all duration-200"
                            :class="[link.active ? 'theme-pagination-active' : 'theme-pagination-inactive']"
                        />
                        <span v-else v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold text-slate-300 bg-white border border-slate-100 rounded-lg cursor-not-allowed dark:text-slate-600 dark:bg-slate-800 dark:border-slate-700" />
                    </template>
                </div>
            </div>
        </div>

        <ConfirmModal
            :show="isModalOpen"
            title="Delete User Account"
            message="Are you sure you want to permanently remove this user account from the system?"
            confirm-label="Yes, Delete User"
            cancel-label="No, Keep User"
            :badge="selectedUser?.name"
            :badge-initial="selectedUser?.name?.slice(0, 1)"
            @close="closeModal"
            @confirm="confirmDelete"
        />
    </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.4s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>