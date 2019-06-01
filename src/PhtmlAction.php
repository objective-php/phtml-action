<?php

namespace ObjectivePHP\PhtmlAction;

use ObjectivePHP\Application\ApplicationAwareInterface;
use ObjectivePHP\Application\ApplicationAccessorsTrait;
use ObjectivePHP\PhtmlAction\Config\PhtmlDefaultLayout;
use ObjectivePHP\PhtmlAction\Config\PhtmlLayoutPath;
use ObjectivePHP\PhtmlAction\Exception\PhtmlLayoutNotFoundException;
use ObjectivePHP\PhtmlAction\Exception\PhtmlTemplateNotFoundException;
use ObjectivePHP\HttpAction\HttpAction;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;

/**
 * Class PhtmlAction
 *
 * @package ObjectivePHP\PhtmlAction
 */
abstract class PhtmlAction extends HttpAction implements ApplicationAwareInterface
{
    use ApplicationAccessorsTrait;

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
        $layout = $layout ?? $this->getDefaultLayout();

        $vars = array_merge($this->getVars(), $vars);

        $this->setVars($vars);

        $this->setViewOutput($this->renderViewScript($view));

        if ($layout !== false) {
            $this->setViewOutput($this->renderLayoutScript($layout));
        }

        $response = new Response();
        $response->getBody()->write($this->getViewOutput());

        return $response;
    }

    /**
     * Actually renders a view script
     *
     * this method is private since it is not supposed to get triggered
     * by inherited classes directly, but using render() instead.
     *
     * @param $view
     * @return string
     * @throws PhtmlTemplateNotFoundException
     */
    private function renderViewScript($view)
    {
        $viewScriptPath = $this->resolveViewScriptPath($view);

        if (!$viewScriptPath) {
            throw new PhtmlTemplateNotFoundException(sprintf('No view script matches given template "%s"', $view));
        }

        return $this->import($viewScriptPath);
    }

    /**
     * Actually renders a layout script
     *
     * this method is private since it is not supposed to get triggered
     * by inherited classes directly, but using render() instead.
     *
     * @param $layout
     * @return string
     * @throws PhtmlLayoutNotFoundException
     */
    private function renderLayoutScript($layout)
    {
        $layoutScriptPath = $this->resolveLayoutScriptPath($layout);

        if (!$layoutScriptPath) {
            throw new PhtmlLayoutNotFoundException(sprintf('No layout script matches given layout "%s"', $layout));
        }

        return $this->import($layoutScriptPath);
    }

    protected function resolveViewScriptPath($template = null)
    {
        if (!$template) {
            // set default view name
            $reflected = new \ReflectionObject($this);

            $template = substr($reflected->getFileName(), 0, -4) . ".phtml";
        }

        if (!file_exists($template)) {
            throw new PhtmlTemplateNotFoundException('Template file "' . $template . '“ does not exist.');
        }

        return $template;
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
        if ($this->defaultLayout) {
            return $this->defaultLayout;
        } else {
            $config = $this->getApplication()->getConfig();
            return $config->get(PhtmlDefaultLayout::KEY);
        }
    }

    public function errorHandler($level, $message, $file, $line)
    {
        if (ini_get('display_errors') == 0 || (0 === error_reporting())) {
            return false;
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

        echo '<span style="color: ' . $color . ';font-weight:bold">[' . $levelLabel . '] ' . $file . ':' . $line . ' => ' . $message . '</span><br>';
    }

    /**
     * @return callable
     */
    protected function getErrorHandler()
    {
        if (is_null($this->errorHandler)) {
            return [$this, 'errorHandler'];
        } else {
            return $this->errorHandler;
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
    public function setVars(array &$vars)
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

    /**
     * @return string
     */
    public function getViewOutput(): string
    {
        return $this->viewOutput;
    }

    /**
     * @param string $viewOutput
     */
    public function setViewOutput(string $viewOutput)
    {
        $this->viewOutput = $viewOutput;
    }

    /**
     * @param $layout
     * @return string
     */
    protected function resolveLayoutScriptPath($layout)
    {
        $config = $this->getApplication()->getConfig();
        $layoutPaths = $config->get(PhtmlLayoutPath::KEY);

        foreach ($layoutPaths as $layoutPath) {
            $candidatePath = $layoutPath . '/' . $layout . '.phtml';
            if (file_exists($candidatePath)) {
                return $candidatePath;
            }
        }
    }

    protected function import($file)
    {
        ob_start();
        // deactivate error handler during rendering
        $previousErrorHandler = set_error_handler($this->getErrorHandler());

        if (!file_exists($file)) {
            trigger_error('View script "' . $file . '" not found.', E_USER_WARNING);
        } else {
            include "$file";
        }

        $output = ob_get_clean();

        // restore previous error handler
        set_error_handler($previousErrorHandler);

        return $output;
    }

    protected function param($key)
    {
        $config = $this->getApplication()->getConfig();

        return $config->get($key);
    }
}
