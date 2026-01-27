# Componente KardexModal

## Descripción
Componente Livewire reutilizable que muestra los últimos movimientos del kardex de un producto en un modal.

## Ubicación
- **Componente PHP**: `app/Livewire/KardexModal.php`
- **Vista Blade**: `resources/views/livewire/kardex-modal.blade.php`

## Características
- ✅ Modal completamente estilizado con Bootstrap 5
- ✅ Muestra los últimos movimientos del kardex de un producto
- ✅ Soporte para productos eliminados (withTrashed)
- ✅ Formato automático de cantidades con cajas/unidades
- ✅ Información detallada: entrada, salida, stock anterior, saldo, precio, total
- ✅ Muestra usuario que realizó el movimiento
- ✅ Límite configurable de movimientos
- ✅ Diseño responsive
- ✅ Componente anidado - no requiere propiedades públicas en el componente padre

## Uso

### 1. Incluir el componente en tu vista Blade

Agrega el componente al final de tu vista principal:

```blade
<div>
    <!-- Tu contenido principal -->
    
    <!-- Componente anidado de Kardex Modal -->
    <livewire:kardex-modal />
</div>
```

### 2. Disparar el evento para mostrar el modal

Desde cualquier parte de tu vista, dispara el evento `mostrarKardex` con el ID del producto:

#### Opción 1: Usando Alpine.js (recomendado)
```blade
<button x-on:click="$dispatch('mostrarKardex', { productoId: {{ $producto->id }} })">
    <i class="fa-solid fa-clock-rotate-left"></i> Ver Kardex
</button>
```

#### Opción 2: En un elemento de imagen
```blade
<img src="..." 
    x-on:click="$dispatch('mostrarKardex', { productoId: {{ $producto->id }} })"
    style="cursor: pointer;"
    title="Ver movimientos de Kardex">
```

### 3. Personalizar límite de movimientos (opcional)

Por defecto muestra los últimos 10 movimientos. Puedes cambiar este límite:

```blade
<button x-on:click="$dispatch('mostrarKardex', { productoId: {{ $producto->id }}, limite: 20 })">
    Ver más movimientos
</button>
```

## Ejemplos de implementación

### En el módulo de Productos
```blade
<!-- Botón en la tarjeta de producto -->
<button class="btn btn-sm btn-info" 
    x-on:click="$dispatch('mostrarKardex', { productoId: {{ $producto->id }} })">
    <i class="fa-solid fa-clock-rotate-left"></i> Kardex
</button>

<!-- Al final del archivo -->
<livewire:kardex-modal />
```

### En el módulo de Compras
```blade
<!-- Botón en el modal de detalles de compra -->
@foreach ($compraSeleccionada->compraItems as $item)
    <button class="btn btn-sm btn-info" 
        x-on:click="$dispatch('mostrarKardex', { productoId: {{ $item->producto_id }} })"
        title="Ver movimientos de Kardex">
        <i class="fa-solid fa-clock-rotate-left"></i>
    </button>
@endforeach

<!-- Al final del archivo -->
<livewire:kardex-modal />
```

### En el módulo de Ventas
```blade
<!-- Botón en el carrito de ventas -->
<button class="btn btn-sm btn-info" 
    x-on:click="$dispatch('mostrarKardex', { productoId: {{ $item->producto_id }} })">
    Historial
</button>

<!-- Al final del archivo -->
<livewire:kardex-modal />
```

## Estructura del Modal

El modal muestra la siguiente información:

| Columna | Descripción |
|---------|-------------|
| Fecha | Fecha y hora del movimiento |
| Entrada | Cantidad que ingresó (badge verde) |
| Salida | Cantidad que salió (badge rojo) |
| Anterior | Stock antes del movimiento |
| Saldo | Stock después del movimiento |
| Precio | Precio unitario del movimiento |
| Total | Precio total del movimiento |
| Observación | Descripción del movimiento |
| Usuario | Usuario que realizó el movimiento |

## Personalización

### Cambiar colores del header
Edita el archivo `resources/views/livewire/kardex-modal.blade.php`:

```blade
<div class="modal-header bg-info text-white">
    <!-- Cambia bg-info por bg-primary, bg-success, bg-danger, etc. -->
```

### Cambiar límite por defecto
Edita el archivo `app/Livewire/KardexModal.php`:

```php
public $limite = 10; // Cambia el número según necesites
```

### Agregar filtros o búsqueda
Puedes extender el componente agregando propiedades públicas:

```php
public $fechaDesde = null;
public $fechaHasta = null;
public $tipoMovimiento = 'todos'; // 'entrada', 'salida', 'todos'
```

## Dependencias

- Laravel 11+
- Livewire 3+
- Bootstrap 5
- Alpine.js
- Font Awesome (para iconos)

## Notas importantes

1. **Multi-tenant**: El componente respeta el scope de tenant actual
2. **Productos eliminados**: Puede mostrar movimientos de productos eliminados (soft delete)
3. **Formato de cantidades**: Utiliza los accessors del modelo Kardex para formatear cajas/unidades
4. **Performance**: Limita automáticamente los resultados para evitar cargas pesadas

## Solución de problemas

### El modal no aparece
- Verifica que Alpine.js esté cargado correctamente
- Asegúrate de que el componente `<livewire:kardex-modal />` esté incluido en la vista
- Revisa la consola del navegador para errores JavaScript

### Los movimientos no se formatean correctamente
- Verifica que el modelo `Kardex` tenga los accessors: `entrada_formateado`, `salida_formateado`, `saldo_formateado`, `anterior_formateado`
- Asegúrate de que el producto tenga configurado correctamente `cantidad` y `medida`

### El modal se muestra detrás de otros elementos
- Verifica que no haya conflictos de z-index
- El backdrop usa `z-index: 1040` y el modal `z-index: 1050`
