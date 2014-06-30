<?php

namespace JanMarek\AutowiringBundle\Tests\Fixtures;

class ClassWithDefaultArguments
{

	/** @var mixed[] */
	private $args;

	/**
	 * @param \JanMarek\AutowiringBundle\Tests\Fixtures\ExampleClass $c1
	 * @param int $param
	 */
	public function __construct(ExampleClass $c1 = NULL, $param = 123)
	{
		$this->args = array($c1, $param);
	}

	/**
	 * @return mixed[]
	 */
	public function getArgs()
	{
		return $this->args;
	}

}
