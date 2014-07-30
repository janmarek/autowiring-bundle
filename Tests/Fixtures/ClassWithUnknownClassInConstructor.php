<?php

namespace JanMarek\AutowiringBundle\Tests\Fixtures;

class ClassWithUnknownClassInConstructor
{

    public function __construct(Foo $foo)
    {

    }

}
