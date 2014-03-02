<?php

use App\Libraries\Controller as Controller;
use THCFrame\Request\RequestMethods;
use THCFrame\Registry\Registry;

/**
 * Description of IndexController
 *
 * @author Tomy
 */
class App_Controller_Index extends Controller
{

    /**
     * @before _secured
     */
    public function index()
    {

        //vyber ucitelu
        $view = $this->getActionView();

        $ucitele = App_Model_User::all(
                        array('role = ?' => 'role_ucitel')
        );

        if (RequestMethods::post('submitStepOne')) {
            $uciteleAr = RequestMethods::post('checkboxUcitele');

            if (empty($uciteleAr)) {
                $view->set('errors', array('ucitele', 'Neni vybran zadny ucitel'));
            } else {
                $session = Registry::get("session");
                $session->set("uciteleAr", serialize($uciteleAr));

                self::redirect('/steptwo');
            }
        }

        $view->set('ucitele', $ucitele);
    }

    /**
     * @before _secured, _rodic
     */
    public function steptwo()
    {

        $view = $this->getActionView();

        //pole id ucitelu
        $session = Registry::get("session");
        $uciteleAr = unserialize($session->get("uciteleAr"));

        $casy = App_Model_Cas::all();
        $selectedProf = App_Model_User::all(
                        array('id_user IN (?)' => $uciteleAr,
                    'role = ?' => 'role_ucitel'), array('id_user', 'jmeno', 'prijmeni')
        );

        if (RequestMethods::post("submitStepTwo")) {
            $userId = $this->getUser()->getId();
            $usedCasy = array();
            $errors = array();

            $database = Registry::get("database");
            $database->beginTransaction();

            foreach ($uciteleAr as $id) {
                $selectBox = RequestMethods::post("selectbox_" . $id);

                list($iducitele, $idcasu) = explode("-", $selectBox);

                //kontrola zaznamu ve vazebni tabulce
                $obsazeneCasy = App_Model_Konzultace::all(
                                array(
                                    "id_ucitel = ?" => $iducitele,
                                    "id_cas = ?" => $idcasu
                                )
                );

                //pokud nejaky zaznam existuje vytvori chybu
                if (FALSE !== $obsazeneCasy) {
                    $errors['selectbox_' . $id] = "Tento cas je jiz zabran";
                    continue;
                }

                //kontrola na duplicitni casy ve formulari
                if (array_key_exists($idcasu, $usedCasy)) {
                    $errors['selectbox_' . $id] = "Tento cas je jiz vybran u jineho ucitele";
                } else {
                    $usedCasy[] = $idcasu;
                }

                //vytvoreni novyho zaznamu ve vazebni tabulce
                $konzultace = new App_Model_Konzultace(array(
                    "id_cas" => $idcasu,
                    "id_ucitel" => $iducitele,
                    "id_rodic" => $userId
                ));

                if ($konzultace->validate()) {
                    $newIds[] = $konzultace->save();
                } else {
                    $errors['selectbox_' . $id] = "Chyba pri ukladani zaznamu";
                }
            }

            if (empty($errors)) {
                $database->commitTransaction();
                $session->set("newIds", serialize($newIds));
                $view->flashMessage("Casy navstev ulozeny");
                self::redirect("/stepthree");
            } else {
                $view->set("errors", $errors);
                $database->rollbackTransaction();
            }
        }

        $view->set("casy", $casy)
                ->set("ucitele", $selectedProf);
    }

    /**
     * @before _secured, _rodic
     */
    public function stepthree()
    {
        $view = $this->getActionView();
        $userId = 7;
        //$userId = $this->getUser()->getId();
        $query = App_Model_Konzultace::getQuery(array("tb_konzultace.*"));

        $query->join("tb_user", "tb_konzultace.id_ucitel = uc.id", "uc", array("uc.firstname", "uc.lastname"))
                ->join("tb_cas", "tb_konzultace.id_cas = c.id", "c", array("c.cas_start", "c.cas_end"))
                ->where("tb_konzultace.id_rodic = ?", $userId);

        $konzultace = App_Model_Konzultace::initialize($query);
        //print("<pre>".print_r($konzultace,true)."</pre>");die();
        $view->set("konzultace", $konzultace);

        if (RequestMethods::post("submitStepThree") == "Potvrdit") {
            $view->flashMessage("Konzultace jsou nyní potvrzeny");
            self::redirect("/stepfour");
        } elseif (RequestMethods::post("submitStepTree") == "Zrusit") {

            $session = Registry::get("session");
            $konzIds = unserialize($session->get("newIds"));

            $errors = array();
            $database = Registry::get("database");
            $database->beginTransaction();

            foreach ($konzIds as $id) {
                $konzultace = App_Model_Konzultace::first(
                                array("id = ?" => $id)
                );

                if (!$konzultace->delete()) {
                    $errors[] = "Chyba pri mazani zaznamu {$id}";
                }
            }

            if (empty($errors)) {
                $database->commitTransaction();
                self::redirect("/");
            } else {
                $view->flashMessage("Nastala neocekavana chyba");
                $database->rollbackTransaction();
            }
        }
    }

    /**
     * @before _secured, _rodic
     */
    public function stepfour()
    {
        
    }

}
