<x-layouts.tenant.theme>
    <div class="container-fluid" style="padding-top: 0 !important;">
        <div class="row starter-main" style="margin-top: 0 !important;">
            <div class="col-sm-12" style="padding-top: 0 !important;">

                <div class="card" style="margin-top: 0 !important;">
                    <div class="card-body text-center py-5">

                        <div class="mb-4">
                            <i class="fa-solid fa-lock fa-5x text-danger"></i>
                        </div>

                        <h2 class="text-danger fw-bold mb-2">Suscripción Vencida</h2>
                        <p class="text-muted fs-5 mb-1">
                            La suscripción de <strong>{{ currentTenant()?->name }}</strong> ha expirado.
                        </p>
                        <p class="text-muted mb-4">
                            No puedes realizar ventas ni compras hasta que el administrador renueve el plan.
                        </p>

                        <div class="alert alert-warning d-inline-block text-start" style="max-width: 480px;">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>
                            Comunícate con el administrador de tu tienda para renovar la suscripción y volver a operar con normalidad.
                        </div>

                        <div class="mt-4">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary">
                                    <i class="fa-solid fa-right-from-bracket me-1"></i>
                                    Cerrar sesión
                                </button>
                            </form>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</x-layouts.tenant.theme>
