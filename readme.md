Autowiring bundle
=================

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
    return array(
        // ...
        new JanMarek\AutowiringBundle\JanMarekAutowiringBundle(),
        // ...
    );
}
```

Features
--------

**Constructor autowiring**

```php
class Foo
{
    public function __construct(Bar $bar) {
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
    public function setObject(Bar $bar) {
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

```yaml
services:
    service_foo:
        class: Foo
        arguments:
            bar: @service_bar
        
    service_bar:
        class: Bar
        
    withSetters:
        class: ClassWithSetters
        calls:
            - [setObject, { bar: @service_bar }]
```

**Class guessing by naming convention**

If you don't set service class, AutowiringBundle converts service name to a class name and adds it to service definition
if that class exists. Rules for conversion are - underscore names are converted to CamelCase and "." is used as namespace
separator.

```yaml
services:
    vendor.name_space.class_name:
```


License
-------

BSD
      


