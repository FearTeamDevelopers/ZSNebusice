<?php

define("DEBUG", TRUE);
define("APP_PATH", __DIR__);

// core
require("./vendors/THCFrame/core/core.php");
THCFrame\Core\Core::initialize();

// plugins

$path = APP_PATH . "/application/plugins";
$iterator = new \DirectoryIterator($path);

foreach ($iterator as $item) {
    if (!$item->isDot() && $item->isDir()) {
        include($path . "/" . $item->getFilename() . "/initialize.php");
    }
}

//module loading

THCFrame\Core\Core::registerModule(new App_Module());
THCFrame\Core\Core::registerModule(new Admin_Module());
THCFrame\Core\Core::registerModule(new Cron_Module());

// load services and run dispatcher
THCFrame\Core\Core::run();
