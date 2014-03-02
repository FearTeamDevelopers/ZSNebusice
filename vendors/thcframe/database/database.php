<?php

namespace THCFrame\Database;

use THCFrame\Core\Base as Base;
use THCFrame\Events\Events as Events;
use THCFrame\Registry\Registry as Registry;
//use THCFrame\Database\Database as Database;
use THCFrame\Database\Exception as Exception;

/**
 * Factory class
 * 
 * @author Tomy
 */
class Database extends Base
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
     * @return \THCFrame\Database\Exception\Implementation
     */
    protected function _getImplementationException($method)
    {
        return new Exception\Implementation(sprintf("%s method not implemented", $method));
    }

    /**
     * 
     * @return \THCFrame\Database\Database\Connector\Mysql
     * @throws Exception\Argument
     */
    public function initialize()
    {
        Events::fire("framework.database.initialize.before", array($this->type, $this->options));

        if (!$this->type) {
            $configuration = Registry::get("configuration");

            if ($configuration) {
                $configuration = $configuration->initialize();

                if (DEBUG) {
                    $parsed = $configuration->parse("configuration/config_dev");
                } else {
                    $parsed = $configuration->parse("configuration/config");
                }

                if (!empty($parsed->database->default) && !empty($parsed->database->default->type)) {
                    $this->type = $parsed->database->default->type;
                    unset($parsed->database->default->type);
                    $this->options = (array) $parsed->database->default;
                }
            }
        }

        if (!$this->type) {
            throw new Exception\Argument("Invalid type");
        }

        Events::fire("framework.database.initialize.after", array($this->type, $this->options));

        switch ($this->type) {
            case "mysql": {
                    return new Connector\Mysql($this->options);
                    break;
                }
            default: {
                    throw new Exception\Argument("Invalid type");
                    break;
                }
        }
    }

}
