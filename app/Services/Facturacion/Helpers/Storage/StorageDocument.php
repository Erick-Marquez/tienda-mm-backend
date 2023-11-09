<?php
namespace App\Services\Facturacion\Helpers\Storage;

use Illuminate\Support\Facades\Storage;

class StorageDocument
{
    public static function uploadStorage($folder, $filename, $extension, $file_content)
    {
        Storage::disk('public')->put($folder.DIRECTORY_SEPARATOR.$filename.'.'.$extension, $file_content);
    }

    public static function downloadStorage($path)
    {
        return Storage::disk('public')->download($path);
    }
}