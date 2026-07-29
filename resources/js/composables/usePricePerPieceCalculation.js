import { computed, watch } from 'vue';

const toNumber = (value) => {
    if (value === '' || value === null || value === undefined) {
        return null;
    }

    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : null;
};

const roundCurrency = (value) => Math.round(value * 100) / 100;

export function usePricePerPieceCalculation(form) {
    const recalculatePricePerPiece = () => {
        const pricePerCarton = toNumber(form.price_per_carton);
        const piecesPerCarton = toNumber(form.pieces_per_carton);

        if (pricePerCarton === null || piecesPerCarton === null || piecesPerCarton === 0) {
            form.price_per_piece = 0;
            return;
        }

        form.price_per_piece = roundCurrency(pricePerCarton / piecesPerCarton);
    };

    const formattedPricePerPiece = computed(() => {
        const value = toNumber(form.price_per_piece);
        return (value ?? 0).toFixed(2);
    });

    const prepareProductPricing = () => {
        recalculatePricePerPiece();

        const decimalFields = ['purchase_price', 'selling_price', 'price_per_carton', 'price_per_piece', 'weight'];
        decimalFields.forEach((field) => {
            const value = form[field];
            if (value === '' || value === null || value === undefined) {
                form[field] = null;
                return;
            }

            const parsed = toNumber(value);
            form[field] = parsed === null ? null : roundCurrency(parsed);
        });

        const integerFields = ['carton_qty', 'pieces_per_carton'];
        integerFields.forEach((field) => {
            const value = form[field];
            if (value === '' || value === null || value === undefined) {
                form[field] = null;
                return;
            }

            const parsed = toNumber(value);
            form[field] = parsed === null ? null : Math.trunc(parsed);
        });
    };

    watch(
        () => [form.price_per_carton, form.pieces_per_carton],
        recalculatePricePerPiece,
        { immediate: true },
    );

    return {
        formattedPricePerPiece,
        prepareProductPricing,
        recalculatePricePerPiece,
    };
}
