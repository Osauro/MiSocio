<?php

namespace App\Services;

use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use App\Models\TenantConfig;
use Exception;

class PrinterService
{
    protected ?Printer $printer = null;
    protected ?TenantConfig $config = null;
    protected string $error = '';

    /**
     * Conectar a la impresora según la configuración del tenant
     */
    public function connect(int $tenantId): bool
    {
        $this->config = TenantConfig::where('tenant_id', $tenantId)->first();

        if (!$this->config || !$this->config->impresora_nombre) {
            $this->error = 'No hay impresora configurada';
            return false;
        }

        try {
            $connector = $this->getConnector();
            $this->printer = new Printer($connector);
            return true;
        } catch (Exception $e) {
            $this->error = 'Error al conectar: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Obtener el conector adecuado según el nombre de la impresora
     */
    protected function getConnector()
    {
        $nombre = $this->config->impresora_nombre;

        // Si es una IP (formato IP:PUERTO)
        if (preg_match('/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}):?(\d+)?$/', $nombre, $matches)) {
            $ip = $matches[1];
            $puerto = $matches[2] ?? 9100;
            return new NetworkPrintConnector($ip, (int) $puerto);
        }

        // En Windows, usar WindowsPrintConnector
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return new WindowsPrintConnector($nombre);
        }

        // En Linux, usar el archivo del dispositivo
        if (file_exists('/dev/usb/lp0')) {
            return new FilePrintConnector('/dev/usb/lp0');
        }

        // Intentar con CUPS
        return new FilePrintConnector("/tmp/escpos_print_{$nombre}");
    }

    /**
     * Imprimir ticket de venta
     */
    public function imprimirVenta(array $venta): bool
    {
        if (!$this->printer) {
            $this->error = 'Impresora no conectada';
            return false;
        }

        try {
            $ancho = $this->config->ancho_caracteres ?? 48;

            // Logo si existe
            if ($this->config->logo && file_exists(storage_path('app/public/' . $this->config->logo))) {
                try {
                    $logo = EscposImage::load(storage_path('app/public/' . $this->config->logo));
                    $this->printer->bitImage($logo);
                } catch (Exception $e) {
                    // Continuar sin logo
                }
            }

            // Encabezado
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->setEmphasis(true);
            $this->printer->text(($this->config->nombre_tienda ?? 'MI TIENDA') . "\n");
            $this->printer->setEmphasis(false);

            if ($this->config->direccion) {
                $this->printer->text($this->config->direccion . "\n");
            }
            if ($this->config->telefono) {
                $this->printer->text("Tel: " . $this->config->telefono . "\n");
            }
            if ($this->config->nit) {
                $this->printer->text("NIT: " . $this->config->nit . "\n");
            }

            $this->printer->text(str_repeat('=', $ancho) . "\n");

            // Información de la venta
            $this->printer->setJustification(Printer::JUSTIFY_LEFT);
            $this->printer->text("Venta #: " . ($venta['numero'] ?? $venta['id']) . "\n");
            $this->printer->text("Fecha: " . ($venta['fecha'] ?? date('d/m/Y H:i')) . "\n");

            if (!empty($venta['cliente'])) {
                $this->printer->text("Cliente: " . $venta['cliente'] . "\n");
            }

            $this->printer->text(str_repeat('-', $ancho) . "\n");

            // Items
            $this->printer->setEmphasis(true);
            $this->printer->text($this->formatearLinea('PRODUCTO', 'CANT', 'TOTAL', $ancho) . "\n");
            $this->printer->setEmphasis(false);
            $this->printer->text(str_repeat('-', $ancho) . "\n");

            foreach ($venta['items'] ?? [] as $item) {
                // Primera línea: nombre del producto
                $nombre = mb_substr($item['nombre'] ?? $item['producto'], 0, $ancho - 15);
                $this->printer->text($nombre . "\n");

                // Segunda línea: cantidad x precio = subtotal
                $detalle = sprintf(
                    "  %s x Bs.%.2f",
                    $item['cantidad'],
                    $item['precio_unitario'] ?? $item['precio']
                );
                $subtotal = sprintf("Bs.%.2f", $item['subtotal'] ?? ($item['cantidad'] * ($item['precio_unitario'] ?? $item['precio'])));
                $this->printer->text($this->formatearLinea($detalle, '', $subtotal, $ancho) . "\n");
            }

            $this->printer->text(str_repeat('-', $ancho) . "\n");

            // Totales
            $this->printer->setJustification(Printer::JUSTIFY_RIGHT);
            $this->printer->setEmphasis(true);
            $this->printer->text("TOTAL: Bs. " . number_format($venta['total'] ?? 0, 2) . "\n");
            $this->printer->setEmphasis(false);

            if (!empty($venta['pago'])) {
                $this->printer->text("Pago: Bs. " . number_format($venta['pago'], 2) . "\n");
                $cambio = ($venta['pago'] ?? 0) - ($venta['total'] ?? 0);
                if ($cambio > 0) {
                    $this->printer->text("Cambio: Bs. " . number_format($cambio, 2) . "\n");
                }
            }

            $this->printer->text(str_repeat('=', $ancho) . "\n");

            // Pie de ticket
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->text("¡Gracias por su compra!\n");

            if ($this->config->propietario_celular) {
                $this->printer->text("WhatsApp: " . $this->config->propietario_celular . "\n");
            }

            $this->printer->feed(2);

            // Corte de papel
            if ($this->config->corte_automatico) {
                $this->printer->cut();
            }

            // Abrir cajón
            if ($this->config->abrir_cajon) {
                $this->printer->pulse();
            }

            return true;
        } catch (Exception $e) {
            $this->error = 'Error al imprimir: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Impresión de prueba
     */
    public function imprimirPrueba(): bool
    {
        if (!$this->printer) {
            $this->error = 'Impresora no conectada';
            return false;
        }

        try {
            $ancho = $this->config->ancho_caracteres ?? 48;

            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->text(str_repeat('=', $ancho) . "\n");
            $this->printer->setEmphasis(true);
            $this->printer->text("IMPRESION DE PRUEBA\n");
            $this->printer->setEmphasis(false);
            $this->printer->text(str_repeat('=', $ancho) . "\n\n");

            $this->printer->setJustification(Printer::JUSTIFY_LEFT);
            $this->printer->text("Tienda: " . ($this->config->nombre_tienda ?? 'No configurada') . "\n");
            $this->printer->text("Impresora: " . $this->config->impresora_nombre . "\n");
            $this->printer->text("Tipo: " . $this->config->impresora_tipo . "\n");
            $this->printer->text("Papel: " . $this->config->papel_tamano . "\n");
            $this->printer->text("Ancho: " . $ancho . " caracteres\n");
            $this->printer->text(str_repeat('-', $ancho) . "\n");
            $this->printer->text("Corte automatico: " . ($this->config->corte_automatico ? 'SI' : 'NO') . "\n");
            $this->printer->text("Abrir cajon: " . ($this->config->abrir_cajon ? 'SI' : 'NO') . "\n");
            $this->printer->text("Sonido: " . ($this->config->sonido_apertura ? 'SI' : 'NO') . "\n");

            $this->printer->text(str_repeat('=', $ancho) . "\n");
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->text("Configuracion correcta!\n");
            $this->printer->text(date('d/m/Y H:i:s') . "\n");

            $this->printer->feed(2);

            if ($this->config->corte_automatico) {
                $this->printer->cut();
            }

            if ($this->config->abrir_cajon) {
                $this->printer->pulse();
            }

            return true;
        } catch (Exception $e) {
            $this->error = 'Error en prueba: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Imprimir texto genérico
     */
    public function imprimirTexto(string $texto, bool $cortar = true): bool
    {
        if (!$this->printer) {
            $this->error = 'Impresora no conectada';
            return false;
        }

        try {
            $this->printer->text($texto);
            $this->printer->feed(2);

            if ($cortar && $this->config->corte_automatico) {
                $this->printer->cut();
            }

            return true;
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Cerrar conexión
     */
    public function close(): void
    {
        if ($this->printer) {
            try {
                $this->printer->close();
            } catch (Exception $e) {
                // Ignorar errores al cerrar
            }
            $this->printer = null;
        }
    }

    /**
     * Obtener último error
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * Formatear línea con columnas
     */
    protected function formatearLinea(string $izq, string $centro, string $der, int $ancho): string
    {
        $espacios = $ancho - mb_strlen($izq) - mb_strlen($centro) - mb_strlen($der);
        if ($espacios < 1) $espacios = 1;

        if ($centro) {
            $mitad = intval($espacios / 2);
            return $izq . str_repeat(' ', $mitad) . $centro . str_repeat(' ', $espacios - $mitad) . $der;
        }

        return $izq . str_repeat(' ', $espacios) . $der;
    }

    /**
     * Destructor - cerrar conexión automáticamente
     */
    public function __destruct()
    {
        $this->close();
    }
}
