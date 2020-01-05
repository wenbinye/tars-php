<?php

declare(strict_types=1);

namespace wenbinye\tars\di;

interface DefinitionConfiguration extends ContainerBuilderAwareInterface
{
    public function getDefinitions(): array;
}
