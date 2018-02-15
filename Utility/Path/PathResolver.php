<?php

namespace Liip\ImagineBundle\Utility\Path;

class PathResolver implements PathResolverInterface
{
    /**
     * @var string
     */
    protected $webRoot;
    
    /**
     * @var string
     */
    protected $cachePrefix;
    
    /**
     * @var string
     */
    protected $cacheRoot;
    
    public function __construct(
        $webRootDir,
        $cachePrefix = 'media/cache'
    ) {
        $this->webRoot = rtrim(str_replace('//', '/', $webRootDir), '/');
        $this->cachePrefix = ltrim(str_replace('//', '/', $cachePrefix), '/');
        $this->cacheRoot = $this->webRoot.'/'.$this->cachePrefix;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getFilePath($path, $filter)
    {
        return $this->webRoot.'/'.$this->getFileUrl($path, $filter);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getFileUrl($path, $filter)
    {
        // crude way of sanitizing URL scheme ("protocol") part
        $path = str_replace('://', '---', $path);
    
        return $this->cachePrefix.'/'.$filter.'/'.ltrim($path, '/');
    }
    
    /**
     * {@inheritdoc}
     */
    public function getCacheRoot()
    {
        return $this->cacheRoot;
    }
}
