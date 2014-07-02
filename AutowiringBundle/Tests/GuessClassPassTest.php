<?php

namespace JanMarek\AutowiringBundle\Tests;

use JanMarek\AutowiringBundle\GuessClassPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class GuessClassPassTest extends BaseTestCase
{

	/** @var ContainerBuilder */
	private $builder;

	/** @var YamlFileLoader */
	private $loader;

	/** @var GuessClassPass */
	private $object;

	protected function setUp()
	{
		$this->builder = new ContainerBuilder();
		$this->loader = new YamlFileLoader($this->builder, new FileLocator());
		$this->object = new GuessClassPass();
	}

	public function testSimpleService()
	{
		$this->loader->load(__DIR__ . '/Fixtures/guessClass1.yml');
		$this->object->process($this->builder);

		$definition = $this->builder->getDefinition('jan_marek.autowiring_bundle.tests.fixtures.example_class');
		$this->assertEquals('JanMarek\AutowiringBundle\Tests\Fixtures\ExampleClass', $definition->getClass());
	}

	public function testServiceWithArguments()
	{
		$this->loader->load(__DIR__ . '/Fixtures/guessClass2.yml');
		$this->object->process($this->builder);

		$definition = $this->builder->getDefinition(
            'jan_marek.autowiring_bundle.tests.fixtures.class_with_unknown_parameter_type'
        );
		$this->assertEquals(array('blabla'), $definition->getArguments());
	}

	public function testPassDoesNotOverrideDefinedClass()
	{
		$this->loader->load(__DIR__ . '/Fixtures/guessClass2.yml');
		$this->object->process($this->builder);

		$definition = $this->builder->getDefinition('jan_marek.autowiring_bundle.tests.fixtures.class_with_constructor');
		$this->assertEquals('JanMarek\AutowiringBundle\Tests\Fixtures\ExampleClass', $definition->getClass());
	}

	public function testClassIsNotGuessedInAbstractDefinitions()
	{
		$this->loader->load(__DIR__ . '/Fixtures/guessClass2.yml');
		$this->object->process($this->builder);

		$definition = $this->builder->getDefinition('jan_marek.autowiring_bundle.tests.fixtures.example_class');
		$this->assertNull($definition->getClass());
	}

	public function testClassIsNotGuessedInAbstractDefinitions2()
	{
		$this->loader->load(__DIR__ . '/Fixtures/guessClass2.yml');
		$this->object->process($this->builder);

		$definition = $this->builder->getDefinition('foobar');
		$this->assertNull($definition->getClass());
	}

	public function testClassDoesNotExist()
	{
		$this->loader->load(__DIR__ . '/Fixtures/guessClassFactory.yml');
		$this->object->process($this->builder);

		$definition = $this->builder->getDefinition('class_not_exist');
		$this->assertNull($definition->getClass());
	}

}
