<?php

use THCFrame\Model\Model;

/**
 * Description of UserModel
 *
 * @author Tomy
 */
class App_Model_Potvrzeni extends Model
{

    /**
     * @column
     * @readwrite
     * @primary
     * @type auto_increment
     */
    protected $_id;

    /**
     * @column
     * @readwrite
     * @type text
     * @lenght 12
     *
     * @validate required, date
     * @label datum schuzky
     */
    protected $_datum_schuzky;
    
    /**
     * @column
     * @readwrite
     * @type boolean
     * @index
     */
    protected $_je_potvrzen;

    /**
     * @column
     * @readwrite
     * @type datetime
     */
    protected $_created;

    /**
     * @column
     * @readwrite
     * @type datetime
     */
    protected $_modified;

}
