<?php

use Admin\Libraries\Controller as Controller;
use THCFrame\Request\RequestMethods as RequestMethods;

/**
 * Description of SponsorController
 *
 * @author Tomy
 */
class Admin_Controller_Sponsor extends Controller {

    /**
     * @before _secured, _admin
     * @param type $name
     * @param type $username
     * @return string
     * @throws \Exception
     */
    private function _upload($name, $sponsor) {

        if (isset($_FILES[$name]) && !empty($_FILES[$name]["name"])) {
            $file = $_FILES[$name];
            $path = "/public/uploads/sponsors/";

            $size = filesize($file["tmp_name"]);
            $extension = pathinfo($file["name"], PATHINFO_EXTENSION);
            $filename = "{$sponsor->getTitle()}_{$sponsor->getId()}.{$extension}";

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
            throw new \Exception("Logo cannot be empty");
        }
    }

    /**
     * @before _secured, _admin
     */
    public function index() {
        $view = $this->getActionView();

        $sponsors = App_Model_Sponsor::all();

        $view->set("sponsors", $sponsors);
    }

    /**
     * @before _secured, _admin
     */
    public function add() {
        $view = $this->getActionView();
        $errors = array();
        $view->set("errors", $errors);

        if (RequestMethods::post("addSponsor")) {

            $sponsor = new App_Model_Sponsor(array(
                "title" => RequestMethods::post("title"),
                "url" => RequestMethods::post("url")
            ));

            try {
                $path = $this->_upload("logo", $sponsor);
                $sponsor->setLogo($path);
            } catch (\Exception $e) {
                $errors["logo"] = array($e->getMessage());
            }

            if (empty($errors) && $sponsor->validate()) {
                $sponsor->save();

                $view->flashMessage("Sponsor has been successfully created");
                self::redirect("/admin/sponsor/");
            } else {
                $view->set("errors", $errors + $sponsor->getErrors());
            }
        }
    }

    /**
     * 
     * @before _secured, _admin
     * @param type $id
     */
    public function edit($id) {
        $view = $this->getActionView();
        $errors = array();

        $sponsor = App_Model_Sponsor::first(array(
                    "id = ?" => $id
        ));
        
        if (NULL === $sponsor) {
            $view->flashMessage("Sponsor not found");
            self::redirect("/admin/sponsor/");
        }

        if (RequestMethods::post("editSponsor")) {
            $sponsor->title = RequestMethods::post("title");
            $sponsor->url = RequestMethods::post("url");
            $sponsor->active = RequestMethods::post("active");

            try {
                $path = $this->_upload("logo", $sponsor);
                $sponsor->setLogo($path);
            } catch (\Exception $e) {
                $errors["logo"] = array($e->getMessage());
            }

            if (empty($errors) && $sponsor->validate()) {
                $sponsor->save();

                $view->flashMessage("All changes were successfully saved");
                self::redirect("/admin/sponsor/");
            }

            $view->set("errors", $errors + $sponsor->getErrors());
        }

        $view->set("sponsor", $sponsor);
    }

    /**
     * 
     * @before _secured, _admin
     * @param type $id
     */
    public function delete($id) {
        $view = $this->getActionView();

        $sponsor = App_Model_Sponsor::first(array(
                    "id = ?" => $id
        ));
        
        if (NULL === $sponsor) {
            $view->flashMessage("Sponsor not found");
            self::redirect("/admin/sponsor/");
        }

        if (RequestMethods::post("deleteSponsor")) {
            if (NULL !== $sponsor) {
                if ($sponsor->delete()) {
                    $view->flashMessage("Sponsor has been deleted");
                    self::redirect("/admin/sponsor/");
                } else {
                    $view->flashMessage("Unknown error eccured");
                    self::redirect("/admin/sponsor/");
                }
            } else {
                $view->flashMessage("Unknown id provided");
                self::redirect("/admin/sponsor/");
            }
        } elseif (RequestMethods::post("cancel")) {
            self::redirect("/admin/sponsor/");
        }

        $view->set("sponsor", $sponsor);
    }

}