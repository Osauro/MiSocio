<?php

namespace App\Livewire\Landlord;

use App\Models\GaleriaImagen;
use App\Models\Producto;
use App\Traits\SweetAlertTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class GaleriaManager extends Component
{
    use WithPagination, WithFileUploads, SweetAlertTrait;

    public $search = '';
    public $perPage = 24;
    public $nuevaImagen;

    protected $listeners = ['eliminarImagen', 'guardarNombreImagen'];

    public function mount(): void
    {
        $this->perPage = $_COOKIE['paginateGaleria'] ?? 24;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedNuevaImagen(): void
    {
        if ($this->nuevaImagen) {
            $this->subirImagen();
        }
    }

    public function subirImagen(): void
    {
        $this->validate([
            'nuevaImagen' => 'required|image|max:10240',
        ], [
            'nuevaImagen.required' => 'Selecciona una imagen',
            'nuevaImagen.image'    => 'El archivo debe ser una imagen',
            'nuevaImagen.max'      => 'La imagen no puede superar 10 MB',
        ]);

        $filename = time() . '_' . uniqid() . '.jpg';
        $path = 'galeria/' . $filename;

        $processed = Image::read($this->nuevaImagen->getRealPath())
            ->cover(512, 512)
            ->toJpeg(90);

        Storage::disk('public')->put($path, (string) $processed);

        $img = GaleriaImagen::create([
            'url'         => $path,
            'nombre'      => null,
            'tags'        => [],
            'veces_usado' => 0,
            'subido_por'  => Auth::id(),
        ]);

        $this->nuevaImagen = null;
        $this->resetPage();
        $this->dispatch('swal:pedir-nombre', ['id' => $img->id]);
    }

    public function editarNombre(int $id): void
    {
        $this->dispatch('swal:pedir-nombre', ['id' => $id]);
    }

    public function guardarNombreImagen(int $id, string $nombre): void
    {
        $imagen = GaleriaImagen::findOrFail($id);
        $imagen->update(['nombre' => trim($nombre)]);
        $this->toast('success', 'Nombre guardado.');
    }

    public function confirmarEliminar(int $id): void
    {
        $this->dispatch('swal:confirm', [
            'id'                 => $id,
            'title'              => '¿Eliminar imagen?',
            'text'               => 'Se eliminará el archivo y los productos que la usen quedarán sin imagen.',
            'event'              => 'eliminarImagen',
            'confirmButtonText'  => 'Sí, eliminar',
            'confirmButtonColor' => '#d33',
        ]);
    }

    public function eliminarImagen(int $id): void
    {
        $imagen = GaleriaImagen::findOrFail($id);

        // Poner imagen = null en todos los productos que usen esta imagen
        Producto::where('imagen', $imagen->url)->update(['imagen' => null]);

        // Eliminar el archivo físico
        if (Storage::disk('public')->exists($imagen->url)) {
            Storage::disk('public')->delete($imagen->url);
        }

        $imagen->delete();

        $this->toast('success', 'Imagen eliminada.');
    }

    public function getImagenesProperty()
    {
        return GaleriaImagen::when($this->search, function ($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('tags', 'like', '%' . $this->search . '%');
            })
            ->orderByDesc('updated_at')
            ->paginate($this->perPage);
    }

    #[Layout('layouts.landlord.theme')]
    public function render()
    {
        return view('livewire.landlord.galeria-manager', [
            'imagenes' => $this->imagenes,
        ]);
    }
}
