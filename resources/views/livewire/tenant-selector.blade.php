<div>
    @if($showOverlay)
    <!-- Overlay de pantalla completa -->
    <div class="tenant-selector-overlay" wire:click="close">
        <div class="overlay-content" wire:click.stop>
            <div class="overlay-header">
                <h3><i class="fa-solid fa-store me-2"></i>Selecciona tu Tienda</h3>
                <button type="button" class="btn-close-overlay" wire:click="close">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>

            <div class="tenants-grid">
                @foreach($tenants as $tenant)
                <div class="tenant-card {{ $tenant->id == $currentTenantId ? 'active' : '' }}"
                     x-data="{ tenantId: {{ $tenant->id }} }"
                     x-on:click="$wire.selectTenant(tenantId).then(() => window.location.reload())"
                     style="border-color: {{ $tenant->theme_color }};">
                    <div class="tenant-card-header" style="background: linear-gradient(135deg, {{ $tenant->theme_color }} 0%, {{ $tenant->theme_color }}dd 100%);">
                        <i class="fa-solid fa-store"></i>
                    </div>
                    <div class="tenant-card-body">
                        <h4>{{ $tenant->name }}</h4>
                        @if($tenant->id == $currentTenantId)
                        <span class="badge-active">
                            <i class="fa-solid fa-check me-1"></i>Activa
                        </span>
                        @endif
                    </div>
                    <div class="tenant-card-footer">
                        <small class="text-muted">{{ $tenant->pivot->role }}</small>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <style>
        .tenant-selector-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .overlay-content {
            background: white;
            border-radius: 16px;
            padding: 30px;
            max-width: 900px;
            width: 90%;
            max-height: 85vh;
            overflow-y: auto;
            animation: slideUp 0.3s ease;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .overlay-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .overlay-header h3 {
            margin: 0;
            color: #333;
            font-size: 24px;
            font-weight: 600;
        }

        .btn-close-overlay {
            background: transparent;
            border: none;
            font-size: 24px;
            color: #666;
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .btn-close-overlay:hover {
            background: rgba(0, 0, 0, 0.05);
            color: #333;
        }

        .tenants-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 10px 0;
        }

        .tenant-card {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .tenant-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .tenant-card.active {
            border-width: 3px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .tenant-card-header {
            padding: 30px;
            text-align: center;
            color: white;
        }

        .tenant-card-header i {
            font-size: 48px;
            opacity: 0.9;
        }

        .tenant-card-body {
            padding: 20px;
            text-align: center;
            background: white;
        }

        .tenant-card-body h4 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 18px;
            font-weight: 600;
        }

        .badge-active {
            display: inline-block;
            background: #4CAF50;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .tenant-card-footer {
            padding: 12px 20px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            text-align: center;
        }

        .tenant-card-footer small {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        @media (max-width: 768px) {
            .overlay-content {
                padding: 20px;
                width: 95%;
            }

            .tenants-grid {
                grid-template-columns: 1fr;
            }

            .overlay-header h3 {
                font-size: 20px;
            }
        }
    </style>
    @endif
</div>
