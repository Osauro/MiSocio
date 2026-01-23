<div>
    @if($tenants && $tenants->count() > 1)
        <!-- Selector de Tenants - Vista Desktop -->
        <div class="d-none d-md-block">
            <button type="button"
                    class="btn btn-primary"
                    onclick="Livewire.dispatch('openTenantSelector')"
                    style="font-size: 14px;">
                <i class="fa-solid fa-store me-2"></i>
                {{ currentTenant()?->name ?? 'Seleccionar Tienda' }}
            </button>
        </div>

        <!-- Selector de Tenants - Vista Móvil (Icono) -->
        <div class="dropdown d-md-none">
            <div class="user-img" onclick="Livewire.dispatch('openTenantSelector')" style="cursor: pointer;">
                <i class="fa-solid fa-store" style="color: {{ getThemeColor() }}; font-size: 24px;"></i>
            </div>
        </div>
    @elseif($tenants && $tenants->count() == 1)
        <!-- Solo 1 tenant - Desktop -->
        <span class="badge bg-info d-none d-md-inline-block" style="padding: 8px 15px; font-size: 13px;">
            <i class="fa-solid fa-store me-1"></i>
            {{ $tenants->first()->name }}
        </span>
        <!-- Solo 1 tenant - Móvil -->
        <div class="user-img d-md-none" style="cursor: default;">
            <i class="fa-solid fa-store" style="color: {{ getThemeColor($tenants->first()->theme_number) }}; font-size: 24px;"></i>
        </div>
    @endif
</div>
