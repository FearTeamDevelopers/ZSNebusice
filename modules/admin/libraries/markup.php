<?php

namespace Admin\Libraries;

/**
 * Description of Markup
 *
 * @author Tomy
 */
class Markup {

    public function __construct() {
        
    }

    public function __clone() {
        
    }

    /**
     * 
     * @param type $array
     * @param type $key
     * @param type $separator
     * @param type $before
     * @param type $after
     * @return string
     */
    public static function errors($array, $key, $separator = "<br />", $before = "<span class=\"error\">", $after = "</span>") {
        if (isset($array[$key])) {
            return $before . join($separator, $array[$key]) . $after;
        }
        return "";
    }

}