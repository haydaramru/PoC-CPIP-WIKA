<?php

namespace App\Http\Controllers;

use App\Models\ColumnAlias;
use App\Services\WorkbookFieldMapper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ColumnAliasController extends Controller
{
    private const CONTEXTS = ['project', 'work_item', 'material', 'equipment', 'period', 's_curve'];

    public function __construct(
        private readonly ?WorkbookFieldMapper $fieldMapper = null,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $aliases = ColumnAlias::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = trim((string) $request->query('q'));

                $query->where(function ($inner) use ($keyword) {
                    $inner->where('alias', 'like', "%{$keyword}%")
                        ->orWhere('target_field', 'like', "%{$keyword}%");
                });
            })
            ->when($request->context, fn($q) => $q->where('context', $request->context))
            ->when($request->boolean('active_only', true), fn($q) => $q->where('is_active', true))
            ->orderBy('context')
            ->orderBy('alias')
            ->get();

        return response()->json(['data' => $aliases]);
    }

    public function show(ColumnAlias $columnAlias): JsonResponse
    {
        return response()->json(['data' => $columnAlias]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'alias'        => 'required|string|max:120',
            'target_field' => 'required|string|max:80',
            'context'      => 'nullable|in:project,work_item,material,equipment,period,s_curve',
            'is_active'    => 'sometimes|boolean',
        ]);

        $validated['alias'] = $this->normalizeAlias($validated['alias']);
        $this->ensureAliasCanBeSaved($validated);

        $alias = ColumnAlias::create([
            ...$validated,
            'is_active'  => $validated['is_active'] ?? true,
            'created_by' => $request->user()?->id,
        ]);

        return response()->json(['data' => $alias], 201);
    }

    public function update(Request $request, ColumnAlias $columnAlias): JsonResponse
    {
        $validated = $request->validate([
            'alias'        => 'sometimes|string|max:120',
            'context'      => 'nullable|in:project,work_item,material,equipment,period,s_curve',
            'is_active'    => 'sometimes|boolean',
            'target_field' => 'sometimes|string|max:80',
        ]);

        if (array_key_exists('alias', $validated)) {
            $validated['alias'] = $this->normalizeAlias($validated['alias']);
        }

        $payload = array_merge(
            $columnAlias->only(['alias', 'target_field', 'context', 'is_active']),
            $validated,
        );

        $this->ensureAliasCanBeSaved($payload, $columnAlias);
        $columnAlias->update($validated);

        return response()->json(['data' => $columnAlias->fresh()]);
    }

    public function destroy(ColumnAlias $columnAlias): JsonResponse
    {
        $columnAlias->update(['is_active' => false]);

        return response()->json([
            'message' => 'Alias berhasil dinonaktifkan.',
            'data' => $columnAlias->fresh(),
        ]);
    }

    private function normalizeAlias(string $alias): string
    {
        $normalized = $this->mapper()->normalizeHeader($alias);

        if ($normalized === '') {
            throw ValidationException::withMessages([
                'alias' => 'Alias tidak valid setelah dinormalisasi.',
            ]);
        }

        return $normalized;
    }

    private function ensureAliasCanBeSaved(array $payload, ?ColumnAlias $current = null): void
    {
        $this->ensureTargetFieldAllowed($payload['target_field'], $payload['context'] ?? null);
        $this->ensureAliasDoesNotConflictWithBuiltin($payload);

        $query = ColumnAlias::query()
            ->where('alias', $payload['alias'])
            ->where('context', $payload['context'] ?? null);

        if ($current !== null) {
            $query->whereKeyNot($current->id);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'alias' => 'Alias sudah ada untuk context tersebut.',
            ]);
        }
    }

    private function ensureAliasDoesNotConflictWithBuiltin(array $payload): void
    {
        $context = $payload['context'] ?? null;

        if ($context === null) {
            return;
        }

        $builtinAliases = WorkbookFieldMapper::builtinAliases()[$context] ?? [];
        $builtinTarget = $builtinAliases[$payload['alias']] ?? null;

        if ($builtinTarget === null) {
            return;
        }

        if ($builtinTarget === $payload['target_field']) {
            throw ValidationException::withMessages([
                'alias' => 'Alias sudah tersedia sebagai alias bawaan sistem.',
            ]);
        }

        throw ValidationException::withMessages([
            'alias' => "Alias sudah dipakai sebagai alias bawaan untuk field {$builtinTarget}.",
        ]);
    }

    private function ensureTargetFieldAllowed(string $targetField, ?string $context): void
    {
        $allowed = $this->allowedTargetFields($context);

        if (!in_array($targetField, $allowed, true)) {
            throw ValidationException::withMessages([
                'target_field' => 'Target field tidak valid untuk context tersebut.',
            ]);
        }
    }

    private function allowedTargetFields(?string $context): array
    {
        if ($context !== null) {
            return $this->mapper()->knownFields($context);
        }

        $fields = [];
        foreach (self::CONTEXTS as $itemContext) {
            $fields = array_merge($fields, $this->mapper()->knownFields($itemContext));
        }

        return array_values(array_unique($fields));
    }

    private function mapper(): WorkbookFieldMapper
    {
        return $this->fieldMapper ?? new WorkbookFieldMapper();
    }
}
