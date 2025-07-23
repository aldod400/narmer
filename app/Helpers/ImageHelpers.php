<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;

class ImageHelpers
{
    public static function addImage(UploadedFile $image, string $path)
    {
        $filename = uniqid() . '.' . $image->getClientOriginalExtension();

        $path = $image->storeAs($path, $filename, 'public');

        return $path;
    }
    
}
