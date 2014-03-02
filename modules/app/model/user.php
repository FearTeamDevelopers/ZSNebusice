<?php

use THCFrame\Model\Model;
use THCFrame\Security\UserInterface;

/**
 * Description of UserModel
 *
 * @author Tomy
 */
class App_Model_User extends Model implements UserInterface
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
     * @length 60
     * @index
     * @unique
     *
     * @validate required, email, max(60)
     * @label email address
     */
    protected $_email;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 130
     * @index
     *
     * @validate required, min(5), max(130)
     * @label password
     */
    protected $_password;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 25
     * 
     * @validate required, alpha, max(25)
     * @label user role
     */
    protected $_role;

    /**
     * @column
     * @readwrite
     * @type boolean
     * @index
     */
    protected $_active;
    
    /**
     * @column
     * @readwrite
     * @type text
     * @length 40
     *
     * @validate required, alpha, min(3), max(40)
     * @label first name
     */
    protected $_firstname;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 40
     *
     * @validate required, alpha, min(3), max(40)
     * @label last name
     */
    protected $_lastname;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 20
     *
     * @validate required, alphanumeric, min(1), max(20)
     * @label č. kabinetu
     */
    protected $_kabinet;

    /**
     * 
     * @param type $value
     * @throws \THCFrame\Security\Exception\Role
     */
    public function setRole($value) {
        $role = strtolower(substr($value, 0, 5));
        if ($role != 'role_') {
            throw new \THCFrame\Security\Exception\Role(sprintf('Role %s is not valid', $value));
        } else {
            $this->_role = $value;
        }
    }

    /**
     * 
     */
    public function isActive() {
        return (boolean) $this->_active;
    }

    /**
     * 
     * @return type
     */
    public function getWholeName() {
        return $this->_firstname . " " . $this->_lastname;
    }

    /**
     * 
     * @return type
     */
    public function __toString() {
        $str = "Id: {$this->_id} <br/>Email: {$this->_email} <br/> Name: {$this->_firstname} {$this->_lastname}";
        return $str;
    }

}
