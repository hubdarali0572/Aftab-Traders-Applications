<script setup>
import { computed } from 'vue';

const props = defineProps({
    products: { type: Array, default: () => [] },
    warehouseStocks: { type: Array, default: () => [] },
    sellingUnits: { type: Array, default: () => ['piece'] },
    warehouseId: { type: [String, Number], default: '' },
    saleStatus: { type: String, default: 'draft' },
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
    selling_unit: 'piece',
    quantity: '',
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

const productById = (id) => props.products.find((p) => String(p.id) === String(id));

const availableStock = (productId) => {
    if (!props.warehouseId || !productId) return null;
    const row = props.warehouseStocks.find(
        (s) => String(s.warehouse_id) === String(props.warehouseId) && String(s.product_id) === String(productId)
    );
    return row ? Number(row.quantity) : 0;
};

const onProductChange = (index) => {
    const row = items.value[index];
    const product = productById(row.product_id);
    if (product?.selling_price != null) {
        row.unit_price = product.selling_price;
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

const stockWarning = (row) => {
    if (props.saleStatus !== 'completed' || !props.warehouseId || !row.product_id) return null;
    const qty = parseFloat(row.quantity) || 0;
    const available = availableStock(row.product_id) ?? 0;
    if (qty > available) {
        return `Insufficient stock. Available: ${available}, requested: ${qty}.`;
    }
    return null;
};

if (!items.value.length) {
    addRow();
}

defineExpose({ subtotal });
</script>

<template>
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">{{ $t('Sale Items') }}</h3>
            <button type="button" @click="addRow" class="theme-btn-primary px-4 py-2 text-xs shrink-0">{{ $t('Add Item') }}</button>
        </div>

        <div
            v-if="saleStatus === 'completed'"
            class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200"
        >
            {{ $t('Completed sales deduct stock from the selected warehouse immediately.') }}
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-100 dark:border-slate-700">
            <table class="w-full text-left border-collapse min-w-[1100px]">
                <thead>
                    <tr class="theme-table-header">
                        <th class="theme-table-header-cell">{{ $t('Product') }}</th>
                        <th class="theme-table-header-cell">{{ $t('SKU') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Available Stock') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Unit') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Quantity') }}</th>
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
                        <td class="px-4 py-2 text-sm text-slate-500">{{ productById(row.product_id)?.sku || '—' }}</td>
                        <td class="px-4 py-2 text-sm font-bold" :class="stockWarning(row) ? 'text-rose-600' : 'text-slate-600'">
                            {{ row.product_id && warehouseId ? (availableStock(row.product_id) ?? 0) : '—' }}
                        </td>
                        <td class="px-4 py-2">
                            <select v-model="row.selling_unit" class="theme-form-input w-full">
                                <option v-for="u in sellingUnits" :key="u" :value="u">{{ u }}</option>
                            </select>
                        </td>
                        <td class="px-4 py-2">
                            <input v-model="row.quantity" type="number" step="0.01" min="0.01" class="theme-form-input w-full" required />
                            <p v-if="stockWarning(row)" class="text-xs text-rose-600 mt-1">{{ stockWarning(row) }}</p>
                            <p v-if="errors[`items.${index}.quantity`]" class="text-xs text-rose-600 mt-1">{{ errors[`items.${index}.quantity`] }}</p>
                        </td>
                        <td class="px-4 py-2">
                            <input v-model="row.unit_price" type="number" step="0.01" min="0" class="theme-form-input w-full" required />
                        </td>
                        <td class="px-4 py-2">
                            <input v-model="row.discount" type="number" step="0.01" min="0" class="theme-form-input w-full" />
                        </td>
                        <td class="px-4 py-2">
                            <input v-model="row.tax" type="number" step="0.01" min="0" class="theme-form-input w-full" />
                        </td>
                        <td class="px-4 py-2">
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-200">${{ lineTotal(row) }}</span>
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
            {{ $t('Subtotal') }}: <strong class="text-indigo-600 ml-2">${{ subtotal.toFixed(2) }}</strong>
        </div>
        <p v-if="errors.items" class="text-sm text-rose-600">{{ errors.items }}</p>
    </div>
</template>
