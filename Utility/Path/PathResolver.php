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

use Liip\ImagineBundle\Imagine\Cache\Helper\PathHelper;

final class PathResolver implements PathResolverInterface
{
    /**
     * @var string
     */
    private $webRoot;

    /**
     * @var string
     */
    private $cachePrefix;

    /**
     * @var string
     */
    private $cacheRoot;

    public function __construct(
        $webRootDir,
        $cachePrefix = 'media/cache'
    ) {
        $this->webRoot = rtrim(str_replace('//', '/', $webRootDir), '/');
        $this->cachePrefix = ltrim(str_replace('//', '/', $cachePrefix), '/');
        $this->cacheRoot = $this->webRoot.'/'.$this->cachePrefix;
    }

    public function getFilePath(string $path, string $filter): string
    {
        return $this->webRoot.'/'.$this->getFullPath($path, $filter);
    }

    public function getFileUrl(string $path, string $filter): string
    {
        return PathHelper::filePathToUrlPath($this->getFullPath($path, $filter));
    }

    public function getCacheRoot(): string
    {
        return $this->cacheRoot;
    }

    private function getFullPath($path, $filter): string
    {
        // crude way of sanitizing URL scheme ("protocol") part
        $path = str_replace('://', '---', $path);

        return $this->cachePrefix.'/'.$filter.'/'.ltrim($path, '/');
    }
}
