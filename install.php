<?php

error_reporting(E_ALL);

require ("classes/kd_master.class.php");
require ('classes/kd_install.class.php');

if (!is_dir(CONFIG_PATH)) {
    mkdir(CONFIG_PATH, 0755);
}

session_start();
$INSTALL = new kd_install();
$INSTALL->interpreter();

include ('tpl/header.php');
if ($INSTALL::get_tpl() != "") {
    include ('tpl/' . $INSTALL::get_tpl() . '.php');
}
else {
    include ('tpl/install.form.php');
}

include ('tpl/footer.php');
