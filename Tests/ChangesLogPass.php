<?php

namespace JanMarek\AutowiringBundle\Tests;

use Symfony\Component\DependencyInjection\ContainerBuilder;


class ChangesLogPass implements \Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{

	/** @var array[] */
	public $changes = [];

	/**
	 * @param ContainerBuilder $container
	 */
	public function process(ContainerBuilder $container)
	{
		foreach ($container->getDefinitions() as $id => $definition) {
			if ($definition instanceof \Symfony\Component\DependencyInjection\DefinitionDecorator) {
				$this->changes[$id] = $definition->getChanges();
			}
		}
	}

}
