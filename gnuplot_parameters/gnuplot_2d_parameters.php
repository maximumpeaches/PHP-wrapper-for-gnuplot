<?php
// all the functions should use these files if they're generating 2d images with gnuplot
//****************** GNUPLOT PARAMETERS *************************************
// a value of 1, 2, 3 means the first, second or third column is plotted, respectively
// the value of -1 means plot nothing or don't plot that axis. a value of 0 means plot the frame number.
if (isset($_POST['xAxis']) && is_numeric($_POST['xAxis']))
    $xAxis = (int)$_POST['xAxis'];
else
    $xAxis = 4; // default is plot frame number on x axis
if (isset($_POST['yAxis']) && is_numeric($_POST['yAxis']))
    $yAxis = (int)$_POST['yAxis'];
else
    $yAxis = 1; // default is plot column 1 on the y axis

include("gnuplot_shared_parameters.php");


//*****************************************************************************

if (!$xAxis && !$yAxis){ // if both are None then plot nothing
    $gnuplot_object = new donotplot();
}
else{ 
    if (!$animate)
        $gnuplot_object = new twodpng();
    else{
        if ($big)
            $gnuplot_object = new large_twodgif();
        else
            $gnuplot_object = new small_twodgif();
    }
}

// each of the functions need to specify what title they'd like for the gnuplot image
// where the column # is the column # of the data file. so column # 1 is the first column in the data file, etc.
if ($xAxis || $yAxis){ // if user selected None i.e. 0 for all the axes, then we created a donotplot gnuplot object, and don't want to mess around with setting axes values.
    switch ($xAxis){
        case 0:
        case 4:
            $gnuplot_object->set_x_label($xDimensionName);
            $gnuplot_object->set_x_axis(0); // in gnuplot, 0 for a column number represents the row #. see 'help using' in gnuplot for details.
            break;
        case 1:
            $gnuplot_object->set_x_label($yDimensionName);
            $gnuplot_object->set_x_axis($xAxis);
            break;
        default:
            $gnuplot_object->set_x_label($xDimensionName);
            $gnuplot_object->set_x_axis(0);
            break;
    }
    switch ($yAxis){
        case 0:
        case 4:
            $gnuplot_object->set_y_label($xDimensionName);
            $gnuplot_object->set_y_axis(0); // in gnuplot, 0 for a column number represents the row #. see 'help using' in gnuplot for details.
            break;
        case 1:
            $gnuplot_object->set_y_label($yDimensionName);
            $gnuplot_object->set_y_axis($yAxis);
            break;
        default:
            $gnuplot_object->set_y_label($yDimensionName);
            $gnuplot_object->set_y_axis(1);
            break;
    }
}


?>