<?php

namespace Pina\Container;

use Psr\Container\NotFoundExceptionInterface;
use InvalidArgumentException;

class NotFoundException extends InvalidArgumentException implements NotFoundExceptionInterface
{
    
}
