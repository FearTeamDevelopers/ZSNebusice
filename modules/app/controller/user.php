<?php

use App\Libraries\Controller as Controller;
use THCFrame\Registry\Registry as Registry;
use THCFrame\Request\RequestMethods as RequestMethods;

/**
 * Description of UserController
 *
 * @author Tomy
 */
class App_Controller_User extends Controller
{

    /**
     * 
     */
    public function login()
    {
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
                    $status = $security->authenticate($email, $password);

                    if ($status) {
                        self::redirect("/");
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
    public function logout()
    {
        $security = Registry::get("security");
        $security->logout();
        self::redirect("/");
    }

    /**
     * 
     */
    public function register($key)
    {
        if ($key === "8406c6ad864195ed144ab5c87621b6c233b548baeae6956df3463876aed6bc22d4a") {
            $view = $this->getActionView();
            $errors = array();
            $view->set("errors", $errors);

            if (RequestMethods::post("register")) {
                $security = Registry::get("security");

                $passComparation = $security->comparePasswords(
                        RequestMethods::post("password"), RequestMethods::post("password2")
                );

                if (!$passComparation) {
                    $errors["password2"] = array("Paswords doesnt match");
                }

                $email = App_Model_User::first(array('email = ?' => RequestMethods::post("email")), array('email'));

                if ($email) {
                    $errors["email"] = array("Email is already used");
                }

                $hash = $security->getHash(RequestMethods::post("password"));

                $user = new App_Model_User(array(
                    "firstname" => RequestMethods::post("firstname"),
                    "lastname" => RequestMethods::post("lastname"),
                    "email" => RequestMethods::post("email"),
                    "password" => $hash,
                    "role" => "role_member",
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

                if (empty($errors) && $user->validate()) {
                    try {
                        $path = $this->_upload("photo", $user);
                        $user->setPhoto($path);
                    } catch (\Exception $e) {
                        $errors["photo"] = array($e->getMessage());
                    }

                    if (empty($errors)) {
                        $user->save();

                        $view->flashMessage("Registration completed");
                        self::redirect("/");
                    } else {
                        $view->set("errors", $errors + $user->getErrors());
                    }
                } else {
                    $view->set("errors", $errors + $user->getErrors());
                }
            }
        } else {
            self::redirect("/");
        }
    }

    /**
     * @before _secured
     */
    public function edit()
    {
        $view = $this->getActionView();
        $userId = $this->getUser()->getId();
        $errors = array();

        //required to activate database connection
        $user = App_Model_User::first(array(
                    "id = ?" => $userId
        ));

        if (RequestMethods::post("editProfile")) {
            $security = Registry::get("security");

            $passComparation = $security->comparePasswords(
                    RequestMethods::post("password"), RequestMethods::post("password2")
            );

            if (!$passComparation) {
                $errors["password2"] = array("Paswords doesnt match");
            }

            if (RequestMethods::post("email") != $user->email) {
                $email = App_Model_User::first(array('email = ?' => RequestMethods::post("email", $user->email)), array('email'));
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
            $user->dob = date("Y-m-d", strtotime(RequestMethods::post("dob")));
            $user->cfbuPersonalNum = RequestMethods::post("cfbuPersonalNum");
            $user->playerNum = RequestMethods::post("playerNum");
            $user->team = RequestMethods::post("team");
            $user->nickname = RequestMethods::post("nickname");
            $user->position = RequestMethods::post("position");
            $user->grip = RequestMethods::post("grip");
            $user->other = RequestMethods::post("other");

            if (empty($errors) && $user->validate()) {
                try {
                    $path = $this->_upload("photo", $user);

                    if ($path != "") {
                        $user->setPhoto($path);
                    }
                } catch (\Exception $e) {
                    $errors["photo"] = array($e->getMessage());
                }

                if (empty($errors)) {
                    $user->save();
                    $security->setUser($user);

                    $view->flashMessage("All changes were successfully saved");
                    self::redirect("/");
                } else {
                    $view->set("errors", $errors + $user->getErrors());
                }
            }

            $view->set("errors", $errors + $user->getErrors());
        }

        $view->set("user", $user);
    }

}
