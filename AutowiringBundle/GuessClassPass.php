<?php

namespace JanMarek\AutowiringBundle;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * If class in definition is not set, set class same as service name
 *
 * @author Jan Marek
 * @license BSD
 */
class GuessClassPass implements \Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{

	/**
	 * @param ContainerBuilder $container
	 */
	public function process(ContainerBuilder $container)
	{
		foreach ($container->getDefinitions() as $id => $definition) {
            $className = strtr(Container::camelize($id), '_', '\\');

			/** @var \Symfony\Component\DependencyInjection\Definition $definition */
			if ($definition->getClass() === NULL && !$definition->isAbstract() && (class_exists($className) || interface_exists($className))) {
				$definition->setClass($className);
			}
		}
	}

}
