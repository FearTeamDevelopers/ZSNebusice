<?php

use THCFrame\Model\Model;

/**
 * Description of UserModel
 *
 * @author Tomy
 */
class App_Model_Konzultace extends Model
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
     * @type integer
     *
     * @validate required,min(1), max(10)
     * @label cas
     */
    protected $_id_cas;

    /**
     * @column
     * @readwrite
     * @type integer
     *
     * @validate required, min(1), max(10)
     * @label ucitel
     */
    protected $_id_ucitel;

    /**
     * @column
     * @readwrite
     * @type integer
     *
     * @validate required, min(1), max(10)
     * @label rodic
     */
    protected $_id_rodic;

}
