<?php

use Admin\Libraries\Controller as Controller;
use THCFrame\Request\RequestMethods as RequestMethods;

/**
 * Description of GalleryController
 *
 * @author Tomy
 */
class Admin_Controller_Gallery extends Controller {

    /**
     *  
     * @param type $source
     * @param type $destination
     * @param type $forcedWidth
     * @param type $forcedHeight
     */
    private function _createThumbnail($sourceImage, $destination, $forcedWidth, $forcedHeight) {

        $sourceSize = getimagesize($sourceImage);

        // For a landscape picture or a square
        if ($sourceSize[0] >= $sourceSize[1]) {
            $finalWidth = ($forcedHeight / $sourceSize[1]) * $sourceSize[0];
            $finalHeight = $forcedHeight;
        }
        // For a potrait picture
        else {
            $finalWidth = ($forcedHeight / $sourceSize[1]) * $sourceSize[0];
            $finalHeight = $forcedHeight;
        }

        $newImage = imagecreatetruecolor($finalWidth, $finalHeight) or die('Canno initialize new GD image stream');

        $ext = pathinfo($sourceImage, PATHINFO_EXTENSION);

        if ($ext == 'jpg' || $ext == 'jpeg') {
            $image = imagecreatefromjpeg($sourceImage);
        } elseif ($ext == 'gif') {
            $image = imagecreatefromgif($sourceImage);
        } elseif ($ext == 'png') {
            $image = imagecreatefrompng($sourceImage);
        }

        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $finalWidth, $finalHeight, $sourceSize[0], $sourceSize[1]);

        if ($ext == 'jpg' || $ext == 'jpeg') {
            imagejpeg($newImage, $destination);
        } elseif ($ext == 'gif') {
            imagegif($newImage, $destination);
        } elseif ($ext == 'png') {
            imagepng($newImage, $destination);
        }

        //imagedestroy($newImage);
    }

    /**
     * @before _secured, _admin
     */
    public function index() {
        $view = $this->getActionView();

        $galleries = App_Model_Gallery::all();

        $view->set("galleries", $galleries);
    }

    /**
     * @before _secured, _admin
     */
    public function add() {
        $view = $this->getActionView();

        if (RequestMethods::post("addGallery")) {

            $gallery = new App_Model_Gallery(array(
                "title" => RequestMethods::post("title"),
                "description" => RequestMethods::post("description"),
                "avatar" => ""
            ));

            if ($gallery->validate()) {
                $gallery->save();

                $view->flashMessage("Gallery has been successfully created");
                self::redirect("/admin/gallery/");
            } else {
                $view->set("errors", $gallery->getErrors());
            }
        }
    }

    /**
     * 
     * @before _secured, _admin
     * @param number $id    gallery id
     */
    public function edit($id) {
        $view = $this->getActionView();

        $gallery = App_Model_Gallery::first(array(
                    "id = ?" => $id
        ));

        if (NULL === $gallery) {
            $view->flashMessage("Gallery not found");
            self::redirect("/admin/gallery/");
        }

        if (RequestMethods::post("editGallery")) {
            $gallery->title = RequestMethods::post("title");
            $gallery->description = RequestMethods::post("description");

            if ($gallery->validate()) {
                $gallery->save();

                $view->flashMessage("All changes were successfully saved");
                self::redirect("/admin/gallery/");
            }

            $view->set("errors", $gallery->getErrors());
        }

        $view->set("gallery", $gallery);
    }

    /**
     * 
     * @before _secured, _admin
     * @param number $id    gallery id
     */
    public function delete($id) {
        $view = $this->getActionView();

        $gallery = App_Model_Gallery::first(array(
                    "id = ?" => $id
        ));

        if (NULL === $gallery) {
            $view->flashMessage("Gallery not found");
            self::redirect("/admin/gallery/");
        }

        if (RequestMethods::post("deleteGallery")) {
            if (NULL !== $gallery) {
                if ($gallery->delete()) {
                    $view->flashMessage("Gallery has been deleted");
                    self::redirect("/admin/gallery/");
                } else {
                    $view->flashMessage("Unknown error eccured");
                    self::redirect("/admin/gallery/");
                }
            } else {
                $view->flashMessage("Unknown id provided");
                self::redirect("/admin/gallery/");
            }
        } elseif (RequestMethods::post("cancel")) {
            self::redirect("/admin/gallery/");
        }

        $view->set("gallery", $gallery);
    }

    /**
     * 
     * @before _secured, _admin
     * @param number $id    gallery id
     */
    public function detail($id) {
        $view = $this->getActionView();

        $gallery = App_Model_Gallery::first(array(
                    "id = ?" => $id
        ));

        if (NULL === $gallery) {
            $view->flashMessage("Gallery not found");
            self::redirect("/admin/gallery/");
        }

        $photos = App_Model_Photo::all(array(
                    "galleryId = ?" => $id
        ));

        $view->set("gallery", $gallery)
                ->set("photos", $photos);
    }

    /**
     * @before _secured, _admin
     * @param number $id    gallery id
     */
    public function upload($id) {
        $view = $this->getActionView();
        $path = "/public/uploads/gallery/{$id}/";

        $gallery = App_Model_Gallery::first(array(
                    "id = ?" => $id
                        ), array("id")
        );

        if (NULL === $gallery) {
            $view->flashMessage("Unknown gallery id provided");
            self::redirect("/admin/gallery/");
        }

        if (RequestMethods::post("uploadPhoto")) {
            if (!is_dir("." . $path)) {
                mkdir("." . $path);
            }

            $thumbHeight = 200;
            $message = "";
            $errorMessage = "";

            foreach ($_FILES['photo']['name'] as $i => $name) {
                $k = $i + 1;
                if ($name == "") {
                    $errorMessage .= "{$k}. Photo source can not be empty. Please click the 'Browse', 
                                  button, locate an image then click the 'Upload Files'<br/>";
                    continue;
                } else {
                    $size = filesize($_FILES['photo']['tmp_name'][$i]);
                    $extension = pathinfo($_FILES['photo']['name'][$i], PATHINFO_EXTENSION);
                    $filename = stripslashes($_FILES['photo']['name'][$i]);
                    
                    list($width, $height, $type, $attr) = getimagesize($_FILES['photo']['tmp_name'][$i]);

                    if ($size > 5000000) {
                        $errorMessage .= "{$k}. Your file {$filename} size exceeds the maximum size limit<br/>";
                        continue;
                    } else {
                        if (!in_array($extension, self::$_imageExtensions)) {
                            $errorMessage .= "{$k}. {$filename} - Images can only be with jpg, jpeg, png or gif extension<br/>";
                            continue;
                        } else {
                            $getname = explode(".", $filename);
                            $photoname = $getname[0];
                            
                            if(strlen($photoname) > 50){
                                $photoname = substr($photoname, 0, 50);
                            }
                            
                            $imageName = $photoname . "_large." . $extension;
                            $thumbName = $photoname . "_small." . $extension;

                            $imageLocName = $path . $imageName;
                            $thumbLocName = $path . $thumbName;

                            if (file_exists('.' . $imageLocName)) {
                                $errorMessage .= "{$k}. {$filename} already exists <br/>";
                                continue;
                            }

                            $copy = move_uploaded_file($_FILES['photo']['tmp_name'][$i], "." . $imageLocName);

                            if (!$copy) {
                                $errorMessage .= "{$k}. Error while uploading image {$filename}. Try again.<br/>";
                                continue;
                            } else {

                                $this->_createThumbnail("." . $imageLocName, "." . $thumbLocName, $thumbHeight, $thumbHeight);

                                $photo = new App_Model_Photo(array(
                                    "galleryId" => $id,
                                    "title" => "",
                                    "photoName" => $photoname,
                                    "pathSmall" => $thumbLocName,
                                    "pathLarge" => $imageLocName,
                                    "mime" => $extension,
                                    "size" => $size,
                                    "width" => $width,
                                    "height" => $height
                                ));

                                if ($photo->validate()) {
                                    $photo->save();
                                    $message .= "{$k}. Photo " . $photoname . " uploaded<br/>";
                                } else {
                                    $errorMessage .= "{$k}. " . $photoname . " errors: " . join(", ", $photo->getErrors()) . "<br/>";
                                }
                            }
                        }
                    }
                }
            }

            if ($errorMessage == "") {
                $view->longFlashMessage($message);
                self::redirect("/admin/gallery/detail/" . $id);
            } else {
                $view->set("photoMessage", $errorMessage);
            }
        }
    }

    /**
     * 
     * @before _secured, _admin
     */
    public function photoAction() {
        $view = $this->getActionView();

        $message = "";

        if (RequestMethods::post("performPhotoAction")) {
            $photoIds = RequestMethods::post("photos");
            $action = RequestMethods::post("action");

            switch ($action) {
                case "delete":
                    $photos = App_Model_Photo::all(array(
                                "id IN ?" => $photoIds
                    ));

                    foreach ($photos as $photo) {
                        if (NULL !== $photo) {
                            if (unlink("." . $photo->pathSmall) && unlink("." . $photo->pathLarge)) {
                                $galleryId = $photo->galleryId;
                                $prepMessage = $photo->photoName . " has been deleted<br/>";
                                if ($photo->delete()) {
                                    $message .= $prepMessage;
                                } else {
                                    $message .= "An error occured while deleting " . $photo->photoName . "<br/>";
                                }
                            } else {
                                $message .= "An error occured while deleting files of " . $photo->photoName . "<br/>";
                            }
                        } else {
                            $message .= "Photo with id {$id} not found<br/>";
                        }
                    }

                    $view->longFlashMessage($message);
                    self::redirect("/admin/gallery/detail/" . $galleryId);

                    break;
                case "activate":
                    $photos = App_Model_Photo::all(array(
                                "id IN ?" => $photoIds
                    ));

                    foreach ($photos as $photo) {
                        if (NULL !== $photo) {
                            $photo->active = true;

                            if ($photo->validate()) {
                                $photo->save();
                                $message .= "Photo id " . $photo->photoName . " activated<br/>";
                            } else {
                                $message .= "Photo id {$id} - " . $photo->photoName . " errors: " . join(", ", $photo->getErrors()) . "<br/>";
                            }
                        } else {
                            $message .= "Photo with id {$id} not found<br/>";
                        }
                    }

                    $view->longFlashMessage($message);
                    self::redirect("/admin/gallery/detail/" . $photo->galleryId);

                    break;
                case "inactivate":
                    $photos = App_Model_Photo::all(array(
                                "id IN ?" => $photoIds
                    ));

                    foreach ($photos as $photo) {
                        if (NULL !== $photo) {
                            $photo->active = false;

                            if ($photo->validate()) {
                                $photo->save();
                                $message .= "Photo id " . $photo->photoName . " inactivated<br/>";
                            } else {
                                $message .= "Photo id {$id} - " . $photo->photoName . " errors: " . join(", ", $photo->getErrors()) . "<br/>";
                            }
                        } else {
                            $message .= "Photo with id {$id} not found<br/>";
                        }
                    }

                    $view->longFlashMessage($message);
                    self::redirect("/admin/gallery/detail/" . $photo->galleryId);

                    break;

                case "setavatar":
                    foreach ($photoIds as $id) {
                        $photo = App_Model_Photo::first(array(
                                    "id = ?" => $id
                        ));

                        if (NULL !== $photo) {
                            $gallery = App_Model_Gallery::first(array(
                                        "id = ?" => $photo->galleryId
                            ));

                            if (NULL !== $gallery) {
                                $gallery->avatar = $photo->getPathSmall();

                                if ($gallery->validate()) {
                                    $gallery->save();
                                    $message .= "Photo id {$photo->photoName} set as gallery avatar<br/>";
                                    break;
                                } else {
                                    $message .= "Gallery id {$photo->galleryId} - errors: " . join(", ", $gallery->getErrors()) . "<br/>";
                                    break;
                                }
                            } else {
                                $message .= "Gallery with id {$photo->galleryId} not found<br/>";
                                break;
                            }
                        } else {
                            $message .= "Photo with id {$id} not found<br/>";
                            break;
                        }
                    }

                    $view->longFlashMessage($message);
                    self::redirect("/admin/gallery/detail/" . $photo->galleryId);

                    break;
                default:
                    self::redirect("/admin/gallery/");
                    break;
            }
        }
    }

    /**
     * Response for ajax call
     * 
     * @before _secured, _admin
     * @param type $id
     * @return string
     */
//    public function loadPhotos($id) {
//        $this->willRenderLayoutView = false;
//        $this->willRenderActionView = false;
//        
//        $photos = App_PhotoModel::all(array(
//                    "galleryId = ?" => $id
//        ));
//
//        if (NULL !== $photos) {
//            $str = "";
//
//            foreach ($photos as $photo) {
//                $str .= "<li>";
//                if ($photo->active) {
//                    $str .= "<span class=\"gallery-photolist-photo-active\">";
//                } else {
//                    $str .= "<span class=\"gallery-photolist-photo-inactive\">";
//                }
//                $str .= "<img src=\"{$photo->pathSmall}\" alt=\"\" width=\"200\"/>";
//                $str .= "<input type=\"checkbox\" name=\"photos[]\" value=\"{$photo->id}\" /></span></li>";
//            }
//
//            echo $str;
//        } else {
//            echo "No photos loaded";
//        }
//    }

}