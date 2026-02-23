# Información de Seeders

## Estado Actual: Sistema Limpio

El sistema está configurado para iniciar desde cero con solo lo esencial.

### Datos iniciales (después de `php artisan migrate:fresh --seed`):

**1. Usuario Landlord (Super Admin)**
- **Nombre:** Administrador LicoPOS
- **Celular:** 73010688
- **Contraseña:** 5421
- **Rol:** Super Admin (Landlord)
- **Acceso:** Panel de administración del sistema (/admin/home)

**2. Planes de Suscripción**
- Plan Demo (0 meses, Bs. 0)
- Plan Mensual (1 mes, Bs. 120)
- Plan Trimestral (3 meses, Bs. 330)
- Plan Semestral (6 meses, Bs. 630)
- Plan Anual (12 meses, Bs. 1,200)

### Datos NO incluidos (sistema limpio):
- ❌ Tenants de demostración
- ❌ Usuarios de prueba
- ❌ Productos
- ❌ Categorías
- ❌ Clientes
- ❌ Medidas

## Para habilitar datos de demostración:

Si necesitas datos de prueba para desarrollo, edita `database/seeders/DatabaseSeeder.php` y descomenta las líneas:

```php
$this->call([
    TenantSeeder::class,
    CategoriaSeeder::class,
    MedidaSeeder::class,
    ProductoSeeder::class,
    ClienteSeeder::class,
]);
```

## Flujo de usuario nuevo:

1. Usuario se registra en el sistema
2. Hace login → es redirigido a `/crear-tienda`
3. Selecciona un plan de suscripción
4. Si es Demo: se crea y activa automáticamente
5. Si es plan de pago: sube comprobante y espera verificación del landlord

## Gestión de Landlord:

El landlord puede:
- Ver todos los tenants en `/admin/tenants`
- Gestionar planes en `/admin/planes`
- Verificar pagos en `/admin/pagos`
