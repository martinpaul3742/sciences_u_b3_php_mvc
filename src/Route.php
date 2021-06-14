<?php

namespace App;

class Route
{
    private $path;
    private $httpMethod;
    private $name;
    private $class;
    private $method;


    function __construct(array $params = [])
    {
        $this->path = isset($params['path']) ? $params['path'] : false;
        $this->httpMethod = isset($params['httpMethod']) ? $params['httpMethod'] : 'GET';
        $this->name = isset($params['name']) ? $params['name'] : $this->path;
        $this->class = isset($params['class']) ? $params['class'] : false;
        $this->method = isset($params['method']) ? $params['method'] : false;
    }


    /**
     * Get the value of method
     */ 
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set the value of method
     *
     * @return  self
     */ 
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get the value of class
     */ 
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set the value of class
     *
     * @return  self
     */ 
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get the value of name
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */ 
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of httpMethod
     */ 
    public function getHttpMethod()
    {
        return $this->httpMethod;
    }

    /**
     * Set the value of httpMethod
     *
     * @return  self
     */ 
    public function setHttpMethod($httpMethod)
    {
        $this->httpMethod = $httpMethod;

        return $this;
    }

    /**
     * Get the value of path
     */ 
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the value of path
     *
     * @return  self
     */ 
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }
}
