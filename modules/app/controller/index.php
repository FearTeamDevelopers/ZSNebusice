<?php

use App\Libraries\Controller as Controller;
use THCFrame\Request\RequestMethods;
use THCFrame\Registry\Registry;

/**
 * Description of IndexController
 *
 * @author Tomy
 */
class App_Controller_Index extends Controller {

    /**
     * @before _secured,   
     */
    public function index() {
        $security = Registry::get("security");
        $potvrzeno = $security->getUser()->potvrzeno;
        print_r($potvrzeno);
        if ($potvrzeno == 0) {
            //vyber ucitelu
            $view = $this->getActionView();
            $userId = $userId = $this->getUser()->getId();



            $ucitele = App_Model_User::all(
                            array('role = ?' => 'role_ucitel'
                            )
            );

            if (RequestMethods::post('submitStepOne')) {
                $uciteleAr = RequestMethods::post('checkboxUcitele');

                if (empty($uciteleAr)) {
                    $errors['ucitError'] = array('Neni vybran zadny ucitel');
                } else {
                    $session = Registry::get("session");
                    $session->set("uciteleAr", serialize($uciteleAr));
                    $bla = App_Model_User::first(
                                    array(
                                        'id=?' => $userId
                                    )
                    );
                    $bla->potvrzeno = 1;
                    $bla->save();
                    self::redirect('/steptwo');
                }
            }

            $view->set('errors', $errors)
                    ->set('ucitele', $ucitele);
        } elseif ($potvrzeno == 1) {
            self::redirect("/steptwo");
        } elseif ($potvrzeno == 2) {
            self::redirect("/stepthree");
        } else {
            self::redirect("/stepfour");
        }
    }

    /**
     * @before _secured, _rodic                                                                                               
     */
    public function steptwo() {
        $security = Registry::get("security");
        $potvrzeno = $security->getUser()->potvrzeno;
        print_r($potvrzeno);
        if ($potvrzeno == 1) {
            $view = $this->getActionView();
            $userId = $userId = $this->getUser()->getId();
            $bla = App_Model_User::first(
                            array(
                                'id=?' => $userId
                            )
            );
            //pole id ucitelu
            $session = Registry::get("session");
            $uciteleAr = unserialize($session->get("uciteleAr"));

            $casy = App_Model_Cas::all();
            $selectedProf = App_Model_User::all(
                            array('id IN ?' => array_values($uciteleAr),
                        'role = ?' => 'role_ucitel'), array('id', 'firstname', 'lastname')
            );

            if (RequestMethods::post("submitStepTwo") == 'Zpet') {
                $bla = App_Model_User::first(
                                array(
                                    'id=?' => $userId
                                )
                );
                $bla->potvrzeno = 0;
                $bla->save();
                self::redirect("/");
            }
            if (RequestMethods::post("submitStepTwo") == 'Pokracovat') {
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
                    if (!empty($obsazeneCasy)) {
                        $errors['selectbox_' . $id] = array("Tento cas je jiz zabran");
                        continue;
                    }

                    //kontrola na duplicitni casy ve formulari
                    if (in_array($idcasu, $usedCasy)) {
                        $errors['selectbox_' . $id] = array("Tento cas je jiz vybran u jineho ucitele");
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
                        $errors['selectbox_' . $id] = array("Chyba pri ukladani zaznamu");
                    }
                }

                if (empty($errors)) {
                    $database->commitTransaction();
                    $session->set("newIds", serialize($newIds));
                    $view->flashMessage("Casy navstev ulozeny");
                    $bla->potvrzeno = 2;
                    $bla->save();
                    self::redirect("/stepthree");
                } else {
                    $view->set("errors", $errors);
                    $database->rollbackTransaction();
                }
            }

            $view->set("casy", $casy)
                    ->set("ucitele", $selectedProf);
        } elseif ($potvrzeno == 0) {
            self::redirect("/");
        } elseif ($potvrzeno == 2) {
            self::redirect("/stepthree");
        } else {
            self::reddirect("/stepfour");
        }
    }

    /**
     * @before _secured, _rodic 
     */
    public function stepthree() {
        $security = Registry::get("security");
        $potvrzeno = $security->getUser()->potvrzeno;
        if ($potvrzeno == 2) {
            $view = $this->getActionView();
            $userId = $this->getUser()->getId();

            $query = App_Model_Konzultace::getQuery(array("tb_konzultace.*"));

            $query->join("tb_user", "tb_konzultace.id_ucitel = uc.id", "uc", array("uc.firstname", "uc.lastname"))
                    ->join("tb_cas", "tb_konzultace.id_cas = c.id", "c", array("c.cas_start", "c.cas_end"))
                    ->where("tb_konzultace.id_rodic = ?", $userId);
            $bla = App_Model_User::first(
                            array(
                                'id=?' => $userId
                            )
            );
            $konzultace = App_Model_Konzultace::initialize($query);
            $view->set("konzultace", $konzultace);
            if (RequestMethods::post("submitStepThree") == "Potvrdit") {
                $view->flashMessage("Konzultace jsou nyní potvrzeny");
                $bla->potvrzeno = 3;
                $bla->save();
                self::redirect("/stepfour");
            } elseif (RequestMethods::post("submitStepThree") == "Zrusit") {

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
        } elseif ($potvrzeno == 0) {
            self::redirect("/");
        } elseif ($potvrzeno == 1) {
            self::redirect("/steptwo");
        } else {
            self::reddirect("/stepfour");
        }
    }

    /**
     * @before _secured, _rodic  
     */
    public function stepfour() {
        $security = Registry::get("security");
        $potvrzeno = $security->getUser()->potvrzeno;
        if ($potvrzeno == 0) {
            self::redirect("/");
        } elseif ($potvrzeno == 1) {
            self::redirect("/steptwo");
        } elseif ($potvrzeno == 2) {
            self::redirect("/stepthree");
        } else {
            $view = $this->getActionView();
            $userId = $this->getUser()->getId();
            $bla = App_Model_User::first(
                            array(
                                'id=?' => $userId
                            )
            );
            if (RequestMethods::post("submitStepFour") == "Zrusit") {
                //potvrzeno=0
                $view->flashMessage("Potvrzení zrušeno");
                $bla->potvrzeno = 0;
                $bla->save();

                //smazani vsech zaznamu s prihlasenym uzivatelem z konzultaci
                $database = Registry::get("database");
                $database->beginTransaction();

                $konzIds = App_Model_Konzultace::all(
                                array('id_rodic = ?' => $userId)
                );

                foreach ($konzIds as $id) {
                    $konzultace = App_Model_Konzultace::first(
                                    array("id_rodic = ?" => $userId)
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
                self::redirect("/");
            }
        }
    }

}
