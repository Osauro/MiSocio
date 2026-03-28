<?php

namespace App\Livewire;

use App\Models\Habitacion;
use App\Models\Hospedaje;
use App\Models\HospedajeHabitacion;
use App\Models\ModalidadHabitacion;
use App\Models\Movimiento;
use App\Models\TipoHabitacion;
use App\Models\TarifaHabitacion;
use App\Models\Cliente;
use App\Traits\RequiresTenant;
use App\Traits\SweetAlertTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class Habitaciones extends Component
{
    use RequiresTenant, SweetAlertTrait;

    // Filtros del panel
    public $filtroTipo = '';
    public $filtroEstado = '';

    // Modal de acción sobre habitación
    public bool $mostrarModal = false;
    public ?int $habitacionId = null;

    // ─── Flujo check-in ────────────────────────────────────────────
    // pasoCheckIn: 0 = modal normal, 1 = celular responsable, 2 = acompañantes, 3 = modalidad, 4 = confirmación
    public int $pasoCheckIn = 0;
    public float $totalCheckIn = 0;
    public float $montoPagoEfectivo = 0;
    public float $montoPagoOnline = 0;
    public bool $procesandoCheckIn = false;
    public float $saldoCaja = 0;
    public string $celularBusqueda = '';
    public bool $clienteEncontrado = false;

    // Datos del cliente en búsqueda actual
    public $clienteNombre = '';
    public $clienteCi = '';
    public $clienteCelular = '';
    public $clienteDireccion = '';
    public $clienteNit = '';
    public $clienteCorreo = '';
    public $clienteId = null;

    // Ocupantes de la habitación (el primero es el responsable)
    public $ocupantes = []; // [{cliente_id, nombre, celular, es_responsable}, ...]

    // Datos del hospedaje
    public $modalidad = '';
    public $unidades = 1;
    public $numeroPers = 1;
    public $observaciones = '';
    public $fechaSalidaEst = '';

    // ─── Check-out ─────────────────────────────────────────────────
    public $pagoEfectivo = 0;
    public $pagoOnline = 0;
    public $pagoCredito = 0;

    public function mount()
    {
        if (!hospedajesHabilitados()) {
            abort(403, 'El módulo de hospedajes no está habilitado.');
        }
    }

    public function getHabitacionesProperty()
    {
        return Habitacion::with([
                'tipoHabitacion',
                'tipoHabitacion.tarifas',
                'hospedajeActivo.cliente',
                'hospedajeActivo.habitaciones',
            ])
            ->when($this->filtroTipo,   fn($q) => $q->where('tipo_habitacion_id', $this->filtroTipo))
            ->when($this->filtroEstado, fn($q) => $q->where('estado', $this->filtroEstado))
            ->orderBy('piso')
            ->orderBy('numero')
            ->get();
    }

    public function getTiposProperty()
    {
        return TipoHabitacion::where('activo', true)->orderBy('nombre')->get();
    }

    public function getModalidadesProperty()
    {
        return ModalidadHabitacion::where('activo', true)->orderBy('nombre')->get();
    }

    // ─── Click sobre una habitación ───────────────────────────────
    public function clickHabitacion(int $id): void
    {
        $this->habitacionId = $id;
        $this->resetFormulario();

        $hab = Habitacion::with(['tipoHabitacion.tarifas'])->findOrFail($id);

        if ($hab->estado === 'disponible') {
            // Pre-seleccionar primera tarifa disponible
            $primeraTarifa = $hab->tipoHabitacion->tarifas->where('activo', true)->first();
            if ($primeraTarifa) {
                $this->modalidad = $primeraTarifa->modalidad;
            } else {
                // Fallback: primera modalidad activa
                $primera = ModalidadHabitacion::where('activo', true)->orderBy('nombre')->first();
                $this->modalidad = $primera?->nombre ?? '';
            }
            // Empezar en paso 1: celular
            $this->pasoCheckIn = 1;
        } else {
            $this->pasoCheckIn = 0;
        }

        $this->mostrarModal = true;
    }

    // ═══════════════════════════════════════════════════════════════
    // FLUJO CHECK-IN (3 PASOS)
    // ═══════════════════════════════════════════════════════════════

    // Buscar cliente manualmente con botón
    public function buscarCliente(): void
    {
        $this->celularBusqueda = trim($this->celularBusqueda);

        // Validar mínimo 6 caracteres
        if (strlen($this->celularBusqueda) < 6) {
            $this->toast('warning', 'Ingresa al menos 6 caracteres para buscar');
            return;
        }

        // Buscar por celular O por CI
        $cliente = Cliente::where(function($q) {
            $q->where('celular', $this->celularBusqueda)
              ->orWhere('ci', $this->celularBusqueda);
        })->first();

        if ($cliente) {
            // Cliente encontrado - cargar datos
            $this->clienteId = $cliente->id;
            $this->clienteNombre = $cliente->nombre;
            $this->clienteCelular = $cliente->celular ?? '';
            $this->clienteCi = $cliente->ci ?? '';
            $this->clienteDireccion = $cliente->direccion ?? '';
            $this->clienteNit = $cliente->nit ?? '';
            $this->clienteCorreo = $cliente->correo ?? '';
            $this->clienteEncontrado = true;
        } else {
            // No encontrado - preparar formulario vacío
            $this->clienteEncontrado = false;
            $this->clienteId = null;
            $this->clienteNombre = '';
            $this->clienteCelular = '';
            $this->clienteCi = '';
            $this->clienteDireccion = '';
            $this->clienteNit = '';
            $this->clienteCorreo = '';
        }
    }

    // Agregar cliente a la habitación
    public function agregarOcupante(): void
    {
        // Validar nombre obligatorio
        if (empty(trim($this->clienteNombre))) {
            $this->toast('error', 'El nombre es obligatorio');
            return;
        }

        // Si no se encontró, buscar por los datos del formulario antes de crear
        if (!$this->clienteEncontrado) {
            // Usar los valores del formulario
            $celular = trim($this->clienteCelular) ?: null;
            $ci = trim($this->clienteCi) ?: null;
            $nit = trim($this->clienteNit) ?: null;

            // Buscar si existe un cliente con el celular, CI o NIT ingresados
            $clienteExistente = Cliente::where(function($query) use ($celular, $ci, $nit) {
                if ($celular) {
                    $query->orWhere('celular', $celular);
                }
                if ($ci) {
                    $query->orWhere('ci', $ci);
                }
                if ($nit) {
                    $query->orWhere('nit', $nit);
                }
            })->first();

            if ($clienteExistente) {
                // Cliente encontrado - actualizar sus datos
                $clienteExistente->update([
                    'nombre'    => trim($this->clienteNombre),
                    'celular'   => $celular,
                    'ci'        => $ci,
                    'direccion' => trim($this->clienteDireccion) ?: null,
                    'nit'       => $nit,
                    'correo'    => trim($this->clienteCorreo) ?: null,
                ]);
                $this->clienteId = $clienteExistente->id;
            } else {
                // Crear cliente nuevo
                $cliente = Cliente::create([
                    'tenant_id' => $this->getTenantId(),
                    'nombre'    => trim($this->clienteNombre),
                    'celular'   => $celular,
                    'ci'        => $ci,
                    'direccion' => trim($this->clienteDireccion) ?: null,
                    'nit'       => $nit,
                    'correo'    => trim($this->clienteCorreo) ?: null,
                ]);
                $this->clienteId = $cliente->id;
            }
        }

        // Verificar si ya fue agregado
        $yaAgregado = collect($this->ocupantes)->contains('cliente_id', $this->clienteId);
        if ($yaAgregado) {
            $this->toast('warning', 'Este cliente ya fue agregado a la habitación');
            return;
        }

        // Agregar a la lista de ocupantes
        $esResponsable = count($this->ocupantes) === 0;
        $this->ocupantes[] = [
            'cliente_id' => $this->clienteId,
            'nombre' => trim($this->clienteNombre),
            'celular' => trim($this->clienteCelular) ?: trim($this->clienteCi),
            'es_responsable' => $esResponsable,
        ];

        $mensaje = $esResponsable ? 'Responsable agregado' : 'Ocupante agregado';
        $this->toast('success', $mensaje);

        // Limpiar formulario
        $this->limpiarBuscador();

        // Emitir evento para devolver focus al buscador
        $this->dispatch('ocupante-agregado');
    }

    // Limpiar buscador
    public function limpiarBuscador(): void
    {
        $this->celularBusqueda = '';
        $this->clienteEncontrado = false;
        $this->clienteId = null;
        $this->clienteNombre = '';
        $this->clienteCelular = '';
        $this->clienteCi = '';
        $this->clienteDireccion = '';
        $this->clienteNit = '';
        $this->clienteCorreo = '';
    }

    // Eliminar ocupante
    public function eliminarOcupante($index): void
    {
        if (isset($this->ocupantes[$index])) {
            unset($this->ocupantes[$index]);
            $this->ocupantes = array_values($this->ocupantes);

            // Reasignar responsable al primero si se eliminó el responsable
            if (!empty($this->ocupantes)) {
                $this->ocupantes[0]['es_responsable'] = true;
                for ($i = 1; $i < count($this->ocupantes); $i++) {
                    $this->ocupantes[$i]['es_responsable'] = false;
                }
            }

            $this->toast('info', 'Ocupante eliminado');
        }
    }

    // Paso 1 → Paso 2 (modalidad)
    public function avanzarPaso1(): void
    {
        // Validar que haya al menos un ocupante
        if (empty($this->ocupantes)) {
            $this->toast('error', 'Debes agregar al menos un ocupante (responsable) a la habitación');
            return;
        }

        $this->pasoCheckIn = 3;
    }

    // ═══════════════════════════════════════════════════════════════
    // PASO 3: MODALIDAD
    // ═══════════════════════════════════════════════════════════════

    // Paso 2 → Paso 3 (confirmación)
    public function avanzarPaso2(): void
    {
        // Puede continuar sin acompañantes
        $this->pasoCheckIn = 3;
    }

    // Paso 3 → Paso 4: seleccionaron modalidad, calcular hora salida
    public function avanzarPaso3(): void
    {
        $this->validate([
            'modalidad' => 'required|string|max:80',
        ]);

        // Calcular número de personas: total de ocupantes
        $this->numeroPers = count($this->ocupantes);
        $this->unidades = 1;

        // Calcular hora de salida estimada según modalidad
        $mod = ModalidadHabitacion::where('nombre', $this->modalidad)->where('activo', true)->first();
        if ($mod) {
            $horasTotales = $mod->horas * $this->unidades;
            $this->fechaSalidaEst = now()->addHours($horasTotales)->format('Y-m-d\TH:i');
        } else {
            $this->fechaSalidaEst = now()->addHours(12)->format('Y-m-d\TH:i');
        }

        // Calcular total
        $hab = Habitacion::findOrFail($this->habitacionId);
        $tarifa = TarifaHabitacion::where('tipo_habitacion_id', $hab->tipo_habitacion_id)
            ->where('modalidad', $this->modalidad)
            ->where('activo', true)
            ->first();

        if ($tarifa) {
            // Si la tarifa es por persona, multiplicar precio por número de personas
            if ($tarifa->precio_por_persona) {
                $this->totalCheckIn = round($tarifa->precio * $this->numeroPers * $this->unidades, 2);
            } else {
                // Si no es por persona, el precio es fijo por unidad
                $this->totalCheckIn = round($tarifa->precio * $this->unidades, 2);
            }
        } else {
            $this->totalCheckIn = 0;
        }

        $this->montoPagoEfectivo = $this->totalCheckIn;
        $this->montoPagoOnline   = 0;

        $ultimo = Movimiento::latest()->first();
        $this->saldoCaja = $ultimo ? $ultimo->saldo : 0;

        $this->pasoCheckIn = 4;
    }

    // Retroceder pasos
    public function retrocederPaso(): void
    {
        if ($this->pasoCheckIn === 3) {
            $this->pasoCheckIn = 1;
        } elseif ($this->pasoCheckIn === 4) {
            $this->pasoCheckIn = 3;
        }
    }

    public function updatedMontoPagoEfectivo($value)
    {
        $efectivo = max(0, round((float) $value, 2));
        $total    = $this->totalCheckIn;

        if ($efectivo >= $total) {
            $this->montoPagoEfectivo = $total;
            $this->montoPagoOnline   = 0;
        } else {
            $online = round((float) $this->montoPagoOnline, 2);
            if ($efectivo + $online > $total) {
                $this->montoPagoOnline = round($total - $efectivo, 2);
            }
        }
    }

    public function updatedMontoPagoOnline($value)
    {
        $online = max(0, round((float) $value, 2));
        $total  = $this->totalCheckIn;

        if ($online >= $total) {
            $this->montoPagoOnline   = $total;
            $this->montoPagoEfectivo = 0;
        } else {
            $efectivo = round((float) $this->montoPagoEfectivo, 2);
            if ($online + $efectivo > $total) {
                $this->montoPagoEfectivo = round($total - $online, 2);
            }
        }
    }

    // Paso 4: confirmar pago y registrar check-in
    public function confirmarCheckIn(): void
    {
        $efectivo   = round((float) $this->montoPagoEfectivo, 2);
        $online     = round((float) $this->montoPagoOnline, 2);
        $total      = $this->totalCheckIn;
        $pagado     = $efectivo + $online;

        // Validar que el pago cubra el total (no se acepta crédito)
        if ($pagado < $total) {
            $this->toast('error', 'El pago debe cubrir el total. No se acepta crédito en este servicio.');
            return;
        }

        $hab = Habitacion::findOrFail($this->habitacionId);

        if ($hab->estado !== 'disponible') {
            $this->toast('error', 'La habitación ya no está disponible.');
            return;
        }

        $this->procesandoCheckIn = true;

        try {
            DB::beginTransaction();

            // Obtener el responsable (primer ocupante) como cliente_id
            $responsable = collect($this->ocupantes)->firstWhere('es_responsable', true);
            $clienteResponsableId = $responsable ? $responsable['cliente_id'] : ($this->ocupantes[0]['cliente_id'] ?? null);

            // Acompañantes = todos los ocupantes excepto el responsable
            $acompanantes = collect($this->ocupantes)->where('es_responsable', false)->values()->toArray();

            // Tarifa y cálculo de precio
            $tarifa = TarifaHabitacion::where('tipo_habitacion_id', $hab->tipo_habitacion_id)
                ->where('modalidad', $this->modalidad)
                ->where('activo', true)
                ->first();

            $subtotal = $this->totalCheckIn;
            $precioUnit = $tarifa ? $tarifa->precio : 0;

            // Crear hospedaje
            $hospedaje = Hospedaje::create([
                'tenant_id'             => $this->getTenantId(),
                'user_id'               => Auth::id(),
                'cliente_id'            => $clienteResponsableId,
                'acompanantes'          => count($acompanantes) > 0 ? $acompanantes : null,
                'estado'                => 'activo',
                'fecha_entrada'         => now(),
                'fecha_salida_estimada' => $this->fechaSalidaEst ?: null,
                'numero_personas'       => $this->numeroPers,
                'observaciones'         => $this->observaciones ?: null,
                'total'                 => $subtotal,
                'efectivo'              => $efectivo,
                'online'                => $online,
                'credito'               => 0,
            ]);

            HospedajeHabitacion::create([
                'hospedaje_id'    => $hospedaje->id,
                'habitacion_id'   => $hab->id,
                'tarifa_id'       => $tarifa?->id,
                'modalidad'       => $this->modalidad,
                'unidades'        => $this->unidades,
                'numero_personas' => $this->numeroPers,
                'precio_unitario' => $precioUnit,
                'subtotal'        => $subtotal,
            ]);

            // Cambiar estado habitación
            $hab->update(['estado' => 'ocupada']);

            // Registrar movimientos
            $nombreCliente = $cliente?->nombre ?? 'Huésped';
            $folio         = '#' . str_pad($hospedaje->numero_folio, 4, '0', STR_PAD_LEFT);
            $detalleBase   = 'Check-in Hab. ' . $hab->numero . ' ' . $folio . ' - ' . $nombreCliente;

            if ($efectivo > 0) {
                Movimiento::create([
                    'tenant_id' => $this->getTenantId(),
                    'user_id'   => Auth::id(),
                    'detalle'   => $detalleBase,
                    'ingreso'   => $efectivo,
                    'egreso'    => 0,
                ]);
            }

            if ($online > 0) {
                Movimiento::create([
                    'tenant_id' => $this->getTenantId(),
                    'user_id'   => Auth::id(),
                    'detalle'   => $detalleBase . ' (Online)',
                    'ingreso'   => $online,
                    'egreso'    => 0,
                ]);
            }

            DB::commit();

            $this->cerrarModal();

            $msg = 'Check-in registrado — Hab. ' . $hab->numero;
            $this->toast('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->procesandoCheckIn = false;
            $this->toast('error', 'Error al registrar check-in: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // CHECK-OUT
    // ═══════════════════════════════════════════════════════════════
    public function solicitarEntrega(): void
    {
        $this->dispatch('swal:confirm', [
            'title' => '¿Entregar habitación?',
            'text' => 'La habitación pasará a estado de limpieza.',
            'confirmButtonText' => 'Sí, entregar',
            'confirmButtonColor' => '#198754',
            'event' => 'ejecutarCheckOut',
            'id' => $this->habitacionId,
        ]);
    }

    #[\Livewire\Attributes\On('ejecutarCheckOut')]
    public function confirmarCheckOut($id = null): void
    {
        $habId = $id ?? $this->habitacionId;
        $hab       = Habitacion::with('hospedajeActivo')->findOrFail($habId);
        $hospedaje = $hab->hospedajeActivo;

        if ($hospedaje) {
            $hospedaje->update([
                'estado'            => 'finalizado',
                'fecha_salida_real' => now(),
            ]);
        }

        $hab->update(['estado' => 'limpieza']);

        $this->cerrarModal();
        $this->toast('success', 'Habitación entregada correctamente');
    }

    // ─── Avanzar estado ───────────────────────────────────────────
    public function avanzarEstado(): void
    {
        $hab = Habitacion::findOrFail($this->habitacionId);

        if ($hab->estado === 'ocupada') {
            $this->toast('warning', 'Usa "Finalizar" para hacer el check-out.');
            return;
        }

        $siguiente = Habitacion::CICLO_ESTADOS[$hab->estado] ?? 'disponible';
        $hab->update(['estado' => $siguiente]);

        $this->cerrarModal();
        $this->toast('info', 'Habitación ' . $hab->numero . ' → ' . ucfirst($siguiente));
    }

    public function marcarDisponible(): void
    {
        $hab = Habitacion::findOrFail($this->habitacionId);
        $hab->update(['estado' => 'disponible']);
        $this->cerrarModal();
        $this->toast('success', 'Habitación ' . $hab->numero . ' disponible');
    }

    public function marcarMantenimiento(): void
    {
        $hab = Habitacion::findOrFail($this->habitacionId);
        $hab->update(['estado' => 'mantenimiento']);
        $this->cerrarModal();
        $this->toast('info', 'Habitación ' . $hab->numero . ' en mantenimiento');
    }

    // ─── Cerrar / reset ───────────────────────────────────────────
    public function cerrarModal(): void
    {
        $this->mostrarModal = false;
        $this->habitacionId = null;
        $this->resetFormulario();
    }

    private function resetFormulario(): void
    {
        $this->clienteNombre     = '';
        $this->clienteCi         = '';
        $this->clienteCelular    = '';
        $this->clienteId         = null;
        $this->clienteDireccion  = '';
        $this->clienteNit        = '';
        $this->clienteCorreo     = '';
        $this->celularBusqueda   = '';
        $this->clienteEncontrado = false;
        $this->ocupantes         = [];
        $this->modalidad         = '';
        $this->unidades          = 1;
        $this->numeroPers        = 1;
        $this->observaciones     = '';
        $this->fechaSalidaEst    = '';
        $this->pasoCheckIn       = 0;
        $this->totalCheckIn      = 0;
        $this->montoPagoEfectivo = 0;
        $this->montoPagoOnline   = 0;
        $this->procesandoCheckIn = false;
        $this->pagoEfectivo      = 0;
        $this->pagoOnline        = 0;
        $this->pagoCredito       = 0;
    }

    public function render()
    {
        $habitacion = $this->habitacionId
            ? Habitacion::with([
                'tipoHabitacion',
                'tipoHabitacion.tarifas',
                'hospedajeActivo.cliente',
                'hospedajeActivo.habitaciones',
            ])->find($this->habitacionId)
            : null;

        return view('livewire.habitaciones', [
            'habitaciones' => $this->habitaciones,
            'tipos'        => $this->tipos,
            'modalidades'  => $this->modalidades,
            'habitacion'   => $habitacion,
        ]);
    }
}
