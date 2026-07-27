<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ detail: Object, adjustments: Array, products: Array });

const form = useForm({
    stock_adjustment_id: props.detail.stock_adjustment_id,
    product_id: props.detail.product_id,
    system_quantity: props.detail.system_quantity,
    physical_quantity: props.detail.physical_quantity,
    unit_cost: props.detail.unit_cost, // Added this field
    reason: props.detail.reason,
    remarks: props.detail.remarks,
    status: Boolean(props.detail.status),
});

const diffQty = computed(() => (parseFloat(form.physical_quantity || 0) - parseFloat(form.system_quantity || 0)).toFixed(2));
const totalCost = computed(() => (Math.abs(diffQty.value) * parseFloat(form.unit_cost || 0)).toFixed(2));

const submit = () => form.put(route('stock-adjustment-details.update', props.detail.id));
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Edit Line Item" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-black text-slate-900">Edit Adjustment Item</h2>
                <p class="text-sm text-slate-500 font-medium">Record ID: #{{ detail.id }}</p>
            </div>
            <Link :href="route('stock-adjustment-details.index')" class="theme-form-back-link">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Back to List
            </Link>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div class="theme-form-card p-8 md:p-10">
                <!-- Main Form Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-10 gap-y-6">
                    <div>
                        <InputLabel value="Adjustment Reference" />
                        <select v-model="form.stock_adjustment_id" class="theme-form-input w-full" required>
                            <option v-for="a in adjustments" :key="a.id" :value="a.id">{{ a.reference_no }}</option>
                        </select>
                        <InputError :message="form.errors.stock_adjustment_id" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel value="Product" />
                        <select v-model="form.product_id" class="theme-form-input w-full" required>
                            <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                        <InputError :message="form.errors.product_id" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel value="Reason for Adjustment" />
                        <TextInput v-model="form.reason" class="w-full" placeholder="e.g. Breakage, Audit correction" />
                        <InputError :message="form.errors.reason" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel value="System Quantity" />
                        <TextInput type="number" step="0.01" v-model="form.system_quantity" class="w-full" />
                        <InputError :message="form.errors.system_quantity" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel value="Physical Quantity" />
                        <TextInput type="number" step="0.01" v-model="form.physical_quantity" class="w-full" />
                        <InputError :message="form.errors.physical_quantity" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel value="Unit Cost" />
                        <TextInput type="number" step="0.01" v-model="form.unit_cost" class="w-full" />
                        <InputError :message="form.errors.unit_cost" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel value="Difference (Auto)" />
                        <div class="theme-form-input bg-slate-50 dark:bg-slate-800/50 font-bold" :class="diffQty >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                            {{ diffQty > 0 ? '+' : '' }}{{ diffQty }}
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Total Value (Auto)" />
                        <div class="theme-form-input bg-slate-50 dark:bg-slate-800/50 font-black text-indigo-600">
                            ${{ totalCost }}
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Item Status" />
                        <label class="inline-flex items-center gap-3 mt-2 cursor-pointer select-none">
                            <button type="button" @click="form.status = !form.status" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200" :class="form.status ? 'bg-indigo-600' : 'bg-slate-300'">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200" :class="form.status ? 'translate-x-6' : 'translate-x-1'" />
                            </button>
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ form.status ? 'Active' : 'Inactive' }}</span>
                        </label>
                    </div>
                </div>

                <!-- Remarks Section -->
                <div class="mt-8">
                    <InputLabel for="remarks" value="Remarks" class="theme-form-label ml-1" />
                    <textarea id="remarks" v-model="form.remarks" class="theme-form-input w-full h-24 pt-3 resize-none" placeholder="Enter additional details regarding this adjustment..."></textarea>
                    <InputError :message="form.errors.remarks" class="mt-2 ml-1" />
                </div>
            </div>

            <div class="flex justify-center pt-4">
                <PrimaryButton :disabled="form.processing" class="theme-btn-primary px-12 py-4">Update Adjustment Item</PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>