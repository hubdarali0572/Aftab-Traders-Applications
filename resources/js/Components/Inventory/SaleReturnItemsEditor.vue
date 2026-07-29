<script setup>
import { computed } from 'vue';

const props = defineProps({
    saleDetails: { type: Array, default: () => [] },
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
    reason: '',
    remarks: '',
});

const formatUnit = (value) => value?.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()) ?? '—';

const saleLine = (productId) => props.saleDetails.find((d) => String(d.product_id) === String(productId));

const onProductChange = (index) => {
    items.value = items.value.map((row, i) => (i === index ? { ...row } : row));
};

const addRow = () => {
    items.value = [...items.value, emptyRow()];
};

const removeRow = (index) => {
    const next = [...items.value];
    next.splice(index, 1);
    items.value = next.length ? next : [emptyRow()];
};

const lineTotal = (row) => {
    const line = saleLine(row.product_id);
    if (!line || !row.quantity) return '0.00';
    const sold = parseFloat(line.quantity) || 1;
    const qty = parseFloat(row.quantity) || 0;
    const unitDiscount = (parseFloat(line.discount) || 0) / sold;
    const unitTax = (parseFloat(line.tax) || 0) / sold;
    const price = parseFloat(line.unit_price) || 0;
    return (qty * price - unitDiscount * qty + unitTax * qty).toFixed(2);
};

const returnableQty = (productId) => saleLine(productId)?.returnable_qty ?? saleLine(productId)?.quantity ?? 0;

const unitLabel = (row) => {
    const line = saleLine(row.product_id);
    if (!line) return '—';

    return formatUnit(line.selling_unit);
};

const qtyWarning = (row) => {
    if (!row.product_id || !row.quantity) return null;
    const max = parseFloat(returnableQty(row.product_id)) || 0;
    const qty = parseFloat(row.quantity) || 0;
    if (qty > max) {
        return `Max returnable: ${max}`;
    }
    return null;
};

const totalQuantity = computed(() =>
    items.value.reduce((sum, row) => sum + (parseFloat(row.quantity) || 0), 0)
);

const totalAmount = computed(() =>
    items.value.reduce((sum, row) => sum + parseFloat(lineTotal(row)), 0)
);

if (!items.value.length) {
    addRow();
}

defineExpose({ totalQuantity, totalAmount });
</script>

<template>
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">{{ $t('Return Items') }}</h3>
            <button type="button" @click="addRow" class="theme-btn-primary px-4 py-2 text-xs shrink-0">{{ $t('Add Item') }}</button>
        </div>

        <div v-if="!saleDetails.length" class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
            {{ $t('Select an original invoice to load returnable products.') }}
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-100 dark:border-slate-700">
            <table class="w-full text-left border-collapse min-w-[900px]">
                <thead>
                    <tr class="theme-table-header">
                        <th class="theme-table-header-cell">{{ $t('Product') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Sold Qty') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Returnable') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Return Qty') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Selling Unit') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Unit Price') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Return Amount') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Reason') }}</th>
                        <th class="theme-table-header-cell text-right w-16"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <tr v-for="(row, index) in items" :key="index" class="theme-table-row align-top">
                        <td class="px-4 py-2 min-w-[180px]">
                            <select v-model="row.product_id" class="theme-form-input w-full" required @change="onProductChange(index)">
                                <option value="" disabled>{{ $t('Select Product') }}</option>
                                <option v-for="d in saleDetails" :key="d.product_id" :value="d.product_id" :disabled="parseFloat(d.returnable_qty ?? d.quantity) <= 0">
                                    {{ d.product?.name || 'Product #' + d.product_id }}
                                </option>
                            </select>
                            <p v-if="errors[`items.${index}.product_id`]" class="text-xs text-rose-600 mt-1">{{ errors[`items.${index}.product_id`] }}</p>
                        </td>
                        <td class="px-4 py-2 text-sm">{{ saleLine(row.product_id)?.quantity ?? '—' }}</td>
                        <td class="px-4 py-2 text-sm font-bold text-emerald-600">{{ returnableQty(row.product_id) }}</td>
                        <td class="px-4 py-2">
                            <input v-model="row.quantity" type="number" step="0.01" min="0.01" class="theme-form-input w-full" required />
                            <p v-if="qtyWarning(row)" class="text-xs text-rose-600 mt-1">{{ qtyWarning(row) }}</p>
                            <p v-if="errors[`items.${index}.quantity`]" class="text-xs text-rose-600 mt-1">{{ errors[`items.${index}.quantity`] }}</p>
                        </td>
                        <td class="px-4 py-2">
                            <span
                                class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wide bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200 min-h-[42px]"
                            >
                                {{ unitLabel(row) }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-sm">${{ saleLine(row.product_id)?.unit_price ?? '0.00' }}</td>
                        <td class="px-4 py-2 text-sm font-bold">${{ lineTotal(row) }}</td>
                        <td class="px-4 py-2">
                            <input v-model="row.reason" type="text" class="theme-form-input w-full" :placeholder="$t('Return reason')" />
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

        <div class="flex flex-wrap gap-6 justify-end text-sm font-bold text-slate-600 dark:text-slate-300">
            <span>{{ $t('Total Quantity') }}: <strong class="text-indigo-600">{{ totalQuantity.toFixed(2) }}</strong></span>
            <span>{{ $t('Total Amount') }}: <strong class="text-emerald-600">${{ totalAmount.toFixed(2) }}</strong></span>
        </div>
        <p v-if="errors.items" class="text-sm text-rose-600">{{ errors.items }}</p>
    </div>
</template>
