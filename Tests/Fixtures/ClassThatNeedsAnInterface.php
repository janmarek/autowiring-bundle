<?php

namespace JanMarek\AutowiringBundle\Tests\Fixtures;

class ClassThatNeedsAnInterface
{

    /** @var \JanMarek\AutowiringBundle\Tests\Fixtures\InterfaceForClassWithInterface */
    private $interface;

    public function __construct(InterfaceForClassWithInterface $interface)
    {
        $this->interface = $interface;
    }

    /**
     * @return \JanMarek\AutowiringBundle\Tests\Fixtures\InterfaceForClassWithInterface
     */
    public function getInterface()
    {
        return $this->interface;
    }

}
