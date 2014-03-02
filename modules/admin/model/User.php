<?php

use THCFrame\Model\Model;
use THCFrame\Security\UserInterface;

/**
 * Description of UserModel
 *
 * @author Tomy
 */
class Admin_Model_User extends Model implements UserInterface {

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
     * @type boolean
     * @index
     */
    protected $_active;

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
     * @lenght 12
     *
     * @validate required, date, max(12)
     * @label date of birth
     */
    protected $_dob;

    /**
     * @column
     * @readwrite
     * @type text
     * @lenght 2
     * 
     * @validate required, numeric, max(2)
     * @label player number
     */
    protected $_playerNum;

    /**
     * @column
     * @readwrite
     * @type text
     * @lenght 15
     * 
     * @validate required, numeric, max(15)
     */
    protected $_cfbuPersonalNum;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 2
     * 
     * @validate required, alpha, max(2)
     * @label team
     */
    protected $_team;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 30
     * 
     * @validate alphanumeric, max(30)
     * @label nickname
     */
    protected $_nickname;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * 
     * @validate max(100)
     * @label photo
     */
    protected $_photo;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 2
     * 
     * @validate required, alpha, max(2)
     * @label position
     */
    protected $_position;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 2
     * 
     * @validate required, alpha, max(2)
     * @label grip
     */
    protected $_grip;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 256
     * 
     * @validate alphanumeric, max(1024)
     * @label other
     */
    protected $_other;

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

    /**
     * 
     */
    public function preSave() {
        $primary = $this->getPrimaryColumn();
        $raw = $primary["raw"];

        if (empty($this->$raw)) {
            $this->setCreated(date("Y-m-d H:i:s"));
            $this->setActive(true);
        }
        $this->setModified(date("Y-m-d H:i:s"));
    }

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