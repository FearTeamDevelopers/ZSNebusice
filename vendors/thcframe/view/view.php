<?php

namespace THCFrame\View;

use THCFrame\Core\Base as Base;
use THCFrame\Events\Events as Events;
use THCFrame\Template as Template;
use THCFrame\View\Exception as Exception;

/**
 * Description of View
 *
 * @author Tomy
 */
class View extends Base
{

    /**
     * @readwrite
     */
    protected $_file;

    /**
     * @readwrite
     */
    protected $_data;

    /**
     * @readwrite
     */
    protected $_flashMessage;

    /**
     * @read
     */
    protected $_template;

    /**
     * 
     * @param type $options
     */
    public function __construct($options = array())
    {
        parent::__construct($options);

        Events::fire("framework.view.construct.before", array($this->file));

        $this->_template = new Template\Template(array(
            "implementation" => new Template\Implementation\Extended()
        ));

        $this->_checkMessage();

        Events::fire("framework.view.construct.after", array($this->file, $this->template));
    }

    /**
     * 
     * @param type $method
     * @return \THCFrame\View\Exception\Implementation
     */
    protected function _getImplementationException($method)
    {
        return new Exception\Implementation(sprintf("%s method not implemented", $method));
    }

    /**
     * Method check if there is any message set or not
     */
    private function _checkMessage()
    {
        if (isset($_SESSION['flashMessage'])) {
            $this->set("flashMessage", $_SESSION['flashMessage']);
            unset($_SESSION['flashMessage']);
        } else {
            $this->set("flashMessage", "");
        }

        if (isset($_SESSION['longFlashMessage'])) {
            $this->set("longFlashMessage", $_SESSION['longFlashMessage']);
            unset($_SESSION['longFlashMessage']);
        } else {
            $this->set("longFlashMessage", "");
        }
    }

    /**
     * 
     * @return string
     */
    public function render()
    {
        Events::fire("framework.view.render.before", array($this->file));

        if (!file_exists($this->file)) {
            return "";
        }

        return $this->template
                        ->parse(file_get_contents($this->file))
                        ->process($this->data);
    }

    /**
     * 
     * @param type $key
     * @param type $default
     * @return type
     */
    public function get($key, $default = "")
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
        return $default;
    }

    /**
     * 
     * @param type $key
     * @param type $value
     * @throws Exception\Data
     */
    protected function _set($key, $value)
    {
        if (!is_string($key) && !is_numeric($key)) {
            throw new Exception\Data("Key must be a string or a number");
        }

        $data = $this->data;

        if (!$data) {
            $data = array();
        }

        $data[$key] = $value;
        $this->data = $data;
    }

    /**
     * 
     * @param type $key
     * @param type $value
     * @return \THCFrame\View\View
     */
    public function set($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $_key => $value) {
                $this->_set($_key, $value);
            }
            return $this;
        }

        $this->_set($key, $value);
        return $this;
    }

    /**
     * 
     * @param type $key
     * @return \THCFrame\View\View
     */
    public function erase($key)
    {
        unset($this->data[$key]);
        return $this;
    }

    /**
     * 
     * @param text $msg
     * @return text
     */
    public function flashMessage($msg = "")
    {
        if (!empty($msg)) {
            $_SESSION['flashMessage'] = $msg;
        } else {
            return $this->get('flashMessage');
        }
    }

    /**
     * 
     * @param text $msg
     * @return text
     */
    public function longFlashMessage($msg = "")
    {
        if (!empty($msg)) {
            $_SESSION['longFlashMessage'] = $msg;
        } else {
            return $this->get('longFlashMessage');
        }
    }

}
