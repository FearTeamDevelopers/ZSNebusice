<?php

include 'modelupdater.php';

$updater = new ModelUpdater(array(
    "dir" => APP_PATH."/application/modelupdate"
));

$updater->run();