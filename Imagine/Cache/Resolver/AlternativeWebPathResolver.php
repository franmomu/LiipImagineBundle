<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

class AlternativeWebPathResolver extends AbstractWebPathResolver
{
    /**
     * {@inheritdoc}
     */
    public function resolve($path, $filter)
    {
        return sprintf('/%s', $this->getPathResolver()->getFileUrl($path, $filter));
    }
}
