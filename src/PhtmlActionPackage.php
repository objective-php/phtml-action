<?php
/**
 * Created by PhpStorm.
 * User: gde
 * Date: 26/03/2018
 * Time: 16:09
 */

namespace ObjectivePHP\PhtmlAction;


use ObjectivePHP\Application\Package\AbstractPackage;
use ObjectivePHP\Application\Package\PackageInterface;
use ObjectivePHP\Config\Config;
use ObjectivePHP\Config\ConfigAccessorsTrait;
use ObjectivePHP\Config\ConfigInterface;
use ObjectivePHP\Config\ConfigProviderInterface;
use ObjectivePHP\PhtmlAction\Config\PhtmlDefaultLayout;
use ObjectivePHP\PhtmlAction\Config\PhtmlLayoutPath;

/**
 * Class PhtmlActionPackage
 * @package ObjectivePHP\PhtmlAction
 */
class PhtmlActionPackage extends AbstractPackage
{

    use ConfigAccessorsTrait;

    /**
     * @return ConfigInterface
     */
    public function getDirectives(): array
    {
        return [
            new PhtmlLayoutPath('app/layouts'),
            new PhtmlDefaultLayout('layout')
        ];
    }

}
