<?php

namespace Root\api;
use ArrayAccess;
use ReflectionClass;
use Root\helpers\Debug;

/**
 * Class Container
 * @package Root\api
 */
class Container implements ArrayAccess
{
    /**
     * @var array
     */
    private $services = [];

    /**
     * @var array
     */
    private $instantiated = [];

    /**
     * @var array
     */
    private $shared = [];

    /**
     * @var array
     */
    private $aliases = [];

    /**
     * Container constructor.
     */
    public function __construct()
    {

    }

    /**
     * @param string $name
     * @param $service
     * @param bool $share
     */
    public function addInstance(string $name, $service, bool $share = true)
    {
        $this->services[$name] = $service;
        $this->instantiated[$name] = $service;
        $this->shared[$name] = $share;
    }

    /**
     * @param string $name
     * @param null $value
     * @param bool $share
     * @throws \Exception
     */
    public function set(string $name, $value = null, bool $share = true)
    {
        if(! is_string($name) ) {
            throw new \Exception('Container wrong name');
        }

        if( is_null($value) && class_exists($name) ) {
            $value = $name;
        }

        $this->services[$name] = $value;
        $this->shared[$name] = $share;
    }

    /**
     * @param string $name
     * @param null $value
     * @throws \Exception
     */
    public function singleton(string $name, $value = null)
    {
        $this->set($name, $value, true);
    }

    /**
     * @param string $name
     * @param null $value
     * @throws \Exception
     */
    public function instance(string $name, $value = null)
    {
        $this->set($name, $value, false);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->services[$name]) ||
               isset($this->instantiated[$name]) ||
               $this->is_alias($name);
    }

    /**
     * @param string $name
     * @return array|mixed|object|string
     * @throws \ReflectionException
     */
    public function get(string $name)
    {
        $name = $this->get_alias($name);

        if (isset($this->instantiated[$name]) && $this->shared[$name]) {
            return $this->instantiated[$name];
        }

        $service = $this->get_service($name);

        if( is_string($service) && class_exists($service) ) {
            $object = $this->build_object($service);
        }
        else if( is_array($service) && class_exists($obj = reset($service)) ) {
            $params = array_slice($service, 1);
            $object = $this->build_object($obj, $params);
        }
        else if ( $service instanceof \Closure ) {
            $object = $service($this);
        } else if ( is_string($service) || is_array($service) ) {
            $object = $service;
        }

        if ( !empty($this->shared[$name]) ) {
            $this->instantiated[$name] = $object;
        }

        return $object;
    }

    /**
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    private function get_service($name)
    {
        $name = $this->get_alias($name);

        if( !empty($this->services[$name]) ) {
            return $this->services[$name];
        }

        throw new \Exception('No service ' . $name);
    }


    /**
     * @param string $name
     * @param array $params
     * @return object|string
     * @throws \ReflectionException
     */
    public function build_object(string $name, array $params = [])
    {
        $reflector = new ReflectionClass($name);

        if (! $reflector->isInstantiable() ) {
            return $name;
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return new $name;
        }

        $parameters = $constructor->getParameters();

        $arguments = [];
        foreach($parameters as $param) {
            if( $class = $param->getClass() ) {
                $className = $class->getName();
                if(! ($this->has($className)) ) {
                    $this->set($className, $className);
                }
                $arguments[] = $this->get($className);
            } elseif ( $param->isArray() ) {
                $arguments[] = [];
            } else {
                if( $param->isDefaultValueAvailable() ) {
                    throw new \Exception('Unable to resolve ' . $param->getName() . ' in service ' . $name);
                }
                $arguments[] = $param->isDefaultValueAvailable();
            }
        }

        return $reflector->newInstanceArgs($arguments);
    }

    /**
     * @param $name
     * @return array|mixed|object|string
     * @throws \ReflectionException
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @param $name
     * @throws \Exception
     */
    public function unset($name)
    {
        $this->unset($this->instantiated[$name]);
        $this->unset($this->services[$name]);
        $this->unset($this->shared[$name]);
        $this->unset($this->aliases[$name]);

        if( ($alias = $this->get_alias($name)) ) {
            $this->unset($alias);
        }
    }

    /**
     * @param $abstract
     * @param $alias
     */
    public function set_alias($abstract, $alias)
    {
        if( !$abstract || !$alias ) {
            return;
        }
        $this->aliases[$alias] = $abstract;
    }

    /**
     * @param $abstract
     * @return mixed
     * @throws \Exception
     */
    public function get_alias($abstract)
    {
        if (! isset($this->aliases[$abstract]) ) {
            return $abstract;
        }

        if ( $this->aliases[$abstract] === $abstract ) {
            throw new \Exception("[{$abstract}] is aliased to itself.");
        }

        return $this->get_alias($this->aliases[$abstract]);
    }

    /**
     * @param $name
     * @return bool
     */
    public function is_alias($name)
    {
        return isset($this->aliases[$name]);
    }

    /**
     * @param mixed $offset
     * @return bool|void
     * @throws \Exception
     */
    public function offsetExists($offset)
    {
        $this->has($offset);
    }

    /**
     * @param mixed $offset
     * @return mixed|void
     * @throws \ReflectionException
     */
    public function offsetGet($offset)
    {
        $this->get($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws \Exception
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * @param mixed $offset
     * @throws \Exception
     */
    public function offsetUnset($offset)
    {
        $this->unset($offset);
    }
}