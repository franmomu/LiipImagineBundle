<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Utility\Path;

interface PathResolverInterface
{
    public function getFilePath(string $path, string $filter): string;

    public function getFileUrl(string $path, string $filter): string;

    public function getCacheRoot(): string;
}
