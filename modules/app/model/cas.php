<?php

use THCFrame\Model\Model as Model;

/**
 * Description of NewsModel
 *
 * @author Tomy
 */
class App_Model_Cas extends Model
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
     * @length 20
     * 
     * @validate time
     */
    protected $_cas_start;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 20
     * 
     * @validate time
     */
    protected $_cas_end;

}
