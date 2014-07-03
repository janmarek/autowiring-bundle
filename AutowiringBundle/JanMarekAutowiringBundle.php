<?php

namespace JanMarek\AutowiringBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class JanMarekAutowiringBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new GuessClassPass());
        $container->addCompilerPass(new ExpandServiceValuesPass());
        $container->addCompilerPass(new AutowiringPass());
    }

}