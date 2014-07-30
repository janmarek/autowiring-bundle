<?php

namespace JanMarek\AutowiringBundle\Tests\Fixtures;

class ClassWithUnknownParameterType
{

    /**
     * @param mixed $param
     */
    public function __construct($param)
    {

    }

}
