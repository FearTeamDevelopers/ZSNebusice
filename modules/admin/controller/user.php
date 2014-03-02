<?php

use Admin\Libraries\Controller as Controller;
use THCFrame\Registry\Registry as Registry;
use THCFrame\Request\RequestMethods as RequestMethods;

/**
 * Description of UserController
 *
 * @author Tomy
 */
class Admin_Controller_User extends Controller {

    /**
     * @before _secured, _admin
     * @param type $name
     * @param type $username
     * @return string
     * @throws \Exception
     */
    private function _upload($name, $user) {

        if (isset($_FILES[$name]) && !empty($_FILES[$name]["name"])) {
            $file = $_FILES[$name];
            $path = "/public/uploads/team/";

            $size = filesize($file["tmp_name"]);
            $extension = pathinfo($file["name"], PATHINFO_EXTENSION);
            $filename = $user->getEmail() . "_" . $user->getId() . "." . $extension;

            if ($size > 5000000) {
                throw new \Exception("Image size exceeds the maximum size limit");
            } elseif (!in_array($extension, self::$_imageExtensions)) {
                throw new \Exception("Images can only be with jpg, jpeg, png or gif extension");
            } elseif (file_exists("." . $path . $filename)) {
                unlink("." . $path . $filename);

                if (move_uploaded_file($file["tmp_name"], "." . $path . $filename)) {
                    return $path . $filename;
                } else {
                    throw new \Exception("An error occured while uploading the photo");
                }
            } else {
                if (move_uploaded_file($file["tmp_name"], "." . $path . $filename)) {
                    return $path . $filename;
                } else {
                    throw new \Exception("An error occured while uploading the photo");
                }
            }
        } else {
            return "";
        }
    }

    /**
     * 
     */
    public function login() {
        if (RequestMethods::post("login")) {
            $email = RequestMethods::post("email");
            $password = RequestMethods::post("password");

            $view = $this->getActionView();
            $error = false;

            if (empty($email)) {
                $view->set("email_error", "Email not provided");
                $error = true;
            }

            if (empty($password)) {
                $view->set("password_error", "Password not provided");
                $error = true;
            }

            if (!$error) {
                try {
                    $security = Registry::get("security");
                    $status = $security->authenticate($email, $password, true);

                    if ($status) {
                        self::redirect("/admin/");
                    } else {
                        $view->set("account_error", "Email address and/or password are incorrect");
                    }
                } catch (\Exception $e) {
                    $view->set("account_error", $e->getMessage());
                }
            }
        }
    }

    /**
     * 
     */
    public function logout() {
        $security = Registry::get("security");
        $security->logout();
        self::redirect("/admin/");
    }

    /**
     * @before _secured, _admin
     */
    public function index() {
        $view = $this->getActionView();
        $security = Registry::get("security");

        $superAdmin = $security->isGranted("role_superadmin");

        $users = Admin_Model_User::all(array(), array("id", "firstname", "lastname", "email", "role", "active", "team", "created"), "id", "asc"
        );

        $view->set("users", $users)
                ->set("superadmin", $superAdmin);
    }

    /**
     * @before _secured, _admin
     */
    public function add() {
        $security = Registry::get("security");
        $view = $this->getActionView();

        $errors = array();
        $superAdmin = $security->isGranted("role_superadmin");

        if (RequestMethods::post("addUser")) {


            $passComparation = $security->comparePasswords(
                    RequestMethods::post("password"), RequestMethods::post("password2")
            );

            if (!$passComparation) {
                $errors["password2"] = array("Paswords doesnt match");
            }

            $email = Admin_Model_User::first(array('email = ?' => RequestMethods::post("email")), array('email'));

            if ($email) {
                $errors["email"] = array("Email is already used");
            }

            $hash = $security->getHash(RequestMethods::post("password"));

            $user = new Admin_Model_User(array(
                "firstname" => RequestMethods::post("firstname"),
                "lastname" => RequestMethods::post("lastname"),
                "email" => RequestMethods::post("email"),
                "password" => $hash,
                "role" => RequestMethods::post("role", "role_member"),
                "dob" => date("Y-m-d", strtotime(RequestMethods::post("dob"))),
                "playerNum" => RequestMethods::post("playerNum"),
                "cfbuPersonalNum" => RequestMethods::post("cfbuPersonalNum"),
                "team" => RequestMethods::post("team"),
                "nickname" => RequestMethods::post("nickname"),
                "photo" => RequestMethods::post("photo"),
                "position" => RequestMethods::post("position"),
                "grip" => RequestMethods::post("grip"),
                "other" => RequestMethods::post("other")
            ));

            try {
                $path = $this->_upload("photo", $user);
                $user->setPhoto($path);
            } catch (\Exception $e) {
                $errors["photo"] = array($e->getMessage());
            }

            if (empty($errors) && $user->validate()) {
                $user->save();

                $view->flashMessage("Account has been successfully created");
                self::redirect("/admin/user/");
            } else {
                $view->set("errors", $errors + $user->getErrors());
            }
        }

        $view->set("superadmin", $superAdmin);
    }

    /**
     * @before _secured, _admin
     * @param type $id
     */
    public function edit($id) {
        $view = $this->getActionView();
        $security = Registry::get("security");

        $errors = array();
        $superAdmin = $security->isGranted("role_superadmin");

        $user = Admin_Model_User::first(array(
                    "id = ?" => $id
        ));

        if (NULL === $user) {
            $view->flashMessage("User not found");
            self::redirect("/admin/user/");
        }

        if (RequestMethods::post("editUser")) {


            $passComparation = $security->comparePasswords(
                    RequestMethods::post("password"), RequestMethods::post("password2")
            );

            if (!$passComparation) {
                $errors["password2"] = array("Paswords doesnt match");
            }

            if (RequestMethods::post("email") != $user->email) {
                $email = Admin_Model_User::first(array('email = ?' => RequestMethods::post("email", $user->email)), array('email'));
                if ($email) {
                    $errors["email"] = array("Email is already used");
                }
            }

            $pass = RequestMethods::post("password");
            if ($pass == "") {
                $hash = $user->password;
            } else {
                $hash = $security->getHash($pass);
            }

            $user->firstname = RequestMethods::post("firstname");
            $user->lastname = RequestMethods::post("lastname");
            $user->email = RequestMethods::post("email");
            $user->password = $hash;
            $user->role = RequestMethods::post("role");
            $user->dob = date("Y-m-d", strtotime(RequestMethods::post("dob")));
            $user->cfbuPersonalNum = RequestMethods::post("cfbuPersonalNum");
            $user->playerNum = RequestMethods::post("playerNum");
            $user->team = RequestMethods::post("team");
            $user->nickname = RequestMethods::post("nickname");
            $user->position = RequestMethods::post("position");
            $user->grip = RequestMethods::post("grip");
            $user->other = RequestMethods::post("other");
            $user->active = RequestMethods::post("active");

            try {
                $path = $this->_upload("photo", $user);

                if ($path != "") {
                    $user->setPhoto($path);
                }
            } catch (\Exception $e) {
                $errors["photo"] = array($e->getMessage());
            }

            if (empty($errors) && $user->validate()) {
                $user->save();

                $view->flashMessage("All changes were successfully saved");
                self::redirect("/admin/user/");
            }

            $view->set("errors", $errors + $user->getErrors());
        }

        $view->set("user", $user)
                ->set("superadmin", $superAdmin);
    }

    /**
     * 
     * @before _secured, _superadmin
     * @param type $id
     */
    public function delete($id) {
        $view = $this->getActionView();

        $user = Admin_Model_User::first(array(
                    "id = ?" => $id
                        ), array("id", "firstname", "lastname", "email")
        );

        if (NULL === $user) {
            $view->flashMessage("User not found");
            self::redirect("/admin/user/");
        }
        
        $view->set("user", $user);

        if (RequestMethods::post("deleteUser")) {
            if (NULL !== $user) {
                $message = "User " . $user->getFirstname() . " " . $user->getLastname() . " has been deleted";

                if (unlink("." . $user->getPhoto()) && $user->delete()) {
                    $view->flashMessage($message);
                    self::redirect("/admin/user/");
                } else {
                    $view->flashMessage("Unknown error eccured");
                    self::redirect("/admin/user/");
                }
            } else {
                $view->flashMessage("Unknown id provided");
                self::redirect("/admin/user/");
            }
        } elseif (RequestMethods::post("cancel")) {
            self::redirect("/admin/user/");
        }
    }

}