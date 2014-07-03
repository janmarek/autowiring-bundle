Autowiring Bundle [![Build Status](https://secure.travis-ci.org/janmarek/autowiring-bundle.png?branch=master)](http://travis-ci.org/janmarek/autowiring-bundle)
=================

The aim of this Symfony2 bundle is to simplify configuration of services in Symfony Dependency Injection Container.

Autowiring
----------

In Symfony 2 you have to configure parameters of service constructors. But we can use type information to configure service
arguments automatically. Autowiring uses PHP reflection to find out how the service constructor parameters look like and
what types they have. Then it searches the container for services with classes that can be passed to that argument.
If there is only one, autowiring configures that argument automatically for you. Otherwise exception is thrown and you have to
configure service manually like you usually do.

Installation instructions
-------------------------

Install via composer:

```sh
composer require janmarek/autowiring-bundle
```

Enable bundle in your application kernel.

```php
// app/AppKernel.php
public function registerBundles()
{
    return [
        // ...
        new JanMarek\AutowiringBundle\JanMarekAutowiringBundle(),
        // ...
    ];
}
```

Features
--------

**Constructor autowiring**

```php
class Foo
{
    public function __construct(Bar $bar)
    {
        ...
    }
}

class Bar
{

}
```

```yaml
services:
    service_foo:
        class: Foo
        # arguments are configured automatically by types
        
    service_bar:
        class: Bar
```

**Setter autowiring**

```php
class ClassWithSetters
{
    public function setObject(Bar $bar)
    {
        ...
    }
}
```

```yaml
services:
    withSetters:
        class: ClassWithSetters
        calls:
            - [setObject, []] # argument(s) are autowired
```

**Setting arguments by parameter name**

You can set some service constructor or setter arguments by their name in PHP code. Other parameters would be autowired.

```php
class ArgsByName
{
    public function __construct(Foo $foo, $namedArg)
    {
    
    }

    public function setBarAndSomethingElse(Bar $bar, $somethingElse)
    {
        ...
    }
}
```

```yaml
parameters:
    param1: 123
    param2: 456

services:
    service_foo:
        class: Foo
        
    service_bar:
        class: Bar
        
    service_with_args_by_name:
        class: ArgsByName
        arguments:
            namedArg: %param1%
            # params foo is autowired
        calls:
            - [setObject, { somethingElse: %param2% }]
            # param bar is autowired
```

**Class guessing by naming convention**

If you don't set service class, AutowiringBundle converts service name to a class name and adds it to service definition
if that class exists. Rules for conversion are - underscore names are converted to CamelCase and "." is used as namespace
separator.  

```yaml
services:
    # class is automatically set to Vendor\NameSpace\ClassName
    vendor.name_space.class_name:
```

**Conflict prevention**

If constructor needs a service and there are more services implementing given interface,
autowiring can't normally choose one of them and throws an exception.

But if you name service using naming convention described in previous section, it would be 
used as a default service in conflict situation.

```php
namespace NameSpace;

class Foo
{
    public function __construct(Bar $bar)
    {
        ...
    }
}

class Bar
{
    public function __construct($value)
    {
    
    }
}
```

```yaml
services:
    name_space.bar:
        class: NameSpace\Bar
        arguments: [123]
        
    other_bar:
        class: NameSpace\Bar
        arguments: [456]
        
    name_space.foo:
        class: NameSpace\Foo
        # service name_space.bar is autowired to constructor
```

License
-------

BSD
      


