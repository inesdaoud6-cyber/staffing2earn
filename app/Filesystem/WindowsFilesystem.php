<?php

namespace App\Filesystem;

use Illuminate\Filesystem\Filesystem;

/**
 * Windows often denies rename() onto an existing compiled Blade file that is still
 * mapped by OPcache or briefly held by another worker. Laravel's default replace()
 * uses tempnam + rename in the same directory; unlinking the target first avoids that.
 */
class WindowsFilesystem extends Filesystem
{
    public function replace($path, $content, $mode = null)
    {
        if ($this->exists($path)) {
            $resolved = realpath($path) ?: $path;

            @chmod($resolved, 0666);
            @unlink($resolved);
        }

        parent::replace($path, $content, $mode);
    }
}
