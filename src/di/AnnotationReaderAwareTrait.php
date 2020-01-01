<?php

declare(strict_types=1);

namespace wenbinye\tars\di;

use Doctrine\Common\Annotations\Reader;

trait AnnotationReaderAwareTrait
{
    /**
     * @var Reader
     */
    private $annotationReader;

    public function setAnnotationReader(Reader $annotationReader): void
    {
        $this->annotationReader = $annotationReader;
    }
}
