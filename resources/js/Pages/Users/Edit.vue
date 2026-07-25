<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    roles: Array,
    user: Object,
    user_image: {
        type: String,
        default: null,
    },
});

const isEditing = computed(() => !!props.user);

const form = useForm({
    // Spoofing PUT for file uploads
    _method: isEditing.value ? 'put' : 'post', 
    name: props.user?.name ?? '',
    email: props.user?.email ?? '',
    role_id: props.user?.role_id ?? '',
    password: '',
    password_confirmation: '',
    image: null, 
});

// ✅ CORRECTED: Initialize with the 'user_image' prop
const imagePreview = ref(props.user_image || null);
const imageLoadFailed = ref(false);

watch(
    () => props.user_image,
    (url) => {
        imagePreview.value = url || null;
        imageLoadFailed.value = false;
    }
);

const handleImageError = () => {
    imageLoadFailed.value = true;
    imagePreview.value = null;
};

const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.image = file; 
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

// --- DROPDOWN LOGIC ---
const isDropdownOpen = ref(false);
const searchRole = ref('');
const dropdownRef = ref(null);
const filteredRoles = computed(() => {
    if (!props.roles) return [];
    return props.roles.filter(role => role.name.toLowerCase().includes(searchRole.value.toLowerCase()));
});
const selectedRoleName = computed(() => {
    const role = props.roles?.find(r => r.id === form.role_id);
    return role ? role.name.toUpperCase() : 'Select a role';
});
const selectRole = (role) => {
    form.role_id = role.id;
    isDropdownOpen.value = false;
};
const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) isDropdownOpen.value = false;
};
onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));

// --- FORM SUBMISSION ---
const showPassword = ref(false);

const submit = () => {
    if (isEditing.value) {
        // ✅ CORRECTED: Use .post with forceFormData for image uploads
        form.post(route('users.update', props.user.id), {
            forceFormData: true,
            onFinish: () => form.reset('password', 'password_confirmation', 'image'),
        });
    } else {
        form.post(route('users.store'), {
            forceFormData: true,
            onFinish: () => form.reset('password', 'password_confirmation', 'image'),
        });
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Edit User' : 'Create User'" />

    <AuthenticatedLayout>
        <!-- Header Section -->
        <div class="max-w-8xl mx-auto mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight dark:text-slate-100">
                    {{ isEditing ? 'Edit User Account' : 'Create New User' }}
                </h2>
                <p class="text-sm text-slate-800 mt-1 font-medium dark:text-slate-400">
                    {{ isEditing ? 'Update existing user credentials and permissions.' : 'Add a new member to the system and assign their role.' }}
                </p>
            </div>
            <Link 
                :href="route('users.index')" 
                class="theme-form-back-link"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span class="text-slate-900">Back to User List</span>
            </Link>
        </div>

        <div class="max-w-8xl mx-auto pb-24">
            <form @submit.prevent="submit" class="space-y-6">
                
                <!-- Main Information Card -->
                <div class="theme-form-card">
                   <div class="p-8 md:p-10">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
                            
                            <!-- Full Name -->
                            <div class="flex flex-col">
                                <InputLabel for="name" value="Name" class="theme-form-label ml-1" />
                                <TextInput
                                    id="name"
                                    type="text"
                                    class="theme-form-input"
                                    v-model="form.name"
                                    required
                                    autofocus
                                    placeholder="e.g. John Doe"
                                />
                                <InputError :message="form.errors.name" class="mt-2 ml-1" />
                            </div>

                            <!-- Email Address -->
                            <div class="flex flex-col">
                                <InputLabel for="email" value="Email Address" class="theme-form-label ml-1" />
                                <TextInput
                                    id="email"
                                    type="email"
                                    class="theme-form-input"
                                    v-model="form.email"
                                    required
                                    placeholder="john@example.com"
                                />
                                <InputError :message="form.errors.email" class="mt-2 ml-1" />
                            </div>

                            <!-- CUSTOM SEARCHABLE DROPDOWN (Assign Role) -->
                            <div class="flex flex-col relative" ref="dropdownRef">
                                <InputLabel for="role_id" value="Assign Role" class="theme-form-label ml-1" />
                                
                                <!-- Dropdown Trigger -->
                                <div 
                                    @click="isDropdownOpen = !isDropdownOpen"
                                    class="theme-form-select"
                                    :class="isDropdownOpen ? 'theme-form-select-open' : ''"
                                >
                                    <span :class="form.role_id ? 'text-slate-800 font-semibold text-sm dark:text-slate-100' : 'text-slate-400 text-sm font-medium dark:text-slate-500'">
                                        {{ selectedRoleName }}
                                    </span>
                                    <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{'rotate-180': isDropdownOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M19 9l-7 7-7-7" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>

                                <!-- Dropdown Menu -->
                                <transition name="pop">
                                    <div v-if="isDropdownOpen" class="absolute z-50 w-full mt-24 bg-white border border-slate-200 rounded-2xl shadow-2xl overflow-hidden dark:bg-slate-800 dark:border-slate-700">
                                        <div class="p-3 border-b border-slate-100 bg-slate-50/50 dark:border-slate-700 dark:bg-slate-900/40">
                                            <div class="relative">
                                                <input 
                                                    v-model="searchRole"
                                                    type="text" 
                                                    class="theme-form-input py-2.5 text-sm"
                                                    placeholder="Search roles..."
                                                    @click.stop
                                                />
                                            </div>
                                        </div>
                                        <ul class="max-h-56 overflow-y-auto py-2">
                                            <li v-for="role in filteredRoles" :key="role.id"
                                                @click="selectRole(role)"
                                                class="px-5 py-3 text-xs font-bold text-slate-600 cursor-pointer flex items-center justify-between hover:bg-indigo-50 hover:text-indigo-700 transition-colors dark:text-slate-300 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-300"
                                                :class="{'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300': form.role_id === role.id}"
                                            >
                                                {{ role.name.toUpperCase() }}
                                                <svg v-if="form.role_id === role.id" class="w-4 h-4 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            </li>
                                            <li v-if="filteredRoles.length === 0" class="px-5 py-8 text-center text-slate-400 text-xs dark:text-slate-500">No results found</li>
                                        </ul>
                                    </div>
                                </transition>
                                <InputError :message="form.errors.role_id" class="mt-2 ml-1" />
                            </div>

                            <!-- Password -->
                            <div class="flex flex-col">
                                <InputLabel for="password" value="Password" class="theme-form-label ml-1" />
                                <div class="relative">
                                    <TextInput
                                        id="password"
                                        :type="showPassword ? 'text' : 'password'"
                                        class="theme-form-input pr-12"
                                        v-model="form.password"
                                        :required="!isEditing"
                                        placeholder="••••••••"
                                    />
                                    <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-indigo-600 transition-colors">
                                        <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M2.036 12.322a1.012 1.012 0 0 1 0-.644C3.483 8.613 8.242 4.5 12 4.5c3.758 0 8.517 4.113 9.964 7.178.07.147.07.315 0 .462-1.447 3.065-4.206 7.178-9.964 7.178-3.758 0-8.517-4.113-9.964-7.178Z"/><path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21"/></svg>
                                    </button>
                                </div>
                                <p v-if="isEditing" class="mt-2 text-[10px] text-slate-400 font-bold italic ml-1 dark:text-slate-500">Leave blank to keep current password.</p>
                                <InputError :message="form.errors.password" class="mt-2 ml-1" />
                            </div>

                            <!-- Confirm Password -->
                            <div class="flex flex-col">
                                <InputLabel for="password_confirmation" value="Confirm Password" class="theme-form-label ml-1" />
                                <TextInput
                                    id="password_confirmation"
                                    type="password"
                                    class="theme-form-input"
                                    v-model="form.password_confirmation"
                                    :required="!isEditing && form.password"
                                    placeholder="••••••••"
                                />
                                <InputError :message="form.errors.password_confirmation" class="mt-2 ml-1" />
                            </div>

                            <!-- Profile Image -->
                            <div class="flex flex-col">
                                <InputLabel for="image" value="Profile Image" class="theme-form-label ml-1" />
                                
                                <div class="flex items-center gap-5 p-4 bg-slate-50 border border-slate-200 rounded-lg dark:bg-slate-900/60 dark:border-slate-700">
                                    <!-- Image Preview Circle -->
                                    <div class="relative shrink-0">
                                        <div class="w-20 h-20 rounded-xl bg-slate-200 border-2 border-white shadow-sm overflow-hidden flex items-center justify-center dark:bg-slate-700 dark:border-slate-600">
                                            <img
                                                v-if="imagePreview && !imageLoadFailed"
                                                :src="imagePreview"
                                                class="w-full h-full object-cover"
                                                alt="Profile preview"
                                                @error="handleImageError"
                                            />
                                            <svg v-if="!imagePreview || imageLoadFailed" class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Upload Buttons -->
                                    <div class="flex flex-col gap-2">
                                        <input
                                            type="file"
                                            class="hidden"
                                            ref="imageInput"
                                            @change="handleImageChange"
                                            accept="image/*"
                                        />
                                        <button 
                                            type="button" 
                                            @click="$refs.imageInput.click()"
                                            class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-[12px] font-bold uppercase tracking-wider text-slate-700 hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-600 transition-all shadow-sm dark:bg-slate-800 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-indigo-500/10 dark:hover:border-indigo-500/40 dark:hover:text-indigo-300"
                                        >
                                            Select New Photo
                                        </button>
                                        <p class="text-[10px] text-slate-400 font-medium ml-1 dark:text-slate-500">JPG, PNG or GIF. Max 2MB.</p>
                                    </div>
                                </div>
                                
                                <InputError :message="form.errors.image" class="mt-2 ml-1" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div class="flex items-center justify-center pt-4">
                    <PrimaryButton
                        class="theme-btn-primary px-12 py-4 rounded-full text-white font-black text-xs uppercase tracking-widest active:scale-95"
                        :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                    >
                        {{ isEditing ? 'Update User Record' : 'Create User' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Pop animation for the dropdown */
.pop-enter-active { transition: all 0.2s ease-out; }
.pop-leave-active { transition: all 0.1s ease-in; }
.pop-enter-from, .pop-leave-to { opacity: 0; transform: translateY(-10px) scale(0.95); }
</style>