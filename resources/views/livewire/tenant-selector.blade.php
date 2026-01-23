<div>
    @if($showOverlay)
    <!-- Overlay de pantalla completa -->
    <div class="tenant-selector-overlay" wire:click="close">
        <div class="overlay-content" wire:click.stop>
            <div class="overlay-header">
                <h3><i class="fa-solid fa-store me-2"></i>Cambiar de Tienda</h3>
                <button type="button" class="btn-close-overlay" wire:click="close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="tenants-grid" data-count="{{ count($tenants) }}">
                @foreach($tenants as $tenant)
                <button type="button"
                        class="tenant-btn {{ $tenant->id == $currentTenantId ? 'active' : '' }}"
                        wire:click="selectTenant({{ $tenant->id }})"
                        wire:loading.attr="disabled"
                        style="--tenant-color: {{ getThemeColor($tenant->theme_number) }};">

                    <div class="tenant-btn-header" style="background: {{ getThemeColor($tenant->theme_number) }};">
                        <i class="fa-solid fa-store-alt"></i>
                        @if($tenant->id == $currentTenantId)
                        <span class="active-indicator">
                            <i class="fa-solid fa-circle-check"></i>
                        </span>
                        @endif

                        <div class="loading-spinner" wire:loading wire:target="selectTenant({{ $tenant->id }})">
                            <i class="fa-solid fa-spinner fa-spin"></i>
                        </div>
                    </div>

                    <div class="tenant-btn-body">
                        <h4>{{ $tenant->name }}</h4>
                        <span class="tenant-role-badge">
                            <i class="fa-solid fa-user-tag"></i>
                            {{ ucfirst($tenant->pivot->role) }}
                        </span>
                    </div>
                </button>
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
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            padding: 10px 0;
        }

        .tenants-grid[data-count="1"],
        .tenants-grid[data-count="2"] {
            justify-content: center;
            grid-template-columns: repeat(auto-fit, minmax(280px, 300px));
        }

        .tenant-btn {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 16px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            display: flex;
            flex-direction: column;
            text-align: left;
            padding: 0;
            width: 100%;
        }

        .tenant-btn:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
            border-color: var(--tenant-color);
        }

        .tenant-btn.active {
            border-color: var(--tenant-color);
            border-width: 3px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        }

        .tenant-btn-header {
            padding: 40px 20px;
            text-align: center;
            color: white;
            position: relative;
            background: linear-gradient(135deg, var(--tenant-color) 0%, var(--tenant-color) 100%);
        }

        .tenant-btn-header i {
            font-size: 56px;
            opacity: 0.95;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
        }

        .active-indicator {
            position: absolute;
            top: 12px;
            right: 12px;
            background: white;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .active-indicator i {
            font-size: 18px;
            color: #4CAF50;
            filter: none;
        }

        .loading-spinner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.7);
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .loading-spinner i {
            font-size: 28px;
            color: white;
            filter: none;
        }

        .tenant-btn:disabled {
            cursor: not-allowed;
            opacity: 0.7;
        }

        .tenant-btn-body {
            padding: 24px 20px;
            background: white;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .tenant-btn-body h4 {
            margin: 0;
            color: #2c3e50;
            font-size: 20px;
            font-weight: 700;
            line-height: 1.3;
        }

        .tenant-role-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
            color: #5a6c7d;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-transform: capitalize;
            align-self: flex-start;
        }

        .tenant-role-badge i {
            font-size: 12px;
        }

        @media (max-width: 1200px) {
            .tenants-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
            }
        }

        @media (max-width: 768px) {
            .overlay-content {
                padding: 20px;
                width: 95%;
            }

            .tenants-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 16px;
            }

            .tenants-grid[data-count="1"],
            .tenants-grid[data-count="2"] {
                grid-template-columns: repeat(2, 1fr);
            }

            .overlay-header h3 {
                font-size: 20px;
            }

            .tenant-btn-header {
                padding: 30px 15px;
            }

            .tenant-btn-header i {
                font-size: 42px;
            }

            .tenant-btn-body {
                padding: 18px 15px;
            }

            .tenant-btn-body h4 {
                font-size: 16px;
            }

            .tenant-role-badge {
                font-size: 11px;
                padding: 6px 10px;
            }
        }
    </style>
    @endif
</div>
