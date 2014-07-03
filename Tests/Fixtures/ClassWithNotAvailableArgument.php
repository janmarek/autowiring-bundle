<?php

namespace JanMarek\AutowiringBundle\Tests\Fixtures;

class ClassWithNotAvailableArgument
{

	public function __construct(Example3 $o)
	{

	}

}
