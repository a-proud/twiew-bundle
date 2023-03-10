<?php

namespace AProud\TwiewBundle;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment as Twig;
use Minwork\Helper\Arr;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBag;
use Symfony\Component\Yaml\Parser as YamlParser;
use AProud\TwiewBundle\TwiewInterface;

class Twiew implements TwiewInterface
{
	/**
	 *  @var Twig\Environment
	 */
	protected $twig;
	
	/**
	 *  @var Symfony\Component\HttpFoundation\RequestStack
	 */
	protected $requestStack;
	
	/**
	 *  @var Symfony\Component\DependencyInjection\ParameterBag\ContainerBag
	 */
	protected $container;
	
	/**
	 *  tplSchema storage property
	 */
	protected $tplSchema;
	
	
	/**
	 *  @var Twig\Environment $twig         Twig environment object
	 *  @var RequestStack $requestStack 	HttpFoundation request stack object
	 *  @var ContainerBag $containerBag 	Symfony DI container
	 *  @return Twiew instance
	 */
	public function __construct(Twig $twig, RequestStack $requestStack, ContainerBag $containerBag)
	{
		$this->container = $containerBag;
		$this->twig = $twig;
		$this->requestStack = $requestStack;
		$this->tplSchema = [
			'default' => [
				'root_layouts' => [
					'fullpage' => '@twiew/layouts/__layout_fullpage.html.twig',
					'empty' => '@twiew/layouts/__layout_empty.html.twig',
				],
				'head' => [],
				'css' => [],
				'js_top' => [],
				'js_bottom' => [],
				'sections' => [
					'header' => [],
					'main' => [],
					'footer' => [],
				],
			],
		];
	}
	
	/**
     * {@inheritdoc}
     */
	public function render($vars = []): Response
	{
		if (!$this->twig) {
            throw new \LogicException('You cannot use the "Twiew->render()" method if the Twig Bundle is not available. '
										.'Try running "composer require symfony/twig-bundle".');
        }
		$vars['twiew'] = array_merge_recursive($this->tplSchema('default'), $this->tplSchema('current_route'), $vars);
		$layout = $this->tplSchema('default.root_layouts.fullpage');
		if (Arr::get($vars, 'root_layout')) {
			$layout = $this->tplSchema('default.root_layouts.'.Arr::get($vars, 'root_layout'));
		}
		return new Response($this->twig->render($layout, $vars));
	}
	
	/**
	 *  Parse template schema from .yaml file and load it to $this->tplSchema parameter.
	 *  
	 *  @var string $key  Key name in tplSchema. Use dot notation to work with the nested values. Example - 'default.css.main'
	 *  @var string $path Path to .yaml file
	 *  @return void
	 */
	public function tplSchemaFromYaml(string $key, string $path)
	{
		$rootPath = $this->container->get('kernel.project_dir');
		$parser = new YamlParser();
		$schema = $parser->parseFile($rootPath.'/'.ltrim($path, '/'));
		foreach ($schema as $route => $templateVars) {
			$this->tplSchema($route, $templateVars);
		}
	}
	
	/**
     * {@inheritdoc}
     */
	public function tplSchema(string $key = '', $value = null)
	{
		if ($key === 'current_route') {
			$key = $this->requestStack->getCurrentRequest()->attributes->get('_route');
		}
		if ($value !== null) { //set value
			if ($key == '' && is_array($value)) {
				$key = array_keys($value)[0];
				$value = $value[$key];
			}
			if ($key == 'default') {
				throw new \LogicException('Can\'t replace `default` tpl schema. Use dot notation to change default parameters. Example: default.css: [\'/path/to/mystyle.css\']');
			}
			$this->tplSchema = Arr::set($this->tplSchema, $key, $value);
		}
		
		if ($key == '') {
			return $this->tplSchema;
		}
		return Arr::has($this->tplSchema, $key) ? Arr::get($this->tplSchema, $key) : [];
	}
}