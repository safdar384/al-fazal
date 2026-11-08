{{--
  $listId, $items, $usedNames, $nameField, $destroyUrlPrefix
  optional: $piecesField (for unit per pack label)
  optional: $packingLabel = true (show Packing · Qty Unit)
--}}
<div class="border-top pt-3 mt-1">
    <label class="form-label fw-semibold mb-2">Existing Items</label>
    <div id="{{ $listId }}" class="list-group list-group-flush product-lookup-list" style="max-height: 200px; overflow-y: auto;">
        @forelse($items as $item)
            @php
                $name = $item->{$nameField};
                $isUsed = in_array(strtolower((string) $name), $usedNames ?? [], true);
                $label = $name;
                if (!empty($packingLabel) && method_exists($item, 'displayLabel')) {
                    $label = $item->displayLabel();
                } elseif (!empty($piecesField) && isset($item->{$piecesField})) {
                    $pcs = (int) $item->{$piecesField};
                    $label = $name.' = '.$pcs.' '.($pcs === 1 ? 'piece' : 'pieces');
                }
            @endphp
            <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 product-lookup-item"
                 data-id="{{ $item->id }}"
                 data-name="{{ $name }}">
                <span>
                    {{ $label }}
                    @if($isUsed)
                        <span class="badge bg-secondary bg-opacity-25 text-secondary ms-1">In use</span>
                    @endif
                </span>
                @if($isUsed)
                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Cannot delete: used by products">
                        <i class="bi bi-lock"></i>
                    </button>
                @else
                    <button type="button"
                            class="btn btn-sm btn-outline-danger btn-delete-product-lookup"
                            title="Remove"
                            data-id="{{ $item->id }}"
                            data-name="{{ $name }}"
                            data-url="{{ rtrim($destroyUrlPrefix, '/').'/'.$item->id }}"
                            data-select="{{ $selectId }}">
                        <i class="bi bi-trash"></i>
                    </button>
                @endif
            </div>
        @empty
            <div class="text-muted small product-lookup-empty">No items yet.</div>
        @endforelse
    </div>
    <small class="text-muted d-block mt-2">Jo item kisi product pe use ho raha ho, woh delete nahi hoga.</small>
</div>
