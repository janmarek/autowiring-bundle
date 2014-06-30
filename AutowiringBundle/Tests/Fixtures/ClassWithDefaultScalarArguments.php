<?php

namespace JanMarek\AutowiringBundle\Tests\Fixtures;

class ClassWithDefaultScalarArguments
{

	/** @var mixed[] */
	private $args;

	/**
	 * @param NULL|mixed $nullable
	 * @param string $a
	 * @param int $param
	 */
	public function __construct($nullable = NULL, $a = 'foo', $param = 123)
	{
		$this->args = array($nullable, $a, $param);
	}

	/**
	 * @return mixed[]
	 */
	public function getArgs()
	{
		return $this->args;
	}

}
