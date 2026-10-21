<?php

/*
 * Nur was von Livewires Vorgaben abweicht. Laravel mischt diese Datei mit
 * der des Pakets, und zwar je Schlüssel der obersten Ebene: Deshalb steht
 * `temporary_file_upload` hier vollständig.
 */
return [

    /*
     * Hochladen im Panel (App\Support\Medien): Dokumente bis 20 MB, ein
     * Jahresbericht mit Fotos ist schnell größer als Livewires 12 MB. Die
     * Felder selbst setzen engere Grenzen (Bilder 10 MB). PHP muss dazu
     * passen: upload_max_filesize und post_max_size mindestens 20 MB.
     */
    'temporary_file_upload' => [
        'disk' => null,
        'rules' => ['required', 'file', 'max:20480'],
        'directory' => null,
        'middleware' => null,
        'preview_mimes' => [
            'png', 'gif', 'bmp', 'wav', 'mp4',
            'mov', 'avi', 'wmv', 'mp3', 'm4a',
            'jpg', 'jpeg', 'mpga', 'webp', 'wma',
        ],
        'max_upload_time' => 5,
        'cleanup' => true,
    ],

];
