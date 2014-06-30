<?php

namespace JanMarek\AutowiringBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class ExpandServiceValuesPass implements \Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{

	/**
	 * @param ContainerBuilder $container
	 */
	public function process(ContainerBuilder $container)
	{
		$parameterBag = $container->getParameterBag();

		foreach ($container->getDefinitions() as $id => $definition) {
			$alias = $parameterBag->resolveValue($id);
			if ($alias !== $id) {
				$container->setDefinition($alias, $definition);
				$container->removeDefinition($id);
			}
			$definition->setClass($parameterBag->resolveValue($definition->getClass()));
			$definition->setFactoryMethod($parameterBag->resolveValue($definition->getFactoryMethod()));
		}
	}

}
