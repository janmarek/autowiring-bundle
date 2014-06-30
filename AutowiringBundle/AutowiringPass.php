<?php

namespace JanMarek\AutowiringBundle;

use ReflectionMethod;
use ReflectionClass;
use ReflectionParameter;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Martin Hasoň, Jan Marek
 * @license BSD
 */
class AutowiringPass implements \Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{

	/**
	 * @param ContainerBuilder $container
	 */
	public function process(ContainerBuilder $container)
	{
		$classes = $this->getClasses($container);

		foreach ($container->getDefinitions() as $id => $definition) {
			$class = $definition->getClass();

			if ($class) {
				$this->autowireConstructor($class, $definition, $classes, $id, $container);
			}

			if ($definition->getFactoryMethod() !== NULL) {
				$this->autowireFactory($definition, $classes, $id, $container);
			}

			if ($class) {
				$this->autowireSetters($class, $definition, $classes, $id, $container);
			}
		}
	}

	/**
	 * @param Definition $definition
	 * @param string[] $classes
	 * @param string $id
	 * @param ContainerBuilder $container
	 */
	private function autowireFactory(Definition $definition, array $classes, $id, ContainerBuilder $container)
	{
		if ($definition->getFactoryClass()) {
			$factoryClass = $definition->getFactoryClass();
		} else {
			$factoryName = $definition->getFactoryService();
			$factoryClass = $container->getDefinition($factoryName)->getClass();
		}

		if ($factoryClass) {
			$method = new ReflectionMethod($factoryClass, $definition->getFactoryMethod());
			$autowiredArgs = $this->autowireMethod($method, $definition->getArguments(), $classes, $id, $container);
			$definition->setArguments($autowiredArgs);
		}
	}

	/**
	 * @param string $class
	 * @param Definition $definition
	 * @param string[] $classes
	 * @param string $id
	 * @param ContainerBuilder $container
	 */
	private function autowireConstructor($class, Definition $definition, array $classes, $id, ContainerBuilder $container)
	{
		$reflection = new ReflectionClass($class);
		$constructor = $reflection->getConstructor();

		// service not created by factory with public constructor with not fully configured arguments
		if ($constructor !== NULL && $constructor->isPublic() && $definition->getFactoryMethod() === NULL) {
			$autowiredArgs = $this->autowireMethod($constructor, $definition->getArguments(), $classes, $id, $container);
			if ($definition instanceof DefinitionDecorator && $definition->getParent() !== NULL) {
				$parentDef = $container->getDefinition($definition->getParent());
				$parentDefArgsCount = count($parentDef->getArguments());
				$argsToReplace = array();
				foreach ($autowiredArgs as $i => $arg) {
					if ($i < $parentDefArgsCount) {
						$argsToReplace[$i] = $arg;
						unset($autowiredArgs[$i]);
					}
				}
				$definition->setArguments($autowiredArgs);
				foreach ($argsToReplace as $i => $arg) {
					$definition->replaceArgument($i, $arg);
				}
			} else {
				$definition->setArguments($autowiredArgs);
			}
		}
	}

	/**
	 * @param string $class
	 * @param Definition $definition
	 * @param string[] $classes
	 * @param string $id
	 * @param ContainerBuilder $container
	 */
	private function autowireSetters($class, Definition $definition, array $classes, $id, ContainerBuilder $container)
	{
		$newCalls = array();
		foreach ($definition->getMethodCalls() as $call) {
			$method = new ReflectionMethod($class, $call[0]);
			$autowiredArgs = $this->autowireMethod($method, $call[1], $classes, $id, $container);

			$newCalls[] = array($call[0], $autowiredArgs);
		}
		$definition->setMethodCalls($newCalls);
	}

	/**
	 * @param ReflectionMethod $method
	 * @param ReflectionParameter[] $beforeAutowireArgs
	 * @param string[] $classes
	 * @param string $serviceName
	 * @param ContainerBuilder $container
	 * @return ReflectionParameter[]
	 */
	private function autowireMethod(ReflectionMethod $method, array $beforeAutowireArgs, array $classes, $serviceName, ContainerBuilder $container)
	{
		$autowiredArgs = array();

		$parameters = $method->getParameters();

		$this->checkAutowiredParamList($parameters, $beforeAutowireArgs, $serviceName, $container, $method);

		foreach ($parameters as $i => $parameter) {
			if (array_key_exists($i, $beforeAutowireArgs)) {
				$autowiredArgs[] = $beforeAutowireArgs[$i];
			} elseif (array_key_exists($parameter->getName(), $beforeAutowireArgs)) {
				$autowiredArgs[] = $beforeAutowireArgs[$parameter->getName()];
			} elseif ($parameter->isDefaultValueAvailable()){
				$autowiredArgs[] = $parameter->getDefaultValue();
			} else {
				$autowiredArgs[] = $this->getParameterValue($parameter, $classes, $serviceName, $container, $method);
			}
		}

		return $autowiredArgs;
	}

	/**
	 * @param ReflectionMethod[] $parameters
	 * @param mixed[] $beforeAutowireArgs
	 * @param string $serviceName
	 * @param ContainerBuilder $container
	 * @param ReflectionMethod $method
	 */
	private function checkAutowiredParamList(array $parameters, array $beforeAutowireArgs, $serviceName, ContainerBuilder $container, ReflectionMethod $method)
	{
		$names = array_map(function ($param) {
			return $param->getName();
		}, $parameters);

		foreach ($beforeAutowireArgs as $key => $value) {
			if (empty($parameters[$key]) && !in_array($key, $names, TRUE)) {
				$class = $container->getParameterBag()->resolveValue($container->getDefinition($serviceName)->getClass());
				$paramText = is_numeric($key) ? 'at position ' . $key . ' (indexed by 0)' : '$' . $key;
				throw new AutowiringException(
					'Parameter ' . $paramText . ' in ' . $class . '::' . $method->getName() . '() ' .
					'(service ' . $serviceName . ') does not exist.'
				);
			}
		}
	}

	/**
	 * Create map of classes or interfaces and services implemented by each class
	 *
	 * @param ContainerBuilder $container
	 * @return string[]
	 */
	private function getClasses(ContainerBuilder $container)
	{
		$classes = array();
		foreach ($container->getDefinitions() as $id => $definition) {
			$class = $definition->getClass();

			if (!$class) {
				continue;
			}

			$reflection = new ReflectionClass($class);
			if ($reflection) {
				$classes[$reflection->getName()][] = $id;
				foreach ($reflection->getInterfaceNames() as $interface) {
					$classes[$interface][] = $id;
				}

				$parent = $reflection;
				while (($parent = $parent->getParentClass())) {
					$classes[$parent->getName()][] = $id;
				}
			}
		}

		return $classes;
	}

	/**
	 * Guess autowired parameter or throw exception
	 *
	 * @param ReflectionParameter $parameter Reflection
	 * @param string[] $classes class map
	 * @param string $serviceName
	 * @param ContainerBuilder $container
	 * @param ReflectionMethod $method
	 * @return Reference argument value
	 */
	private function getParameterValue(ReflectionParameter $parameter, array $classes, $serviceName, ContainerBuilder $container, ReflectionMethod $method)
	{
		$classReflection = $this->getParameterClassReflection($parameter, $serviceName);

		// autowiring of scalar paramers
		if ($classReflection === NULL) {
			return $this->getAutowiredScalarParameterValue($parameter, $serviceName, $container, $method);
		}

		// supermagic (VasekWiring)
		// return service reference with name equal to parameter class if exist
		$service = strtr(Container::underscore($classReflection->getName()), '\\', '.');
		if ($container->hasDefinition($service) || $container->hasAlias($service) || $container->has($service)) {
			return new Reference($service);
		}

		return $this->findByClass($classReflection, $classes, $serviceName);
	}

	/**
	 * @param ReflectionParameter $parameter
	 * @param string $serviceName
	 * @return ReflectionClass|NULL
	 */
	private function getParameterClassReflection(ReflectionParameter $parameter, $serviceName)
	{
		try {
			return $parameter->getClass();
		} catch (\ReflectionException $e) {
			throw new AutowiringException('Bad class in constructor parameter of service ' . $serviceName, NULL, $e);
		}
	}

	/**
	 * @param ReflectionParameter $parameter
	 * @param string $serviceName
	 * @param ContainerBuilder $container
	 * @param ReflectionMethod $method
	 * @return mixed ReflectionParameter value
	 */
	private function getAutowiredScalarParameterValue(ReflectionParameter $parameter, $serviceName, ContainerBuilder $container, ReflectionMethod $method)
	{
		$paramName = $parameter->getName();
		if ($container->hasParameter($paramName)) {
			return $container->getParameter($paramName);
		}

		// parameter without typehinting, throw exception
		$class = $container->getParameterBag()->resolveValue($container->getDefinition($serviceName)->getClass());
		throw new AutowiringException(
			'Parameter $' . $parameter->getName() . ' in ' . $class . '::' . $method->getName() . '() ' .
			'(service ' . $serviceName . ') cannot be resolved.'
		);
	}

	/**
	 * @param ReflectionClass $reflection
	 * @param string[] $classes
	 * @param string $serviceName
	 * @return Reference
	 */
	private function findByClass(ReflectionClass $reflection, array $classes, $serviceName)
	{
		$class = $reflection->getName();

		// if one suitable service found, return it
		if (isset($classes[$class])) {
			if (count($classes[$class]) === 1) {
				return new Reference($classes[$class][0]);
			} else {
				throw new AutowiringException('Too many services implemented by class ' . $class . ' (' . implode(', ', $classes[$class]) . ') required by service ' . $serviceName . '.');
			}
		} else {
			throw new AutowiringException("There is no service implementing '" . $class . "' in container required by service '" . $serviceName . "'.");
		}
	}

}
