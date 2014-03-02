<?php

namespace THCFrame\Configuration\Driver;

use THCFrame\Registry\Registry as Registry;
use THCFrame\Core\ArrayMethods as ArrayMethods;
use THCFrame\Configuration as Configuration;
use THCFrame\Configuration\Exception as Exception;

/**
 * Description of Ini
 *
 * @author Tomy
 */
class Ini extends Configuration\Driver
{

    /**
     * 
     * @param type $config
     * @param type $key
     * @param type $value
     * @return type
     */
    protected function _pair($config, $key, $value)
    {
        if (strstr($key, ".")) {
            $parts = explode(".", $key, 2);

            if (empty($config[$parts[0]])) {
                $config[$parts[0]] = array();
            }

            $config[$parts[0]] = $this->_pair($config[$parts[0]], $parts[1], $value);
        } else {
            $config[$key] = $value;
        }

        return $config;
    }

    /**
     * 
     * @param type $path
     * @return type
     * @throws Exception\Argument
     * @throws Exception\Syntax
     */
    public function parse($path)
    {
        if (empty($path)) {
            throw new Exception\Argument("\$path argument is not valid");
        }

        if (!isset($this->_parsed[$path])) {
            $config = array();

            ob_start();
            include("./application/{$path}.ini");
            $string = ob_get_contents();
            ob_end_clean();

            $pairs = parse_ini_string($string);

            if ($pairs == false) {
                throw new Exception\Syntax("Could not parse configuration file");
            }

            foreach ($pairs as $key => $value) {
                $config = $this->_pair($config, $key, $value);
            }

            $this->_parsed[$path] = ArrayMethods::toObject($config);
        }

        Registry::set("configParsed", $this->_parsed[$path]);

        return $this->_parsed[$path];
    }

}
