<?php

namespace JanMarek\AutowiringBundle\Tests\Fixtures;

class ClassWithConstructor
{

	public function __construct(ExtendingClass $c1, ExampleClass2 $c2)
	{

	}

}
