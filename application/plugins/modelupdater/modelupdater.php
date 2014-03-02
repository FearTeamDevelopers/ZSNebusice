<?php

class ModelUpdater
{

    private $_pathToUpdates;
    private $_modelVersion;

    public function __construct($options)
    {
        if(!isset($options["dir"])){
            throw new \Exception("Path to model update files not specified");
        }
        
        $this->_pathToUpdates = $options["dir"];
    }

    /**
     * 
     */
    private function _checkUpdateScript()
    {

        $iterator = new \DirectoryIterator($this->_pathToUpdates);
        $arr = array();

        foreach ($iterator as $item) {
            if (!$item->isDot() && $item->isFile()) {
                $filepath = $this->_pathToUpdates . "/" . $item->getFilename();
                list ($model, $fromver, $tover) = explode("-", $item->getFilename());

                if (empty($model) || empty($fromver) || empty($tover)) {
                    //throw new Exception(sprintf("Model update script filename %s is not valid", $item->getFilename()));
                    continue;
                } else {
                    if ($this->_modelVersion[$model] == $fromver &&
                            $this->_modelVersion[$model] < $tover) {
                        $arr[] = array(
                            "path" => $filepath,
                            "model" => $model,
                            "fromver" => $fromver,
                            "tover" => $tover
                        );
                    } else {
                        continue;
                    }
                }
            }
        }
    }
    
    /**
     * 
     * @param type $fileVer
     */
    private function _compareModelVersion($fileVer){
        
    }

    /**
     * 
     */
    private function _loadModelVersion()
    {
        $filename = APP_PATH . "/application/configuration/modelver.ini";
        $this->_modelVersion = parse_ini_file($filename);
    }

    /**
     * 
     */
    public function run()
    {
        //$this->_loadModelVersion();
    }

}
