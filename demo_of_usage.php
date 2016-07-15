<!-- 
A large portion of this file was deleted.
I left what I thought might help illustrate the functioning of the PHP classes modeling the gnuplot functionality
which reside in gnuplot_interface.php
-->

<?php

// include the classes for the gnuplot functionality
include("gnuplot_interface.php");

// these names are used in gnuplot_3d_parameters
$xDimensionName = "Column 1";
$yDimensionName = "Column 2";
$zDimensionName = "Column 3";

include("./gnuplot_parameters/gnuplot_shared_parameters.php");
include("./gnuplot_parameters/gnuplot_3d_parameters.php");

// set desired title for the image
$gnuplot_object->set_title("Center of Mass");
$gnuplot_object->set_key_name("Frame #");

while ($currentFrame <= $lastFrame) {
    // create an SQL query
    $queryRun = " SELECT $query_id" . "('" . $tableName . "' , $f," . ($currentFrame - 1) . ",$frameSkip,$molIndexmin,$molIndexmax,$atomIndexmin,$atomIndexmax,$minX,$minY,$minZ,$maxX,$maxY,$maxZ, '" . implode("_", $molName) . "',$whole,$whole_pcb, '{";
    // execute the SQL query and store the results in a variable
    $result = pg_query($queryRun);

    while ($row = pg_fetch_row($result)) {
        // iterate through the query results and add data points to the gnuplot data file
        $gnuplot_object->record_data($matches[0][1], $matches[0][2], $matches[0][3]);
}

// call gnuplot and save the visualization file to disk
$output_file_location = $gnuplot_object->export();

?>