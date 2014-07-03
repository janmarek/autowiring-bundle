<?php

namespace JanMarek\AutowiringBundle\Tests\Fixtures;

class ClassWithSetters
{

	/**
	 * @var mixed[]
	 */
	private $values;

	public function __construct()
	{
		$this->values = array();
	}

	/**
	 * @param mixed $foo
	 * @param mixed $bar
	 */
	public function someMethod($foo, $bar)
	{
		$this->values[] = $foo;
		$this->values[] = $bar;
	}

	public function setObject(ExampleClass $o)
	{
		$this->values[] = $o;
	}

	/**
	 * @return mixed[]
	 */
	public function getValues()
	{
		return $this->values;
	}

}
