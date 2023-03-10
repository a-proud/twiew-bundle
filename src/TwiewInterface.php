<?php

namespace AProud\TwiewBundle;

use Symfony\Component\HttpFoundation\Response;

interface TwiewInterface
{

    /**
	 *  Wrapper for Twig\Environment render function, return rendered html page based on $tplSchema configuration.
	 *  @var array $vars Optional, tplSchema overrides
	 *  @return Symfony\Component\HttpFoundation\Response
	 */
    public function render(array $vars): Response;

	 /**
	 *  Get value from tplSchema, or set value to tplSchema
	 *  @var string $key   tplSchema key to get or owerride (use array dot notation to work with nested arrays)
	 *  @var mixed $value Optional, override the value in tplSchema key
	 *  @return value for $tplSchema key
	 */
	public function tplSchema(string $key, $value);

}