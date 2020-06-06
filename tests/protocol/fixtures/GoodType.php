<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol\fixtures;

use kuiper\helper\Enum;

class GoodType extends Enum
{
    const COUNTRY = 0;
    const PROVINCE = 1;
    const CITY = 2;
    const DISTRICT = 3;
    const TOWN = 4;
}
