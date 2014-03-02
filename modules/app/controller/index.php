<?php

use App\Libraries\Controller as Controller;
use THCFrame\Request\RequestMethods;
use THCFrame\Core\Core;

/**
 * Description of IndexController
 *
 * @author Tomy
 */
class App_Controller_Index extends Controller
{

    private $_uciteleIdAr = array();

    /**
     * @before _secured
     */
    public function index()
    {

        //vyber ucitelu
        $view = $this->getActionView();

        $ucitele = App_Model_Ucitel::all();

        $view->set('ucitele', $ucitele);
    }

    /**
     * 
     */
    public function steptwo()
    {
        $this->_willRenderLayoutView = false;

        $view = $this->getActionView();

        //pole id ucitelu
        $this->_uciteleIdAr = $uciteleAr = RequestMethods::post('checkboxUcitele');

        $casy = App_Model_Cas::all();
        $selectedProf = App_Model_User::all(
                        array('id_user IN (?)' => $uciteleAr,
                    'role = ?' => 'role_ucitel'), array('id_user', 'jmeno', 'prijmeni')
        );

        $view->set("casy", $casy)
                ->set("ucitele", $selectedProf);
    }

    /**
     * 
     */
    public function stepthree()
    {
        $this->_willRenderLayoutView = false;

        if (empty($this->_uciteleIdAr)) {
            throw new Exception("nejsou vybrani ucitele");
        }

        $view = $this->getActionView();

        //id prihlaseneho rodice
        $userId = $this->getUser()->getId();
        $usedCasy = array();
        $errors = array();

        $database = \THCFrame\Registry\Registry::get("database");
        $database->beginTransaction();

        foreach ($this->_uciteleIdAr as $id) {
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
                $konzultace->save();
            } else {
                $errors['selectbox_' . $id] = "Chyba pri ukladani zaznamu";
            }
        }

        if (empty($errors)) {
            $database->commitTransaction();
            $view->flashMessage("Casy navstev ulozeny");
            self::redirect("/stepfour");
        } else {
            $view->set("errors", $errors);
            $database->rollbackTransaction();
        }
    }

    /**
     * 
     */
    public function stepfour()
    {
        /*
         * select * from tb_konzultace
         * join tb_user uc on  tb_konzultace.id_ucitel = uc.id_user
         * join tb_user ur on tb_konzultace.id_rodic = ur.id_user
         * where tb_konzultace.id_rodic = id
         */
        $this->_willRenderLayoutView = false;

        $view = $this->getActionView();

        $userId = $this->getUser()->getId();
        $query = App_Model_Konzultace::getQuery(array("tb_konzultace.*"));

        $query->join("tb_user", "tb_konzultace.id_ucitel = uc.id_user", "uc", array("uc.*"))
                ->join("tb_user", "tb_konzultace.id_rodic = ur.id_user", "ur", array("ur.*"))
                ->join("tb_cas", "tb_konzultace.id_cas = c.id_cas", "c", array("c.*"))
                ->where("tb_konzultace.id_rodic = ?", $userId);

        $konzultace = App_Model_Konzultace::initialize($query);

        $view->set("konzultace", $konzultace);
    }

}
