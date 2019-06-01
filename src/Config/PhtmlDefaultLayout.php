<?php

namespace ObjectivePHP\PhtmlAction\Config;

use ObjectivePHP\Config\Directive\AbstractScalarDirective;

/**
 * Class PhtmlLayoutDefault
 *
 * @package ObjectivePHP\PhtmlAction\Config
 */
class PhtmlDefaultLayout extends AbstractScalarDirective
{
    const KEY = 'phtml.layout.default';

    /**
     * @var string
     */
    protected $key = self::KEY;
}
