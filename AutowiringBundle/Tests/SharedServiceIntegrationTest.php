<?php

namespace JanMarek\AutowiringBundle\Tests;

use JanMarek\AutowiringBundle\ExpandServiceValuesPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class SharedServiceIntegrationTest extends BaseTestCase
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

	public function testSharedService()
	{
		$this->loader->load(__DIR__ . '/Fixtures/sharedService.yml');
		$this->container->compile();

		$one = $this->container->get('foo');
		$two = $this->container->get('foo');
		$this->assertNotSame($one, $two);
	}

}
