<?php

namespace JanMarek\AutowiringBundle\Tests\Fixtures;

class ExampleFactory
{

	/**
	 * @param \JanMarek\AutowiringBundle\Tests\Fixtures\ExampleClass $obj
	 * @param mixed $foo
	 * @param mixed $bar
	 * @return \JanMarek\AutowiringBundle\Tests\Fixtures\ClassWithSetters
	 */
	public function create(ExampleClass $obj, $foo, $bar)
	{
		$ret = new ClassWithSetters();
		$ret->someMethod($foo, $bar);
		$ret->setObject($obj);

		return $ret;
	}

	/**
	 * @param \JanMarek\AutowiringBundle\Tests\Fixtures\ExampleClass $obj
	 * @param string $foo
	 * @param string $bar
	 * @return \JanMarek\AutowiringBundle\Tests\Fixtures\Example3
	 */
	public static function staticCreate(ExampleClass $obj, $foo, $bar)
	{
		return new Example3();
	}

}
