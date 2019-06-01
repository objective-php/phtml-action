<?php

namespace ObjectivePHP\PhtmlAction\Config;

use ObjectivePHP\Config\Directive\AbstractScalarDirective;
use ObjectivePHP\Config\Directive\MultiValueDirectiveInterface;
use ObjectivePHP\Config\Directive\MultiValueDirectiveTrait;

/**
 * Class PhtmlLayoutPath
 *
 * @package ObjectivePHP\PhtmlAction\Config
 */
class PhtmlLayoutPath extends AbstractScalarDirective implements MultiValueDirectiveInterface
{
    use MultiValueDirectiveTrait;

    const KEY = 'phtml.layout.path';

    protected $key = self::KEY;
}
