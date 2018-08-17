<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://laravel-tenancy.com
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Controllers;

use Hyn\Tenancy\Website\Directory;
use Illuminate\Support\Facades\Storage;

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
            $mimetype = Storage::disk('tenant')->mimeType($path);

            if (config('tenancy.mimes')) {
                // Unfortunately, some shared hoster have limited mime type support, so we need to improve that by our self.
                $fileextension = pathinfo($path)['extension'];

                if (isset(config('tenancy.mimes')[$fileextension])) {
                    $mimetype = config('tenancy.mimes')[$fileextension];
                }
            }

            return response($this->directory->get($path))
                ->header('Content-Type', $mimetype);
        }

        return abort(404);
    }
}
