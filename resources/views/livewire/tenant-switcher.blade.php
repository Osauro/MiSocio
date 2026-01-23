<div>
    @if($tenants && $tenants->count() > 1)
    <div class="dropdown">
        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="tenantDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="iconly-Location icli"></i>
            <span>{{ currentTenant()?->name ?? 'Seleccionar tienda' }}</span>
        </button>
        <ul class="dropdown-menu" aria-labelledby="tenantDropdown">
            @foreach($tenants as $tenant)
            <li>
                <a class="dropdown-item {{ $tenant->id == $currentTenantId ? 'active' : '' }}"
                   href="#"
                   wire:click.prevent="switchTenant({{ $tenant->id }})">
                    <i class="iconly-{{ $tenant->id == $currentTenantId ? 'Tick-Square' : 'Location' }} icli me-2"></i>
                    {{ $tenant->name }}
                    @if($tenant->id == $currentTenantId)
                    <span class="badge bg-success ms-2">Actual</span>
                    @endif
                </a>
            </li>
            @endforeach
        </ul>
    </div>
    @endif
</div>
