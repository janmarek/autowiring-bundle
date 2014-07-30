<?php

namespace JanMarek\AutowiringBundle\Tests\Fixtures;

class ClassWithExtendingClassInConstructor
{

    public function __construct(ExampleClass $c1, ExtendingClass $c2)
    {

    }

}
