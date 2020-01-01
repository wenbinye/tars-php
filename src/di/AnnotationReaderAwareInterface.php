<?php

declare(strict_types=1);

namespace wenbinye\tars\di;

use Doctrine\Common\Annotations\Reader;

interface AnnotationReaderAwareInterface
{
    public function setAnnotationReader(Reader $annotationReader): void;
}
