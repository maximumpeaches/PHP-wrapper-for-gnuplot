<?php

// Connect to database
include($_SERVER["DOCUMENT_ROOT"] . "/msdb/queries/db_connection.php");
$obj = new DBConnection();
$obj->open_connection();

/* Display the parameters for the respective function
*/
include("func_classes.php");
$func_obj = new $_GET['func_name']();
$func_obj->set_schema($_GET['schema']);
$func_obj->func_info();
$func_obj->beginning_params();
$func_obj->display();
$func_obj->display_end();
?>