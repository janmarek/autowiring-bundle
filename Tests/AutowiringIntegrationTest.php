<?php

namespace JanMarek\AutowiringBundle\Tests;

use JanMarek\AutowiringBundle\AutowiringPass;
use JanMarek\AutowiringBundle\ExpandServiceValuesPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class AutowiringIntegrationTest extends BaseTestCase
{

	/** @var ContainerBuilder */
	private $container;

	/** @var YamlFileLoader */
	private $loader;

	protected function setUp()
	{
		$this->container = new ContainerBuilder();
		$this->loader = new YamlFileLoader($this->container, new FileLocator());
		$this->container->addCompilerPass(new ExpandServiceValuesPass());
		$this->container->addCompilerPass(new AutowiringPass());
	}

	public function testAutowiring()
	{
		$this->loader->load(__DIR__ . '/Fixtures/config.yml');
		$this->container->compile();

		$ns = __NAMESPACE__ . '\Fixtures';

		$this->assertInstanceOf($ns . '\ExampleClass', $this->container->get('example'));
		$this->assertInstanceOf($ns . '\ClassWithConstructor', $this->container->get('with_constructor'));
		$this->assertInstanceOf($ns . '\ClassWithExtendingClassInConstructor', $this->container->get('configured'));
		$defaultArgs = $this->container->get('default_args');
		$this->assertInstanceOf($ns . '\ClassWithDefaultArguments', $defaultArgs);
		$this->assertEquals(array(NULL, 123), $defaultArgs->getArgs());
	}

	/**
	 * @expectedException JanMarek\AutowiringBundle\AutowiringException
	 * @expectedExceptionMessage Too many services implemented by class JanMarek\AutowiringBundle\Tests\Fixtures\ExampleClass (example, extending) required by service conflict.
	 */
	public function testTooManyServices()
	{
		$this->loader->load(__DIR__ . '/Fixtures/conflict.yml');
		$this->container->compile();
	}

	/**
	 * @expectedException JanMarek\AutowiringBundle\AutowiringException
	 * @expectedExceptionMessage There is no service implementing 'JanMarek\AutowiringBundle\Tests\Fixtures\Example3' in container required by service 'no_implementation'.
	 */
	public function testNoAvailableService()
	{
		$this->loader->load(__DIR__ . '/Fixtures/noImplementation.yml');
		$this->container->compile();
	}

	/**
	 * @expectedException JanMarek\AutowiringBundle\AutowiringException
	 * @expectedExceptionMessage Parameter $param in JanMarek\AutowiringBundle\Tests\Fixtures\ClassWithUnknownParameterType::__construct() (service unknown_param) cannot be resolved.
	 */
	public function testUnknownParamType()
	{
		$this->loader->load(__DIR__ . '/Fixtures/unknownParamType.yml');
		$this->container->compile();
	}

	public function testFindServiceByNamingConvention()
	{
		$this->loader->load(__DIR__ . '/Fixtures/findServiceByName.yml');
		$this->container->compile();

		$this->assertInstanceOf(
            'JanMarek\AutowiringBundle\Tests\Fixtures\ClassWithExtendingClassInConstructor',
            $this->container->get('conflict')
        );
	}

	public function testAutowireFewArguments()
	{
		$this->loader->load(__DIR__ . '/Fixtures/autowireSomething.yml');
		$this->container->compile();

		$service = $this->container->get('withScalarArgs');
		$this->assertEquals(array('bar', 'bar', 2, 'asdbar'), $service->getArgs());

		$service2 = $this->container->get('withMiscArgs');
		$args2 = $service2->getArgs();
		$this->assertInstanceOf('JanMarek\AutowiringBundle\Tests\Fixtures\ExampleClass', $args2[0]);
		$this->assertEquals(1, $args2[1]);
		$this->assertEquals(2, $args2[2]);
		$this->assertInstanceOf('JanMarek\AutowiringBundle\Tests\Fixtures\ClassWithScalarArgs', $args2[3]);

		$this->assertInstanceOf(
            'JanMarek\AutowiringBundle\Tests\Fixtures\ClassWithConstructor',
            $this->container->get('withObjectArgs')
        );
	}

	public function testAutowireDefaultArgsSetToNull()
	{
		$this->loader->load(__DIR__ . '/Fixtures/autowireSomething.yml');
		$this->container->compile();

		$service = $this->container->get('defaultArgsSetToNull');
		$this->assertEquals(array(NULL, NULL, NULL), $service->getArgs());
	}

	public function testAutowireFewArgumentsInClassWithDefaultArguments()
	{
		$this->loader->load(__DIR__ . '/Fixtures/autowireSomething.yml');
		$this->container->compile();

		$service = $this->container->get('defaultArgs');
		$this->assertEquals(array(NULL, 'foo', 'bar'), $service->getArgs());
	}

	public function testAutowireDomDocument()
	{
		$this->loader->load(__DIR__ . '/Fixtures/autowireDomDocument.yml');
		$this->container->compile();

		$service = $this->container->get('domDocument');
		$this->assertInstanceOf('DomDocument', $service);
	}

	/**
	 * @expectedException JanMarek\AutowiringBundle\AutowiringException
	 * @expectedExceptionMessage Parameter $nonExisting in JanMarek\AutowiringBundle\Tests\Fixtures\ClassWithDefaultScalarArguments::__construct() (service example1) does not exist.
	 */
	public function testAutowireNonExisting1()
	{
		$this->loader->load(__DIR__ . '/Fixtures/autowireNonExistingParam.yml');
		$this->container->compile();

		$service = $this->container->get('example1');
	}

	/**
	 * @expectedException JanMarek\AutowiringBundle\AutowiringException
	 * @expectedExceptionMessage Parameter at position 3 (indexed by 0) in JanMarek\AutowiringBundle\Tests\Fixtures\ClassWithDefaultScalarArguments::__construct() (service example2) does not exist.
	 */
	public function testAutowireNonExisting2()
	{
		$this->loader->load(__DIR__ . '/Fixtures/autowireNonExistingParam2.yml');
		$this->container->compile();

		$service = $this->container->get('example2');
	}

	public function testAutowireSetters()
	{
		$this->loader->load(__DIR__ . '/Fixtures/autowireSetters.yml');
		$this->container->compile();

		$service = $this->container->get('withSetters');
		$values = $service->getValues();

		$this->assertEquals('fooParam', $values[0]);
		$this->assertEquals('barParam', $values[1]);
		$this->assertInstanceOf('JanMarek\AutowiringBundle\Tests\Fixtures\ExampleClass', $values[2]);
		$this->assertEquals('xxx', $values[3]);
		$this->assertInstanceOf('JanMarek\AutowiringBundle\Tests\Fixtures\ExampleClass', $values[4]);
	}

	public function testAutowireFactory()
	{
		$this->loader->load(__DIR__ . '/Fixtures/autowireFactory.yml');
		$this->container->compile();

		$service1 = $this->container->get('withSetters');
		$values = $service1->getValues();
		$this->assertEquals('fooParam', $values[0]);
		$this->assertEquals('barParam', $values[1]);
		$this->assertInstanceOf('JanMarek\AutowiringBundle\Tests\Fixtures\ExampleClass', $values[2]);

		$service2 = $this->container->get('example3');
		$this->assertInstanceOf('JanMarek\AutowiringBundle\Tests\Fixtures\Example3', $service2);
	}

	/**
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage Please add the class to service "withsetters" even if it is constructed by a factory since we might need to add method calls based on compile-time checks.
	 */
	public function testAutowireFactoryWithoutDefinedClass()
	{
		$this->loader->load(__DIR__ . '/Fixtures/autowireFactoryWithoutDefinedClass.yml');
		$this->container->compile();

		$service1 = $this->container->get('withSetters');
		$this->assertInstanceOf('JanMarek\AutowiringBundle\Tests\ClassWithSetters', $service1);

		$service2 = $this->container->get('example3');
		$this->assertInstanceOf('JanMarek\AutowiringBundle\Tests\Example3', $service2);
	}

	/**
	 * @expectedException JanMarek\AutowiringBundle\AutowiringException
	 * @expectedExceptionMessage Bad class in constructor parameter of service classwithunknownclassinconstructor
	 */
	public function testUnknownClassInConstructor()
	{
		$this->loader->load(__DIR__ . '/Fixtures/unknownClassInConstructor.yml');
		$this->container->compile();
	}

	public function testClassWithInterface()
	{
		$this->loader->load(__DIR__ . '/Fixtures/classWithInterface.yml');
		$this->container->compile();

		$object = $this->container->get('jan_marek.autowiring_bundle.tests.fixtures.class_that_needs_an_interface');
		$this->assertInstanceOf('JanMarek\AutowiringBundle\Tests\Fixtures\ClassThatNeedsAnInterface', $object);
		$this->assertInstanceOf('JanMarek\AutowiringBundle\Tests\Fixtures\ClassWithInterface', $object->getInterface());
	}

	public function testAliases()
	{
		$this->loader->load(__DIR__ . '/Fixtures/config.yml');
		$this->container->compile();

		$defaultArgs = $this->container->get('alias');
		$this->assertInstanceOf('JanMarek\AutowiringBundle\Tests\Fixtures\ClassWithDefaultArguments', $defaultArgs);
	}

	public function testConstructorArgumentsFromParentService()
	{
		$this->loader->load(__DIR__ . '/Fixtures/argumentsFromParentService.yml');
		$this->container->compile();

		$this->assertInstanceOf('JanMarek\AutowiringBundle\Tests\Fixtures\FooClass', $this->container->get('foo'));
	}

}
