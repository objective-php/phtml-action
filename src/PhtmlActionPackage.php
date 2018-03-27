<?php
/**
 * Created by PhpStorm.
 * User: gde
 * Date: 26/03/2018
 * Time: 16:09
 */

namespace ObjectivePHP\Middleware\Action\PhtmlAction;


use ObjectivePHP\Application\Package\PackageInterface;
use ObjectivePHP\Config\Config;
use ObjectivePHP\Config\ConfigInterface;
use ObjectivePHP\Config\ConfigProviderInterface;
use ObjectivePHP\Middleware\Action\PhtmlAction\Config\PhtmlDefaultLayout;
use ObjectivePHP\Middleware\Action\PhtmlAction\Config\PhtmlLayoutPath;

/**
 * Class PhtmlActionPackage
 * @package ObjectivePHP\Middleware\Action\PhtmlAction
 */
class PhtmlActionPackage implements PackageInterface, ConfigProviderInterface
{

    /**
     * @return ConfigInterface
     */
    public function getConfig(): ConfigInterface
    {
        $config = new Config();
        $config->registerDirective(new PhtmlLayoutPath('app/layouts'));
        $config->registerDirective(new PhtmlDefaultLayout('layout'));

        return $config;
    }

}