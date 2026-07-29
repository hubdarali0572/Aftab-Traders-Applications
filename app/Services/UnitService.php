<?php

namespace App\Services;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class UnitService
{
    /** Matches sale_details.selling_unit enum values. */
    public const DEFAULT_UNITS = [
        ['name' => 'Piece', 'short_name' => 'piece', 'base_value' => 1],
        ['name' => 'Carton', 'short_name' => 'carton', 'base_value' => 1],
        ['name' => 'Box', 'short_name' => 'box', 'base_value' => 1],
        ['name' => 'Dozen', 'short_name' => 'dozen', 'base_value' => 12],
        ['name' => 'Bundle', 'short_name' => 'bundle', 'base_value' => 1],
        ['name' => 'Pair', 'short_name' => 'pair', 'base_value' => 2],
    ];

    public function ensureDefaultUnits(): void
    {
        if (Unit::withTrashed()->exists()) {
            return;
        }

        $userId = $this->resolveUserId();

        if (! $userId) {
            return;
        }

        foreach (self::DEFAULT_UNITS as $unit) {
            $this->createUnit($unit, $userId);
        }
    }

    public function findForSellingUnit(string $sellingUnit): ?Unit
    {
        $key = strtolower(trim($sellingUnit));

        if ($key === '') {
            return null;
        }

        return Unit::query()
            ->where('status', true)
            ->where(function ($query) use ($key) {
                $query->whereRaw('LOWER(name) = ?', [$key])
                    ->orWhereRaw('LOWER(short_name) = ?', [$key])
                    ->orWhereRaw('LOWER(name) LIKE ?', ["%{$key}%"]);
            })
            ->first();
    }

    /**
     * Resolve a units.id for inventory posting from the sale line selling unit.
     * Products no longer store unit_id — carton/piece pricing is on the product;
     * the sale line selling_unit (piece, carton, etc.) is the source of truth.
     */
    public function resolveForSellingUnit(string $sellingUnit): int
    {
        $this->ensureDefaultUnits();

        $unit = $this->findForSellingUnit($sellingUnit);
        if ($unit) {
            return (int) $unit->id;
        }

        $key = strtolower(trim($sellingUnit));
        $definition = collect(self::DEFAULT_UNITS)->first(
            fn (array $unit) => strtolower($unit['short_name']) === $key
                || strtolower($unit['name']) === $key
        );

        $userId = $this->resolveUserId();

        if ($definition && $userId) {
            return (int) $this->createUnit($definition, $userId)->id;
        }

        $fallback = $this->findForSellingUnit('piece')
            ?? Unit::query()->where('status', true)->orderBy('id')->first()
            ?? Unit::query()->orderBy('id')->first();

        if (! $fallback) {
            throw new InvalidArgumentException(
                'No unit is configured in the system. Please add units in Product Management → Units.'
            );
        }

        return (int) $fallback->id;
    }

    protected function createUnit(array $definition, int $userId): Unit
    {
        return Unit::create([
            'user_id' => $userId,
            'name' => $definition['name'],
            'short_name' => $definition['short_name'],
            'base_value' => $definition['base_value'],
            'description' => 'Default unit',
            'status' => true,
        ]);
    }

    protected function resolveUserId(): ?int
    {
        return Auth::id() ?? User::query()->orderBy('id')->value('id');
    }
}
