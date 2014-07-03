<?php

namespace JanMarek\AutowiringBundle\Tests\Fixtures;

class ClassWithExpandedNameFoobar
{

	/**
	 * @return \JanMarek\AutowiringBundle\Tests\Fixtures\ClassWithExpandedNameFoobar
	 */
	public static function createbar()
	{
		return new self();
	}

}
