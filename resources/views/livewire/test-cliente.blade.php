<div>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3>Test Clientes</h3>
                <button class="btn btn-primary" wire:click="openModal">Abrir Modal</button>
            </div>
            <div class="card-body">
                <ul>
                    @foreach($clientes as $cliente)
                        <li>{{ $cliente->nombre }}</li>
                    @endforeach
                </ul>
                {{ $clientes->links() }}
            </div>
        </div>
    </div>

    @if($showModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Modal Test</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" class="form-control" wire:model="nombre" placeholder="Nombre">
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" wire:click="closeModal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
