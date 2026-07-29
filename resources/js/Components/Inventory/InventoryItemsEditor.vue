<script setup>
import { computed } from 'vue';

const props = defineProps({
    products: { type: Array, default: () => [] },
    modelValue: { type: Array, default: () => [] },
    quantityField: { type: String, default: 'quantity' },
    quantityLabel: { type: String, default: 'Quantity' },
    allowNegative: { type: Boolean, default: false },
    extraField: { type: String, default: null },
    extraLabel: { type: String, default: 'Reason' },
    hint: { type: String, default: '' },
    errors: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['update:modelValue']);

const items = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val),
});

const emptyRow = () => {
    const row = { product_id: '', unit_cost: 0 };
    row[props.quantityField] = props.allowNegative ? 0 : '';
    if (props.extraField) {
        row[props.extraField] = '';
    }
    return row;
};

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
        row.unit_cost = product.purchase_price;
    }
};

const lineTotal = (row) => {
    const qty = Math.abs(parseFloat(row[props.quantityField]) || 0);
    const cost = parseFloat(row.unit_cost) || 0;
    return (qty * cost).toFixed(2);
};

const totalQuantity = computed(() =>
    items.value.reduce((sum, row) => sum + Math.abs(parseFloat(row[props.quantityField]) || 0), 0)
);

const totalAmount = computed(() =>
    items.value.reduce((sum, row) => sum + parseFloat(lineTotal(row)), 0).toFixed(2)
);

if (!items.value.length) {
    addRow();
}

defineExpose({ totalQuantity, totalAmount });
</script>

<template>
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">{{ $t('Products to Correct') }}</h3>
                <p v-if="hint" class="text-xs text-slate-500 mt-1 dark:text-slate-400">{{ hint }}</p>
            </div>
            <button type="button" @click="addRow" class="theme-btn-primary px-4 py-2 text-xs shrink-0">
                {{ $t('Add Row') }}
            </button>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-100 dark:border-slate-700">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="theme-table-header">
                        <th class="theme-table-header-cell">{{ $t('Product') }}</th>
                        <th class="theme-table-header-cell">{{ quantityLabel }}</th>
                        <th class="theme-table-header-cell">{{ $t('Unit Cost') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Line Total') }}</th>
                        <th v-if="extraField" class="theme-table-header-cell">{{ extraLabel }}</th>
                        <th class="theme-table-header-cell text-right w-16"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <tr v-for="(row, index) in items" :key="index" class="theme-table-row">
                        <td class="px-4 py-2 min-w-[200px]">
                            <select
                                v-model="row.product_id"
                                class="theme-form-input w-full"
                                required
                                @change="onProductChange(index)"
                            >
                                <option value="" disabled>{{ $t('Select product') }}</option>
                                <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                            <p v-if="errors[`items.${index}.product_id`]" class="text-xs text-rose-600 mt-1">{{ errors[`items.${index}.product_id`] }}</p>
                        </td>
                        <td class="px-4 py-2">
                            <input
                                v-model="row[quantityField]"
                                type="number"
                                :step="allowNegative ? '0.01' : '0.01'"
                                :min="allowNegative ? undefined : '0.01'"
                                class="theme-form-input w-full"
                                required
                            />
                            <p v-if="errors[`items.${index}.${quantityField}`]" class="text-xs text-rose-600 mt-1">{{ errors[`items.${index}.${quantityField}`] }}</p>
                        </td>
                        <td class="px-4 py-2">
                            <input v-model="row.unit_cost" type="number" step="0.01" min="0" class="theme-form-input w-full" required />
                            <p v-if="errors[`items.${index}.unit_cost`]" class="text-xs text-rose-600 mt-1">{{ errors[`items.${index}.unit_cost`] }}</p>
                        </td>
                        <td class="px-4 py-2">
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ lineTotal(row) }}</span>
                        </td>
                        <td v-if="extraField" class="px-4 py-2">
                            <input v-model="row[extraField]" type="text" class="theme-form-input w-full" :placeholder="extraLabel" />
                        </td>
                        <td class="px-4 py-2 text-right">
                            <button
                                type="button"
                                @click="removeRow(index)"
                                class="theme-table-action-btn theme-table-action-delete"
                                :disabled="items.length <= 1"
                                title="Remove row"
                            >
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap gap-6 justify-end text-sm font-bold text-slate-600 dark:text-slate-300">
            <span>{{ $t('Total Quantity') }}: <strong class="text-indigo-600">{{ totalQuantity.toFixed(2) }}</strong></span>
            <span>{{ $t('Total Amount') }}: <strong class="text-emerald-600">{{ totalAmount }}</strong></span>
        </div>
        <p v-if="errors.items" class="text-sm text-rose-600">{{ errors.items }}</p>
    </div>
</template>
