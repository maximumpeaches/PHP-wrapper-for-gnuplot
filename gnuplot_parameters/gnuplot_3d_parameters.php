<?php
// all the functions should use these files if they're generating 3d images with gnuplot
//****************** GNUPLOT PARAMETERS *************************************
// a value of 1, 2, 3 means the first, second or third column is plotted, respectively
// the value of 0 means plot nothing or don't plot that axis. a value of 4 means plot the frame number.
// it is a bit confusing but we use 0 here to mean plot nothing and 0 in the gnuplot_interface to mean plot the frame #, so we have to convert.

if (isset($_POST['xAxis']) && is_numeric($_POST['xAxis']))
    $xAxis = (int)$_POST['xAxis'];
else
    $xAxis = 1; // default is plot column 1 on the x axis
if (isset($_POST['yAxis']) && is_numeric($_POST['yAxis']))
    $yAxis = (int)$_POST['yAxis'];
else
    $yAxis = 2; // default is plot column 2 on the y axis
if (isset($_POST['zAxis']) && is_numeric($_POST['zAxis']))
    $zAxis = (int)$_POST['zAxis'];
else
    $zAxis = 3; // default is plot column 3 on the z axis

include("gnuplot_shared_parameters.php");

//*****************************************************************************

// plot nothing has a value of 0, so if x,y,z all have nonzero values, then we want to plot *something* on those axes
if ($xAxis && $yAxis && $zAxis){
	if (!$animate)
	{
		$gnuplot_object = new threedpng();
	}
	else{
		if ($big)
			$gnuplot_object = new large_threedgif();
		else
			$gnuplot_object = new small_threedgif();
	}
}
else if (!$xAxis && !$yAxis && !$zAxis){ // else if no axes are to be plotted
	$gnuplot_object = new donotplot();
}
else if (!$zAxis) { // else the user has selected plot one axis and none for the other two, or plot two axes and none for the other one. in either case we'd like to show a 2d png or gif.
	if (!$animate){ // create a 2d png
		$gnuplot_object = new twodpng(); 
	}
	else { // create a 2d gif
		if ($big)
			$gnuplot_object = new large_twodgif();
		else
			$gnuplot_object = new small_twodgif();
	}
}

// each of the functions need to specify what title they'd like for the gnuplot image
// where the column # is the column # of the data file. so column # 1 is the first column in the data file, etc.
if ($xAxis || $yAxis || $zAxis){ // if user selected None i.e. 0 for all the axes, then we created a donotplot gnuplot object, and don't want or need to mess around with setting axes values.
	switch ($xAxis){
		case 0:
		case 4:
			$gnuplot_object->set_x_label("Frame #");
			$gnuplot_object->set_x_axis(0); // in gnuplot, 0 for a column number represents the row #. see 'help using' in gnuplot for details.
			break;
		case 1:
			$gnuplot_object->set_x_label($xDimensionName);
			$gnuplot_object->set_x_axis($xAxis);
			break;
		case 2:
			$gnuplot_object->set_x_label($yDimensionName);
			$gnuplot_object->set_x_axis($xAxis);
			break;
		case 3:
			$gnuplot_object->set_x_label($zDimensionName);
			$gnuplot_object->set_x_axis($xAxis);
			break;
		default:
			$gnuplot_object->set_x_label($xDimensionName);
			$gnuplot_object->set_x_axis(1);
			break;
	}


	switch ($yAxis){
		case 0:
		case 4:
			$gnuplot_object->set_y_label("Frame #");
			$gnuplot_object->set_y_axis(0); // in gnuplot, 0 for a column number represents the row #. see 'help using' in gnuplot for details.
			break;
		case 1:
			$gnuplot_object->set_y_label($xDimensionName);
			$gnuplot_object->set_y_axis($yAxis);
			break;
		case 2:
			$gnuplot_object->set_y_label($yDimensionName);
			$gnuplot_object->set_y_axis($yAxis);
			break;
		case 3:
			$gnuplot_object->set_y_label($zDimensionName);
			$gnuplot_object->set_y_axis($yAxis);
			break;
		default:
			$gnuplot_object->set_y_label($yDimensionName);
			$gnuplot_object->set_y_axis(2);
			break;
	}


	switch ($zAxis){
		case 0:
		case 4:
			$gnuplot_object->set_z_label("Frame #");
			$gnuplot_object->set_z_axis(0); // in gnuplot, 0 for a column number represents the row #. see 'help using' in gnuplot for details.
			break;
		case 1:
			$gnuplot_object->set_z_label($xDimensionName);
			$gnuplot_object->set_z_axis($zAxis);
			break;
		case 2:
			$gnuplot_object->set_z_label($yDimensionName);
			$gnuplot_object->set_z_axis($zAxis);
			break;
		case 3:
			$gnuplot_object->set_z_label($zDimensionName);
			$gnuplot_object->set_z_axis($zAxis);
			break;
		default:
			$gnuplot_object->set_z_label($zDimensionName);
			$gnuplot_object->set_z_axis(3);
			break;
	}
}


?>