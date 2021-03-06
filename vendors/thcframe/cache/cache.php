<?php

namespace THCFrame\Cache;

use THCFrame\Core\Base as Base;
use THCFrame\Events\Events as Events;
use THCFrame\Registry\Registry as Registry;
use THCFrame\Cache\Exception as Exception;

/**
 * Factory class
 * 
 * @author Tomy
 */
class Cache extends Base
{

    /**
     * @readwrite
     */
    protected $_type;

    /**
     * @readwrite
     */
    protected $_options;

    /**
     * 
     * @param type $method
     * @return \THCFrame\Cache\Exception\Implementation
     */
    protected function _getImplementationException($method)
    {
        return new Exception\Implementation(sprintf("%s method not implemented", $method));
    }

    /**
     * Factory method
     * 
     * @return \THCFrame\Cache\Cache\Driver\Memcached
     * @throws Exception\Argument
     */
    public function initialize()
    {
        Events::fire("framework.cache.initialize.before", array($this->type, $this->options));

        if (!$this->type) {
            $configuration = Registry::get("configParsed");

            if (empty($configuration)) {
                $configuration = $configuration->initialize();

                if (DEBUG) {
                    $parsed = $configuration->parse("configuration/config_dev");
                } else {
                    $parsed = $configuration->parse("configuration/config");
                }

                if (!empty($parsed->cache->default) && !empty($parsed->cache->default->type)) {
                    $this->type = $parsed->cache->default->type;
                    unset($parsed->cache->default->type);
                    $this->options = (array) $parsed->cache->default;
                }
            } else {
                if (!empty($configuration->cache->default) && !empty($configuration->cache->default->type)) {
                    $this->type = $configuration->cache->default->type;
                    unset($configuration->cache->default->type);
                    $this->options = (array) $configuration->cache->default;
                }
            }
        }

        if (!$this->type) {
            throw new Exception\Argument("Invalid type");
        }

        Events::fire("framework.cache.initialize.after", array($this->type, $this->options));

        switch ($this->type) {
            case "memcached": {
                    return new Driver\Memcached($this->options);
                    break;
                }
            case "filecache": {
                    return new Driver\Filecache($this->options);
                    break;
                }
            default: {
                    throw new Exception\Argument("Invalid type");
                    break;
                }
        }
    }

}
