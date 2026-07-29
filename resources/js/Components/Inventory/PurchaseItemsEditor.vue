<script setup>
import { computed } from 'vue';

const props = defineProps({
    products: { type: Array, default: () => [] },
    modelValue: { type: Array, default: () => [] },
    errors: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['update:modelValue']);

const items = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val),
});

const emptyRow = () => ({
    product_id: '',
    quantity: '',
    free_quantity: 0,
    unit_price: 0,
    discount: 0,
    tax: 0,
    remarks: '',
});

const addRow = () => {
    items.value = [...items.value, emptyRow()];
};

const removeRow = (index) => {
    const next = [...items.value];
    next.splice(index, 1);
    items.value = next.length ? next : [emptyRow()];
};

const onProductChange = (index) => {
    const row = items.value[index];
    const product = props.products.find((p) => p.id === row.product_id);
    if (product?.purchase_price != null) {
        row.unit_price = product.purchase_price;
    }
};

const lineTotal = (row) => {
    const qty = parseFloat(row.quantity) || 0;
    const price = parseFloat(row.unit_price) || 0;
    const discount = parseFloat(row.discount) || 0;
    const tax = parseFloat(row.tax) || 0;
    return (qty * price - discount + tax).toFixed(2);
};

const subtotal = computed(() =>
    items.value.reduce((sum, row) => sum + parseFloat(lineTotal(row)), 0)
);

if (!items.value.length) {
    addRow();
}

defineExpose({ subtotal });
</script>

<template>
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">{{ $t('Purchase Items') }}</h3>
            <button type="button" @click="addRow" class="theme-btn-primary px-4 py-2 text-xs shrink-0">{{ $t('Add Item') }}</button>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-100 dark:border-slate-700">
            <table class="w-full text-left border-collapse min-w-[900px]">
                <thead>
                    <tr class="theme-table-header">
                        <th class="theme-table-header-cell">{{ $t('Product') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Quantity') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Free Qty') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Unit Price') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Discount') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Tax') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Line Total') }}</th>
                        <th class="theme-table-header-cell text-right w-16"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <tr v-for="(row, index) in items" :key="index" class="theme-table-row align-top">
                        <td class="px-4 py-2 min-w-[180px]">
                            <select v-model="row.product_id" class="theme-form-input w-full" required @change="onProductChange(index)">
                                <option value="" disabled>{{ $t('Select Product') }}</option>
                                <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                            <p v-if="errors[`items.${index}.product_id`]" class="text-xs text-rose-600 mt-1">{{ errors[`items.${index}.product_id`] }}</p>
                        </td>
                        <td class="px-4 py-2">
                            <input v-model="row.quantity" type="number" step="0.01" min="0" class="theme-form-input w-full" required />
                            <p v-if="errors[`items.${index}.quantity`]" class="text-xs text-rose-600 mt-1">{{ errors[`items.${index}.quantity`] }}</p>
                        </td>
                        <td class="px-4 py-2">
                            <input v-model="row.free_quantity" type="number" step="0.01" min="0" class="theme-form-input w-full" />
                        </td>
                        <td class="px-4 py-2">
                            <input v-model="row.unit_price" type="number" step="0.01" min="0" class="theme-form-input w-full" required />
                            <p v-if="errors[`items.${index}.unit_price`]" class="text-xs text-rose-600 mt-1">{{ errors[`items.${index}.unit_price`] }}</p>
                        </td>
                        <td class="px-4 py-2">
                            <input v-model="row.discount" type="number" step="0.01" min="0" class="theme-form-input w-full" />
                        </td>
                        <td class="px-4 py-2">
                            <input v-model="row.tax" type="number" step="0.01" min="0" class="theme-form-input w-full" />
                        </td>
                        <td class="px-4 py-2">
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ lineTotal(row) }}</span>
                        </td>
                        <td class="px-4 py-2 text-right">
                            <button type="button" @click="removeRow(index)" class="theme-table-action-btn theme-table-action-delete" :disabled="items.length <= 1" :title="$t('Remove')">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex justify-end text-sm font-bold text-slate-600 dark:text-slate-300">
            {{ $t('Subtotal') }}: <strong class="text-indigo-600 ml-2">{{ subtotal.toFixed(2) }}</strong>
        </div>
        <p v-if="errors.items" class="text-sm text-rose-600">{{ errors.items }}</p>
    </div>
</template>
