<?php

use Admin\Libraries\Controller as Controller;
use THCFrame\Request\RequestMethods as RequestMethods;

/**
 * Description of MatchController
 *
 * @author Tomy
 */
class Admin_Controller_Match extends Controller {

    /**
     * @before _secured, _admin
     */
    public function index() {
        $view = $this->getActionView();

        $matchesA = App_Model_Match::all(array("team = ?" => "a"));
        $matchesB = App_Model_Match::all(array("team = ?" => "b"));

        $view->set("matchesA", $matchesA)
                ->set("matchesB", $matchesB);
    }

    /**
     * @before _secured, _admin
     */
    public function add() {
        $view = $this->getActionView();

        if (RequestMethods::post("addMatch")) {

            $match = new App_Model_Match(array(
                "home" => RequestMethods::post("home"),
                "host" => RequestMethods::post("host"),
                "date" => date("Y-m-d", strtotime(RequestMethods::post("date"))),
                "hall" => RequestMethods::post("hall"),
                "scoreHome" => RequestMethods::post("scorehome", "-1"),
                "scoreHost" => RequestMethods::post("scorehost", "-1"),
                "season" => RequestMethods::post("season"),
                "team" => RequestMethods::post("team")
            ));

            if ($match->validate()) {
                $match->save();

                $view->flashMessage("Match has been successfully created");
                self::redirect("/admin/match/");
            } else {
                print_r($match->getErrors());exit();
                $view->set("errors", $match->getErrors());
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

        $match = App_Model_Match::first(array(
                    "id = ?" => $id
        ));
        
        if (NULL === $match) {
            $view->flashMessage("Match not found");
            self::redirect("/admin/match/");
        }

        if (RequestMethods::post("editMatch")) {
            $match->home = RequestMethods::post("home");
            $match->host = RequestMethods::post("host");
            $match->date = date("Y-m-d", strtotime(RequestMethods::post("date")));
            $match->hall = RequestMethods::post("hall");
            $match->scoreHome = RequestMethods::post("scorehome", "-1");
            $match->scoreHost = RequestMethods::post("scorehost", "-1");
            $match->season = RequestMethods::post("season");
            $match->report = RequestMethods::post("report", "");
            $match->team = RequestMethods::post("team");
            $match->active = RequestMethods::post("active");

            if ($match->validate()) {
                $match->save();

                $view->flashMessage("All changes were successfully saved");
                self::redirect("/admin/match/");
            }

            $view->set("errors", $match->getErrors());
        }

        $view->set("match", $match);
    }

    /**
     * 
     * @before _secured, _admin
     * @param type $id
     */
    public function delete($id) {
        $view = $this->getActionView();

        $match = App_Model_Match::first(array(
                    "id = ?" => $id
        ));

        if (NULL === $match) {
            $view->flashMessage("Match not found");
            self::redirect("/admin/match/");
        }
        
        if (RequestMethods::post("deleteMatch")) {
            if (NULL !== $match) {
                if ($match->delete()) {
                    $view->flashMessage("Match has been deleted");
                    self::redirect("/admin/match/");
                } else {
                    $view->flashMessage("Unknown error eccured");
                    self::redirect("/admin/match/");
                }
            } else {
                $view->flashMessage("Unknown id provided");
                self::redirect("/admin/match/");
            }
        } elseif (RequestMethods::post("cancel")) {
            self::redirect("/admin/match/");
        }

        $view->set("match", $match);
    }

    /**
     * 
     * @before _secured, _admin
     * @param number $id    matchid
     */
    public function showMessages($id) {
        $view = $this->getActionView();

        $matchMessages = App_MatchChatModel::all(
                        array(
                    "matchId = ?" => $id,
                    "active = ?" => true,
                    "reply = ?" => 0
                        ), array("*"), "created", "asc"
        );

        $view->set("matchMessages", $matchMessages);
    }

    /**
     * @before _secured, _admin
     * @param number $id    message id
     */
    public function deleteMessage($id) {
        $view = $this->getActionView();

        $matchMessage = App_MatchChatModel::all(
                        array(
                    "id = ?" => $id
                        ), array("id", "reply"));

        if (NULL !== $matchMessage) {
            if ($matchMessage->delete()) {
                if ($matchMessage->reply == 0) {
                    App_MatchChatModel::deleteAll(array("reply = ?" => $id));
                }

                $view->flashMessage("Match has been deleted");
                self::redirect("/admin/match/");
            } else {
                $view->flashMessage("Unknown error eccured");
                self::redirect("/admin/match/");
            }
        } else {
            $view->flashMessage("Unknown id provided");
            self::redirect("/admin/match/");
        }
    }

}