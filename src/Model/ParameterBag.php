<?php

namespace Mangadex\Model;

class ParameterBag
{
    private $parameters;

    function __construct($parameters = [])
    {
        $this->parameters = $parameters;
    }

    public function has($key)
    {
        return array_key_exists($key, $this->parameters);
    }

    public function get($key, $default = null)
    {
        return array_key_exists($key, $this->parameters) ? $this->parameters[$key] : $default;
    }

    public function getInt($key, $default = 0)
    {
        return (int)$this->get($key, $default);
    }

    public function getBoolean($key, $default = false)
    {
        return filter_var($this->get($key, $default), \FILTER_VALIDATE_BOOLEAN);
    }

    public function getList($key, $default = [], $delimiter = ',')
    {
        return explode($delimiter, $this->get($key, '')) ?: $default;
    }
}
