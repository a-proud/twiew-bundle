<?php

namespace AProud\TwiewBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Twig\Environment;

class AProudTwiewBundle extends Bundle
{

	public function getPath(): string
	{
		return \dirname(__DIR__);
	}
	
}