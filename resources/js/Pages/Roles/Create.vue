<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    permissionGroups: Object, // Contains 'User Management' and 'Role Management'
    role: Object,
    rolePermissions: Array
});

const isEditing = computed(() => !!props.role);

const form = useForm({
    name: props.role?.name ?? '',
    permissions: props.rolePermissions ?? [], 
});

// Select/Deselect all permissions within one card
const toggleGroup = (groupPermissions, event) => {
    if (event.target.checked) {
        groupPermissions.forEach(p => {
            if (!form.permissions.includes(p.id)) form.permissions.push(p.id);
        });
    } else {
        const ids = groupPermissions.map(p => p.id);
        form.permissions = form.permissions.filter(id => !ids.includes(id));
    }
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('roles.update', props.role.id));
    } else {
        form.post(route('roles.store'));
    }
};
</script>

<template>
    <Head :title="isEditing ? $t('Edit Role') : $t('Create Role')" />

    <AuthenticatedLayout>
        <!-- Header -->
        <div class="max-w-8xl mx-auto mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-700 tracking-tight dark:text-slate-100">
                    {{ isEditing ? $t('Edit Role Authority') : $t('Create System Role') }}
                </h2>
                <p class="text-sm text-slate-500 mt-1 font-medium dark:text-slate-400">{{ $t('Configure access levels for Users and Roles.') }}</p>
            </div>
            <Link 
                :href="route('roles.index')" 
                class="theme-form-back-link"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                {{ $t('Back to Role List') }}
            </Link>
        </div>

        <form @submit.prevent="submit" class="max-w-8xl mx-auto pb-24 space-y-8">
            
            <!-- Role Identity Card -->
            <div class="theme-form-card p-8">
                <div class="max-w-md">
                    <InputLabel for="name" :value="$t('Role Name')" class="theme-form-label" />
                    <TextInput 
                        id="name" v-model="form.name" type="text" required 
                        class="theme-form-input"
                        :placeholder="$t('Create New Role ...')"
                    />
                    <InputError :message="form.errors.name" class="mt-2" />
                </div>
            </div>

            <!-- Permissions Grid: Fixed 2-Column Layout -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div v-for="(permissions, groupName) in permissionGroups" :key="groupName" 
                    class="theme-form-card flex flex-col"
                >
                    <!-- Card Header -->
                    <div class="theme-form-section-header flex items-center justify-between">
                        <h3 class="theme-form-section-title">
                            {{ $t(groupName) }}
                        </h3>
                        
                        <label class="inline-flex items-center cursor-pointer group">
                            <input type="checkbox" @change="toggleGroup(permissions, $event)" class="theme-form-checkbox h-4 w-4">
                            <span class="ml-2 text-[12px] font-bold text-indigo-700 dark:text-white uppercase">{{ $t('Toggle All') }}</span>
                        </label>
                    </div>

                  <!-- Card Body: Permissions Grid (3 columns) -->
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-x-4 gap-y-4">
                        <div v-for="permission in permissions" :key="permission.id" 
                            class="flex items-center p-3 rounded-xl border border-transparent hover:border-slate-100 hover:bg-slate-50/80 transition-all group dark:hover:border-slate-700 dark:hover:bg-slate-700/40"
                        >
                            <input 
                                :id="'perm-' + permission.id"
                                type="checkbox" 
                                v-model="form.permissions" 
                                :value="permission.id"
                                class="theme-form-checkbox h-5 w-5"
                            >
                            <label :for="'perm-' + permission.id" class="ml-3 cursor-pointer flex-1">
                                <span class="block text-[13px] font-bold text-slate-700 capitalize tracking-tight group-hover:text-indigo-600 transition-colors dark:text-slate-200 dark:group-hover:text-indigo-400">
                                    {{ permission.name }}
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Action -->
            <div class="flex items-center justify-center pt-4">
                <PrimaryButton
                    class="theme-btn-primary px-14 py-4 rounded-full font-black text-xs text-white uppercase tracking-widest active:scale-95"
                    :disabled="form.processing"
                >
                    {{ isEditing ? $t('Save Changes') : $t('Create Role') }}
                </PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>