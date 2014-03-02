<?php

use Admin\Libraries\Controller as Controller;
use THCFrame\Request\RequestMethods as RequestMethods;

/**
 * Description of TrainingController
 *
 * @author Tomy
 */
class Admin_Controller_Training extends Controller {

    /**
     * @param type $id
     */
    private function _getAttendace($id) {
        $attendanceModel = new App_Model_Attendance();
        $connector = $attendanceModel->getConnector();
        $id = $connector->escape($id);

        $sql = "SELECT u.firstname, u.lastname, ta.* ";
        $sql .= "FROM tb_attendance ta ";
        $sql .= "JOIN tb_user u ON ta.userId = u.id ";
        $sql .= "WHERE ta.active = true AND trainingId = '{$id}'";

        $result = $connector->execute($sql);

        $rows = array();

        for ($i = 0; $i < $result->num_rows; $i++) {
            $rows[] = $result->fetch_array(MYSQLI_ASSOC);
        }

        return $rows;
    }

    /**
     * 
     * @return type
     */
    private function _checkAttendance() {
        $attendanceModel = new App_Model_Attendance();
        $connector = $attendanceModel->getConnector();

        $sql = "SELECT u.firstname, u.lastname, count(ta.id) as cnt
                FROM tb_attendance ta
                JOIN tb_user u ON ta.userId = u.id
                JOIN tb_training tr ON ta.trainingId = tr.id
                WHERE ta.status = 1 AND tr.date <= now()
                GROUP BY ta.userId";

        $result = $connector->execute($sql);

        $rows = array();

        for ($i = 0; $i < $result->num_rows; $i++) {
            $rows[] = $result->fetch_array(MYSQLI_ASSOC);
        }

        return $rows;
    }

    /**
     * @before _secured, _admin
     */
    public function index() {
        $view = $this->getActionView();

        $trainings = App_TrainingModel::all();

        $view->set("trainings", $trainings);
    }

    /**
     * @before _secured, _admin
     */
    public function add() {
        $view = $this->getActionView();

        if (RequestMethods::post("addTraining")) {

            $training = new App_TrainingModel(array(
                "title" => RequestMethods::post("title"),
                "date" => date("Y-m-d", strtotime(RequestMethods::post("date")))
            ));

            if ($training->validate()) {
                $training->save();

                $view->flashMessage("Training has been successfully created");
                self::redirect("/admin/training/");
            } else {
                $view->set("errors", $training->getErrors());
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

        $training = App_TrainingModel::first(array(
                    "id = ?" => $id
        ));
        
        if (NULL === $training) {
            $view->flashMessage("Training not found");
            self::redirect("/admin/training/");
        }

        if (RequestMethods::post("editTraining")) {
            $training->title = RequestMethods::post("title");
            $training->date = date("Y-m-d", strtotime(RequestMethods::post("date")));
            $training->active = RequestMethods::post("active");

            if ($training->validate()) {
                $training->save();

                $view->flashMessage("All changes were successfully saved");
                self::redirect("/admin/training/");
            }

            $view->set("errors", $training->getErrors());
        }

        $view->set("training", $training);
    }

    /**
     * 
     * @before _secured, _admin
     * @param type $id
     */
    public function delete($id) {
        $view = $this->getActionView();

        $training = App_TrainingModel::first(array(
                    "id = ?" => $id
        ));

        if (NULL === $training) {
            $view->flashMessage("Training not found");
            self::redirect("/admin/training/");
        }
        
        if (RequestMethods::post("deleteTraining")) {
            if (NULL !== $training) {
                if ($training->delete()) {
                    $view->flashMessage("Training has been deleted");
                    self::redirect("/admin/training/");
                } else {
                    $view->flashMessage("Unknown error eccured");
                    self::redirect("/admin/training/");
                }
            } else {
                $view->flashMessage("Unknown id provided");
                self::redirect("/admin/training/");
            }
        } elseif (RequestMethods::post("cancel")) {
            self::redirect("/admin/training/");
        }

        $view->set("training", $training);
    }

    /**
     * @before _secured, _admin
     */
    public function attendance() {
        $view = $this->getActionView();

        $trainingCount = App_TrainingModel::count(array(
            "date <= ?" => date("Y-m-d")
        ));

        $attd = $this->_checkAttendance();
        
        foreach ($attd as $key => $value) {
            $attd[$key]["proc"] = round(($value["cnt"] / $trainingCount) * 100, 2);
        }

        $view->set("attd", $attd)
                ->set("trcount", $trainingCount);
    }

    /**
     * 
     * @before _secured, _admin
     * @param type $id
     */
    public function showAttendance($id) {
        $view = $this->getActionView();

        $training = App_TrainingModel::first(array(
                    "id = ?" => $id
        ));
        
        if (NULL === $training) {
            $view->flashMessage("Training not found");
            self::redirect("/admin/training/");
        }

        $training->attendance = $this->_getAttendace($id);

        $view->set("training", $training);
    }

}