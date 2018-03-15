<?php
/**
 * Created by PhpStorm.
 * User: gde
 * Date: 13/03/2018
 * Time: 22:43
 */

namespace ObjectivePHP\View\Phtml\PhtmlAction;


use ObjectivePHP\Html\Tag\Tag;
use ObjectivePHP\Middleware\HttpAction\HttpAction;
use ObjectivePHP\View\Phtml\PhtmlAction\Exception\PhtmlLayoutNotFoundException;
use ObjectivePHP\View\Phtml\PhtmlAction\Exception\PhtmlTemplateNotFoundException;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;

abstract class PhtmlAction extends HttpAction
{

    /**
     * @var string
     */
    protected $defaultLayout;

    /**
     * @var callable
     */
    protected $errorHandler;

    /**
     * @var array
     */
    protected $vars = [];

    /**
     * @var string View
     */
    protected $viewOutput;

    /**
     * @param array $vars
     * @param null $layout
     * @param null $view
     * @return ResponseInterface
     * @throws PhtmlTemplateNotFoundException
     */
    public function render($vars = [], $layout = null, $view = null): ResponseInterface
    {
        $view = $view ?? $this->resolveViewScriptPath();
        $layout = $layout ?? $this->getDefaultLayout();


        $viewRenderer = function () use ($view, $vars) {

            if (!file_exists($view)) {
                throw new PhtmlTemplateNotFoundException(sprintf('View script "%s" does not exist', $view));
            }

            extract($vars);


            ob_start();
            // deactivate error handler during rendering
            $previousErrorHandler = set_error_handler($this->getErrorHandler());

            include $view;
            $output = ob_get_clean();

            // restore previous error handler
            set_error_handler($previousErrorHandler);

            return $output;
        };

        $layoutRenderer = function ($layout, $vars, $viewOutput) {


            if (!file_exists($layout)) {
                throw new PhtmlLayoutNotFoundException(sprintf('Layout script "%s" does not exist', $layout));
            }

            extract($vars);

            ob_start();
            // deactivate error handler during rendering
            $previousErrorHandler = set_error_handler($this->getErrorHandler());

            include $layout;

            $output = ob_get_clean();

            // restore previous error handler
            set_error_handler($previousErrorHandler);

            $response = new Response();
            $response->getBody()->write($output);

            return $response;
        };

        $output = $viewRenderer();

        if ($layout) {
            $output = $layoutRenderer($vars, $layout, $output);
        }

        $response = new Response();
        $response->getBody()->write($output);

        return $response;

    }

    protected function resolveViewScriptPath()
    {
        // set default view name

        $reflected = new \ReflectionObject($this);

        $viewTemplate = substr($reflected->getFileName(), 0, -4) . ".phtml";

        if (!file_exists($viewTemplate)) {

            throw new PhtmlTemplateNotFoundException('Template file "' . $viewTemplate . '“ does not exist.');
        }

        return $viewTemplate;
    }

    /**
     * @param mixed $defaultLayout
     */
    public function setDefaultLayout($defaultLayout)
    {
        $this->defaultLayout = $defaultLayout;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefaultLayout()
    {
        return $this->defaultLayout;
    }

    public function errorHandler($level, $message, $file, $line)
    {

        if (ini_get('display_errors') == 0) {
            return;
        }

        $levelLabel = '';
        $color = '#000';
        switch ($level) {
            case 1:
            case 16:
            case 64:
            case 256:
            case 4096:
                $levelLabel = 'error';
                $color = '#F00';
                break;

            case 2:
            case 32:
            case 128:
            case 512:
                $color = '#FA0';
                $levelLabel = 'warning';
                break;

            case 4:
                $color = '#F00';
                $levelLabel = 'parse';
                break;

            case 8:
            case 1024:
                $color = '#FAF';
                $levelLabel = 'notice';
                break;

            case 2048:
                $levelLabel = 'strict';
                break;

            case 8192:
            case 16384:
                $levelLabel = 'deprecated';
                break;

        }

        $file = ltrim(str_replace(getcwd(), '', $file), '/\\');

        Tag::span('[' . $levelLabel . '] ' . $file . ':' . $line . ' => ' . $message . '<br>')['style'] = 'color: ' . $color . ';font-weight:bold';
    }

    /**
     * @return callable
     */
    protected function getErrorHandler()
    {
        if (!is_null($this->errorHandler)) {
            return [$this, 'errorHandler'];
        }

    }

    /**
     * @return array
     */
    public function getVars(): array
    {
        return $this->vars;
    }

    /**
     * @param array $vars
     */
    public function setVars(array $vars)
    {
        $this->vars = $vars;
    }

    /**
     * @param      $reference
     * @param null $default
     *
     * @return mixed
     */
    public function get($reference, $default = null)
    {
        return $this->vars[$reference] ?? $default;
    }

    /**
     * @param $reference
     * @param $value
     */
    public function set($reference, $value)
    {
        $this->vars[$reference] = $value;
    }

    /**
     * @param $reference
     */
    public function unset($reference)
    {
        unset($this->vars[$reference]);
    }


}