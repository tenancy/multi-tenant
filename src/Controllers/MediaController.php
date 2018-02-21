<?php

namespace Hyn\Tenancy\Controllers;

use Hyn\Tenancy\Website\Directory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class MediaController
 *
 * @package Hyn\Tenancy\Controllers
 * @use Route::get('/media/{path}', Hyn\Tenancy\Controllers\MediaController::class)
 *          ->where('path', '.+')
 *          ->name('tenant.media');
 */
class MediaController
{
    /**
     * @var Directory
     */
    private $directory;

    public function __construct(Directory $directory)
    {
        $this->directory = $directory;
    }

    public function __invoke(string $path)
    {
        $path = "media/$path";

        if ($this->directory->exists($path)) {
            return new BinaryFileResponse(
                $this->directory->get($path)
            );
        }

        return abort(404);
    }
}
