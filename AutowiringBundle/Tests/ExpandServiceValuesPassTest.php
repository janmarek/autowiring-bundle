<?php

namespace JanMarek\AutowiringBundle\Tests;

use JanMarek\AutowiringBundle\ExpandServiceValuesPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ExpandServiceValuesPassTest extends BaseTestCase
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
	}

	public function testExpandingValues()
	{
		$this->loader->load(__DIR__ . '/Fixtures/expandServiceValues.yml');
		$this->container->compile();

		$this->assertFalse($this->container->has('foo%foo%'));
		$this->assertInstanceOf(
			'JanMarek\AutowiringBundle\Tests\Fixtures\ClassWithExpandedNameFoobar',
			$this->container->get('foobar')
		);

		$this->assertInstanceOf(
			'JanMarek\AutowiringBundle\Tests\Fixtures\ClassWithExpandedNameFoobar',
			$this->container->get('barbar')
		);

		$this->assertInstanceOf(
			'JanMarek\AutowiringBundle\Tests\Fixtures\ClassWithExpandedNameFoobar',
			$this->container->get('foobarbar')
		);
	}

	public function testTwoPercentsAreNotReplaces()
	{
		$this->loader->load(__DIR__ . '/Fixtures/expandServiceValues.yml');
		$this->container->compile();

		$this->assertTrue($this->container->has('barbar%%foo%%'));
	}

}
