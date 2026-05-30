<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Tutoriales extends Component
{
    const PLAYLIST_ID  = 'PLkrGsrYfhNiQ2AKu1I8WUR7YVLkbvC40M';
    const CACHE_KEY    = 'youtube_playlist_tutoriales';
    const CACHE_TTL    = 3600; // 1 hora

    public array  $videos      = [];
    public string $videoActivo = '';
    public string $tituloActivo = '';
    public bool   $cargando    = false;

    public function mount(): void
    {
        $this->videos = $this->obtenerVideos();
        if (!empty($this->videos)) {
            $this->videoActivo  = $this->videos[0]['id'];
            $this->tituloActivo = $this->videos[0]['titulo'];
        }
    }

    public function seleccionarVideo(string $id, string $titulo): void
    {
        $this->videoActivo  = $id;
        $this->tituloActivo = $titulo;
    }

    public function refrescarLista(): void
    {
        $this->cargando = true;
        Cache::forget(self::CACHE_KEY);
        $this->videos = $this->obtenerVideos();
        if (!empty($this->videos)) {
            $this->videoActivo  = $this->videos[0]['id'];
            $this->tituloActivo = $this->videos[0]['titulo'];
        }
        $this->cargando = false;
    }

    private function obtenerVideos(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->fetchPlaylist();
        });
    }

    private function fetchPlaylist(): array
    {
        $url = 'https://www.youtube.com/feeds/videos.xml?playlist_id=' . self::PLAYLIST_ID;

        try {
            $response = Http::timeout(10)->get($url);

            if (!$response->successful()) {
                Log::warning('Tutoriales: no se pudo obtener el feed de YouTube', ['status' => $response->status()]);
                return [];
            }

            $xml = simplexml_load_string($response->body());
            if (!$xml) {
                return [];
            }

            $xml->registerXPathNamespace('yt',    'http://www.youtube.com/xml/schemas/2015');
            $xml->registerXPathNamespace('media', 'http://search.yahoo.com/mrss/');

            $videos = [];
            foreach ($xml->entry as $entry) {
                $ytNodes   = $entry->children('http://www.youtube.com/xml/schemas/2015');
                $mediaNodes = $entry->children('http://search.yahoo.com/mrss/');

                $videoId = (string) $ytNodes->videoId;
                if (empty($videoId)) continue;

                $thumbnail = '';
                if (isset($mediaNodes->group->thumbnail)) {
                    $thumbnail = (string) $mediaNodes->group->thumbnail->attributes()['url'];
                }
                if (empty($thumbnail)) {
                    $thumbnail = "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg";
                }

                $videos[] = [
                    'id'        => $videoId,
                    'titulo'    => (string) $entry->title,
                    'thumbnail' => $thumbnail,
                    'publicado' => (string) $entry->published,
                ];
            }

            return $videos;
        } catch (\Throwable $e) {
            Log::error('Tutoriales: error al obtener feed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function render()
    {
        return view('livewire.tutoriales');
    }
}
