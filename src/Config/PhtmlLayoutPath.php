<?php
/**
 * Created by PhpStorm.
 * User: gde
 * Date: 26/03/2018
 * Time: 16:17
 */

namespace ObjectivePHP\Middleware\Action\PhtmlAction\Config;


use ObjectivePHP\Config\Directive\AbstractScalarDirective;
use ObjectivePHP\Config\Directive\MultiValueDirectiveInterface;

class PhtmlLayoutPath extends AbstractScalarDirective implements MultiValueDirectiveInterface
{

    const KEY = 'phtml.layout.path';
    protected $key = self::KEY;
}