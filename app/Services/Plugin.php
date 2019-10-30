<?php
namespace App\Services;


use App\Model\Post;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Contracts\Support\Arrayable;

class  Plugin implements Arrayable
{

    protected $path;

    protected $packageInfo;

    protected $enabled = false;

    protected $namespace;

    public function __construct($path, $packageInfo)
    {
        $this->path = $path;
        $this->packageInfo = $packageInfo;
    }

    public function __get($name)
    {
        $this->packageInfoAttribute(Str::snake($name, '-'));
    }

    public function __isset($name)
    {
        return isset($this->{$name}) || $this->packageInfoAttribute(Str::snake($name, '-'));
    }

    public function packageInfoAttribute($name)
    {
        return Arr::get($this->packageInfo, $name);
    }

    public function getNameSpace()
    {
        return $this->namespace;
    }
    public function setNameSpace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }


    /**
     * @param bool $enabled
     * @return Plugin
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }


    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Generates an array result for the object.
     *
     * @return array
     */
    public function toArray()
    {
        return (array) array_merge([
            'name'          => $this->name,
            'version'       => $this->getVersion(),
            'path'          => $this->path
        ], $this->packageInfo);
    }


}