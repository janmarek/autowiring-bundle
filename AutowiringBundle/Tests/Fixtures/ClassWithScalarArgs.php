<?php

namespace JanMarek\AutowiringBundle\Tests\Fixtures;

class ClassWithScalarArgs
{

	/** @var mixed[] */
	private $args;

	/**
	 * @param mixed $foo
	 * @param mixed $paramOne
	 * @param mixed $paramTwo
	 * @param mixed $bar
	 */
	public function __construct($foo, $paramOne, $paramTwo, $bar)
	{
		$this->args = func_get_args();
	}

	/**
	 * @return mixed[]
	 */
	public function getArgs()
	{
		return $this->args;
	}

}
