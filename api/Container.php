<?php

namespace Root\api;
use ArrayAccess;
use ReflectionClass;
use Root\helpers\Debug;

/**
 * Class Container
 * @package Root\api
 *
 * @property Config $config
 * @property Request $request
 * @property Database $db
 * @property Settings $settings
 * @property Design $design
 * @property Products $products
 * @property Variants $variants
 * @property Categories $categories
 * @property Brands $brands
 * @property Features $features
 * @property Money $money
 * @property Pages $pages
 * @property Blog $blog
 * @property Cart $cart
 * @property Image $image
 * @property Delivery $delivery
 * @property Payment $payment
 * @property Orders $orders
 * @property Users $users
 * @property Coupons $coupons
 * @property Comments $comments
 * @property Feedbacks $feedbacks
 * @property Notify $notify
 * @property Managers $managers
 *
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
     * @param array $values
     */
    public function __construct($values = [])
    {
        if( !empty($values) ) {
            foreach ($values as $alice => $value) {
                $this->set($alice, $value);
            }
        }
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
     * @param $value
     * @param bool $share
     */
    public function set(string $name, $value, bool $share = true)
    {
        $this->services[$name] = $value;
        $this->shared[$name] = $share;
    }

    /**
     * @param string $name
     * @param $value
     */
    public function singleton(string $name, $value)
    {
        $this->set($name, $value, true);
    }

    /**
     * @param string $name
     * @param $value
     */
    public function instance(string $name, $value)
    {
        $this->set($name, $value, false);
    }

    /**
     * @param string $interface
     * @return bool
     */
    public function has(string $interface): bool
    {
        return isset($this->services[$interface]) || isset($this->instantiated[$interface]);
    }

    /**
     * @param string $name
     * @return array|mixed|object|string
     * @throws \ReflectionException
     */
    public function get(string $name)
    {
        if (isset($this->instantiated[$name]) && $this->shared[$name]) {
            return $this->instantiated[$name];
        }

        $service = $this->services[$name];

        if( is_string($service) && class_exists($service) ) {
            $object = $this->build_object($service);
        }
        else if( is_array($service) && class_exists($obj = reset($service)) ) {
            $params = array_slice($service, 1);
            //$object = new $obj(...$params);
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
     */
    public function unset($name)
    {
        $this->unset($this->instantiated[$name]);
        $this->unset($this->services[$name]);
        $this->unset($this->shared[$name]);
    }

    /**
     * @param $abstract
     * @param $alias
     */
    public function alias($abstract, $alias)
    {
        $this->aliases[$alias] = $abstract;
    }

    /**
     * @param mixed $offset
     * @return bool|void
     */
    public function offsetExists($offset)
    {
        $this->has($offset);
    }

    /**
     * @param mixed $offset
     * @return mixed|void
     */
    public function offsetGet($offset)
    {
        $this->get($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        $this->unset($offset);
    }
}