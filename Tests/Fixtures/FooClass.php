<?php

namespace JanMarek\AutowiringBundle\Tests\Fixtures;

class FooClass extends FooSuperClass
{

    public function __construct(ExampleClass2 $class2, ExampleClass $class)
    {
        parent::__construct($class);
    }

}
