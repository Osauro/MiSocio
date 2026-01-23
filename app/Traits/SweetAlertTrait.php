<?php

namespace App\Traits;

trait SweetAlertTrait
{
    /**
     * Mostrar alerta de éxito.
     */
    public function alertSuccess($title, $text = '')
    {
        $this->dispatch('swal:success', title: $title, text: $text);
    }

    /**
     * Mostrar alerta de error.
     */
    public function alertError($title, $text = '')
    {
        $this->dispatch('swal:error', title: $title, text: $text);
    }

    /**
     * Mostrar alerta de advertencia.
     */
    public function alertWarning($title, $text = '')
    {
        $this->dispatch('swal:warning', title: $title, text: $text);
    }

    /**
     * Mostrar alerta de información.
     */
    public function alertInfo($title, $text = '')
    {
        $this->dispatch('swal:info', title: $title, text: $text);
    }

    /**
     * Mostrar confirmación de eliminación.
     */
    public function confirmDelete($id, $title = '¿Está seguro?', $text = 'Esta acción no se puede revertir', $event = 'delete')
    {
        $this->dispatch('swal:confirm', [
            'id' => $id,
            'title' => $title,
            'text' => $text,
            'event' => $event,
        ]);
    }

    /**
     * Toast notification.
     */
    public function toast($type = 'success', $message = '')
    {
        $this->dispatch('swal:toast', [
            'type' => $type,
            'message' => $message,
        ]);
    }
}
