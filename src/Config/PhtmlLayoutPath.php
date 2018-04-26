<?php

namespace ObjectivePHP\Middleware\Action\PhtmlAction\Config;

use ObjectivePHP\Config\Directive\AbstractScalarDirective;
use ObjectivePHP\Config\Directive\MultiValueDirectiveInterface;
use ObjectivePHP\Config\Directive\MultiValueDirectiveTrait;

/**
 * Class PhtmlLayoutPath
 *
 * @package ObjectivePHP\Middleware\Action\PhtmlAction\Config
 */
class PhtmlLayoutPath extends AbstractScalarDirective implements MultiValueDirectiveInterface
{
    use MultiValueDirectiveTrait;

    const KEY = 'phtml.layout.path';

    protected $key = self::KEY;
}
