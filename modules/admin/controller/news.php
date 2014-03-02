<?php

use Admin\Libraries\Controller as Controller;
use THCFrame\Request\RequestMethods as RequestMethods;

/**
 * Description of NewsController
 *
 * @author Tomy
 */
class Admin_Controller_News extends Controller {

    /**
     * @before _secured, _admin
     */
    public function index() {
        $view = $this->getActionView();

        $news = App_Model_News::all();

        $view->set("news", $news);
    }

    /**
     * @before _secured, _admin
     */
    public function add() {
        $view = $this->getActionView();

        $allphotos = App_Model_Photo::all(
                        array("active = ?" => true), array("id", "pathSmall")
        );

        if (RequestMethods::post("addNews")) {
            $user = $this->getUser();

            $news = new App_Model_News(array(
                "author" => $user->getWholeName(),
                "title" => RequestMethods::post("title"),
                "body" => RequestMethods::post("body")
            ));

            if ($news->validate()) {
                $id = $news->save();

                $newsPhotosIds = RequestMethods::post("photos");

                if (isset($newsPhotosIds) && !empty($newsPhotosIds)) {
                    foreach ($newsPhotosIds as $photoId) {
                        $newsPhoto = new App_Model_NewsPhoto(array(
                            "newsId" => $id,
                            "photoId" => $photoId
                        ));

                        if ($newsPhoto->validate()) {
                            $newsPhoto->save();
                        }
                    }
                }

                $view->flashMessage("News has been successfully created");
                self::redirect("/admin/news/");
            } else {
                $view->set("errors", $news->getErrors());
            }
        }

        $view->set("allphotos", $allphotos);
    }

    /**
     * 
     * @before _secured, _admin
     * @param type $id
     */
    public function edit($id) {
        $view = $this->getActionView();

        $news = App_Model_News::first(array(
                    "id = ?" => $id
        ));

        $sql = "SELECT p.id, p.pathSmall, p.active, np.newsId FROM tb_photo p ";
        $sql .= "JOIN tb_newsphoto np ON np.photoId = p.id ";
        $sql .= "WHERE np.newsId=?";

        $photoModel = new App_Model_Photo();
        $id = $photoModel->getConnector()->escape($id);
        $selectedphotos = $photoModel->getConnector()->execute($sql, $id);

        $allphotos = App_Model_Photo::all(
                        array("active = ?" => true), array("id", "pathSmall")
        );

        if (NULL === $news) {
            $view->flashMessage("News not found");
            self::redirect("/admin/news/");
        }

        if (RequestMethods::post("editNews")) {
            $news->title = RequestMethods::post("title");
            $news->body = RequestMethods::post("body");
            $news->active = RequestMethods::post("active");

            if ($news->validate()) {
                $news->save();

                App_Model_NewsPhoto::deleteAll(array("newsId = ?" => $id));

                $newsPhotosIds = RequestMethods::post("photos");
                
                if (isset($newsPhotosIds) && !empty($newsPhotosIds)) {
                    $newsPhotosIdsUnq = array_unique($newsPhotosIds);

                    foreach ($newsPhotosIdsUnq as $photoId) {
                        $newsPhoto = new App_Model_NewsPhoto(array(
                            "newsId" => $id,
                            "photoId" => $photoId
                        ));

                        if ($newsPhoto->validate()) {
                            $newsPhoto->save();
                        }
                    }
                }

                $view->flashMessage("All changes were successfully saved");
                self::redirect("/admin/news/");
            }

            $view->set("errors", $news->getErrors());
        }

        $view->set("news", $news)
                ->set("allphotos", $allphotos)
                ->set("selectedphotos", $selectedphotos);
    }

    /**
     * 
     * @before _secured, _admin
     * @param type $id
     */
    public function delete($id) {
        $view = $this->getActionView();

        $news = App_Model_News::first(array(
                    "id = ?" => $id
        ));

        if (NULL === $news) {
            $view->flashMessage("News not found");
            self::redirect("/admin/news/");
        }

        if (RequestMethods::post("deleteNews")) {
            if (NULL !== $news) {
                if ($news->delete()) {
                    App_Model_NewsPhoto::deleteAll(array("newsId = ?" => $id));

                    $view->flashMessage("News has been deleted");
                    self::redirect("/admin/news/");
                } else {
                    $view->flashMessage("Unknown error eccured");
                    self::redirect("/admin/news/");
                }
            } else {
                $view->flashMessage("Unknown id provided");
                self::redirect("/admin/news/");
            }
        } elseif (RequestMethods::post("cancel")) {
            self::redirect("/admin/news/");
        }

        $view->set("news", $news);
    }

}