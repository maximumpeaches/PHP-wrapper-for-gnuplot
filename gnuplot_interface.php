<?php
/***********************************************************************************
 * This file is composed of
 * 1) The base class for all gnuplot functionality
 * 2) twod - base class for twodpng and twodgif
 * 3) twodpng - class for creating 2d PNG files
 * 4) twodgif - the class for plotting gif (animated) files in two dimensions
 * 5) threed - base class for threedpng and threedgif
 * 6) threedpng - class for creating 3d PNG files
 * 7) threedgif - the class for plotting gif (animated) files in three dimensions
 ***************************************************************************************/

/***************************************************************************************
Some notes:
// to view errors dealing with the gnuplot script itself
// first find the script output by the output_gif() function below
// the script should be called something like gnuplot_script2897234897.glp
// then run the script from gnuplot on the command line with a command like, "gnuplot gnuplot_script2897234897.glp"
// doing so yields additional error messaging that can be useful in debugging

// it is entirely possible to use popen and feed the commands, such as set title, set output, set terminal, etc, as single commands via popen
// instead of writing a script to file and then creating a gif via shell_exec("gnuplot $gnuplot_script")
// the reason I decided to output the script to file and call shell_exec is because 
// gnuplot prints to stdout a revealing output if there is some error within the script
// by the way if at some point it becomes desirable to skip outputting the script to disk and call gnuplot you can follow the example
// of A PHP Interface to GNU Plot by Liu Yi (google it for more info) where he uses popen
// or you can use something like the following command from within a php script
// shell_exec("gnuplot -e \"outputname='$gnuplot_gif_file'; point_data='$gnuplot_point_data_file'; line_data='$gnuplot_line_data_file';
//        xmin='$centermass_x_min'; xmax='$centermass_x_max'; ymin='$centermass_y_min'; ymax='$centermass_y_max'; zmin='$centermass_z_min'; zmax='$centermass_z_max';
//        xticlength='$xticlength'; yticlength='$yticlength'; zticlength='$zticlength';\"");
*****************************************************************************************/


class gnuplot{

	var $gnuplot_id;
	var $num_of_tics;
	var $x_label, $y_label, $z_label;
	var $x_axis, $y_axis, $z_axis;
	var $gnuplot_script;
	var $gnuplot_script_file_location, $output_file_location;
	// all images (except donotplot, but who cares about that one) will need a line_data file
	// not all images need the point_data file, so we wait to instantiate that until we get to the gif class
	var $line_data_location, $line_data;
	var $title;
	var $has_entry;
	var $sample_max, $sample_num;
	var $one_max, $one_min, $two_max, $two_min, $three_max, $three_min;
	// the label used in the key within the gnuplot image (only animated images have a key at this point in time)
	var $key_name;

	function __construct(){
		// this flag will tell us if our object has at least one data point, and will be useful in reducing the number of checks when calculating $x_max, $y_min, etc.
		$this->has_entry = false;

		// When we write the files to disk, we want a way to distinguish them from other files that are being written to disk at the same time
		// so that we don't write over the gnuplot file created by another user before that user's GIF file is made and loaded
		// so we'll generate a random number and append it to the file names
		if (isset($_POST['random_num']) && is_numeric($_POST['random_num']))
			$this->gnuplot_id = $_POST['random_num'];
		else
			$this->gnuplot_id = "99"; // useful for debugging

		$this->gnuplot_script_file_location = $_SERVER['DOCUMENT_ROOT'] . "/msdb/queries/gnuplot_tmp_files/gnuplot_script" . $this->gnuplot_id . ".glp";
		// file location of line data text file
		$this->line_data_location = $_SERVER['DOCUMENT_ROOT'] . "/msdb/queries/gnuplot_tmp_files/line" . $this->gnuplot_id . ".data";
		// location on disk of the image file, whether it be png or gif
		// we don't have the .png or .gif extension b/c if we did the code to set the src of the image file would have to be complex enough to check whether it's a png or gif
		// this way the same code handles both cases
		$this->output_file_location = $_SERVER['DOCUMENT_ROOT'] . "/msdb/queries/gnuplot_tmp_files/gnuplot_output" . $this->gnuplot_id;

		// we want to keep count of how many samples of the data we've taken so far, and use this in our add_data function
		$this->sample_count = 0;
		// $sample_max should be set whenever a gnuplot object is created, but, if the user forgets we want to have a default value
		$this->sample_max = 10000000000000;

		// create the start of the line data file
		$this->line_data = "";

		// number of tics on the X, Y, Z axes
		// note that this is not actually the number of tics! it can be used as a rough estimate of the number of tics, though
		// i.e. the higher $this->num_of_tics, then higher the actual number of tics, though not one-to-one!
		$this->num_of_tics = 4;
		// the desired number of decimal places in our tics along the axes
		$this->desired_precision = 2;

	}

	public function set_key_name($name){
		$this->key_name = $name;
	}

	// adds data point. called by record_data
	protected function add_line_data ($one, $two, $three){
		// we only want to add a sample data point if we're under an amount defined by $this->sample_max
		if ($this->sample_count <= $this->sample_max){
			$this->line_data .= "$one $two $three\r\n";
		}
	}
	protected function add_point_data ($one, $two, $three){
		// we only want to add a sample data point if we're under an amount defined by $this->sample_max
		if ($this->sample_count <= $this->sample_max){
			$this->point_data .= "$one $two $three\r\n\r\n\r\n";
		}
	}

	protected function write_line_data(){
		// the line_data file will be used by gnuplot to create an image and be available to the user for download
		$this->line_data = "#$this->title query data organized by column\r\n" . $this->line_data;
	    file_put_contents($this->line_data_location, $this->line_data);
	}

	protected function write_point_data(){
		$this->point_data = "#$this->title query data organized by column\r\n"
			. "#this file has two spaces between points so gnuplot can use it to generate GIFs\r\n#1 2 3\r\n" . $this->point_data;
		file_put_contents($this->point_data_location, $this->point_data);
	}
				
	protected function track_max_min($one, $two, $three){
		// this if / else will allow us to keep track of the min / max of our data
		// from the max and min we can calculate the range or tics on the axis
		if ($this->has_entry)
		{
			if ($one < $this->one_min)
				$this->one_min = $one;
			if ($one > $this->one_max)
				$this->one_max = $one;
			if ($two < $this->two_min)
				$this->two_min = $two;
			if ($two > $this->two_max)
				$this->two_max = $two;
			if ($three < $this->three_min)
				$this->three_min = $three;
			if ($three > $this->three_max)
				$this->three_max = $three;
		}
		// else if there has not yet been a data entry
		else if (!$this->has_entry)
		{
			$this->has_entry = true;
			$this->one_min = $one;
			$this->one_max = $one;
			$this->two_min = $two;
			$this->two_max = $two;
			$this->three_min = $three;
			$this->three_max = $three;
		}
	}

	public function set_title($title){
		$this->title = $title;
	}

	// this function determines how many data points we'll have in our gnuplot script
	public function set_max_samples($sample_max)
	{
		// basic input validation
		if (is_numeric($sample_max) && $this->sample_count <= $sample_max && $sample_max >= 0)
			$this->sample_max = $sample_max;
	}

	public function set_x_label($label){
		$this->x_label = $label;
	}
	public function set_y_label($label){
		$this->y_label = $label;
	}
	public function set_z_label($label){
		$this->z_label = $label;
	}
	public function set_x_axis($column_num){
		$this->x_axis = $column_num;
	}
	public function set_y_axis($column_num){
		$this->y_axis = $column_num;
	}
	public function set_z_axis($column_num){
		$this->z_axis = $column_num;
	}

	// helper function for our set_tics functions
	protected function find_tic_length($max, $min){
		// we want to check if max != min because if they are equal and we subtract them, we'll get zero for tic_length, which will give us errors down the road
		if ($max != $min)
			$tic_length = (float)($max - $min) / $this->num_of_tics;
		else
			$tic_length = (float) $max / $this->num_of_tics;
		return $this->custom_round($tic_length);
	}

	// if we used the built-in PHP round function, for instance like, "$f = round(0.0001, 2)"
	// then $f would equal 0. If we then had a gnuplot command "set xtics $f" i.e. "set xtics 0" then we would get an error
	// so instead we have a custom round function that tries to get the number of decimal places as close to $this->desired_precision
	// as possible without letting our $f equal zero
	protected function custom_round($tic_length){
		$n = $this->desired_precision;
		$xtics = round($tic_length, $n);
		while (!$xtics)
		{
			$xtics = round($tic_length, $n);
			$n = $n+1;
		}
		return $xtics;
	}

	// when one of the axes is a frame then we have a different find_tic_length function for it
	protected function find_tic_length_for_frames(){
		$tic_length = (float) $this->sample_count / $this->num_of_tics;
		return $this->custom_round_frames($tic_length);
	}

	// generally, the frame number will be an integer, which we'd like, so a custom function to find the distance between tics on an axis with frame # values won't be necessary
	// i.e. the following function won't be necessary
	// but, there is the case where the user selects start frame equal zero and last frame equal 1. in that case we need to have frame numbers with one decimal place of accuracy
	// or else we get problems with gnuplot where we're setting tic length equal to zero
	protected function custom_round_frames($tic_length){
		$n = 0; // we prefer 0 decimal places for our frame count #
		$xtics = round($tic_length, $n);
		while (!$xtics)
		{
			$xtics = round($tic_length, $n);
			$n = $n+1;
		}
		return $xtics;
	}

	protected function set_xtics(){
		switch ($this->x_axis){
			case 0: // x-axis has frame #'s
				$this->xtics_string = "set xtics " . $this->find_tic_length_for_frames();
				break;
			case 1: // along x-axis we have column 1 data. find distance between tics based on number of tics desired and distance between column min and column max
				$this->xtics_string = "set xtics " . $this->find_tic_length($this->one_max, $this->one_min);
				break;
			case 2: // along x-axis we have column 2 data. find distance between tics based on number of tics desired and distance between column min and column max
				$this->xtics_string = "set xtics " . $this->find_tic_length($this->two_max, $this->two_min);
				break;
			case 3:
				$this->xtics_string = "set xtics " . $this->find_tic_length($this->three_max, $this->three_min);
				break;
		}
	}
	protected function set_ytics(){
		switch ($this->y_axis){
			case 0: // y-axis has frame #'s
				$this->ytics_string = "set ytics " . $this->find_tic_length_for_frames();
				break;
			case 1: // along y-axis we have column 1 data. find distance between tics based on number of tics desired and distance between column min and column max
				$this->ytics_string = "set ytics " . $this->find_tic_length($this->one_max, $this->one_min);
				break;
			case 2: // along y-axis we have column 2 data. find distance between tics based on number of tics desired and distance between column min and column max
				$this->ytics_string = "set ytics " . $this->find_tic_length($this->two_max, $this->two_min);
				break;
			case 3:
				$this->ytics_string = "set ytics " . $this->find_tic_length($this->three_max, $this->three_min);
				break;
		}
	}
	protected function set_ztics(){
		switch ($this->z_axis){
			case 0: // z-axis has frame #'s
				$this->ztics_string = "set ztics " . $this->find_tic_length_for_frames();
				break;
			case 1: // along z-axis we have column 1 data. find distance between tics based on number of tics desired and distance between column min and column max
				$this->ztics_string = "set ztics " . $this->find_tic_length($this->one_max, $this->one_min);
				break;
			case 2: // along z-axis we have column 2 data. find distance between tics based on number of tics desired and distance between column min and column max
				$this->ztics_string = "set ztics " . $this->find_tic_length($this->two_max, $this->two_min);
				break;
			case 3:
				$this->ztics_string = "set ztics " . $this->find_tic_length($this->three_max, $this->three_min);
				break;
		}
	}

	protected function record_png_data ($one, $two, $three){
		$this->sample_count++; // increase the number of samples we've taken by 1
		// it's important that we include columns 1, 2 and 3 in the call to add_line_data, even for 2-d plots
		// because the line data doubles as a data file that the user can download
		$this->add_line_data($one, $two, $three);
		$this->track_max_min($one, $two, $three);
	}

	protected function record_gif_data ($one, $two, $three){
		$this->sample_count++; // increase the number of samples we've taken by 1
		// the 3 dimensional gif needs both line and point data files for gnuplot
		$this->add_line_data($one, $two, $three);
		$this->add_point_data($one, $two, $three);
		$this->track_max_min($one, $two, $three);
	}
}

class twod extends gnuplot {

	protected function set_tics(){
		$this->set_xtics();
		$this->set_ytics();
	}

	protected function export_2dpng() {
			// create the text for the gnuplot_script
		// note that if we have an indent in this text then there is an indent in the script file (which is why it's aligned so funny-looking here)
		$this->gnuplot_script = "set title \"$this->title\"

#set to create a png file. size (width), (height) determines gif size.
set terminal png enhanced size 600, 600
set output '$this->output_file_location'
set xlabel '$this->x_label'
set ylabel '$this->y_label'
set nokey

#\"set xtics\" determines the distance between tics on the x-axis
$this->xtics_string
$this->ytics_string

plot '$this->line_data_location' using $this->x_axis:$this->y_axis linecolor rgb \"#CFC493\" title 'Center Of Mass' with lines;";

        // outputs gnuplot_script to disk
        file_put_contents($this->gnuplot_script_file_location, $this->gnuplot_script);

        // calls gnuplot from the command line to execute the script and output the gif
        shell_exec("gnuplot $this->gnuplot_script_file_location");

        // return the location of the gif image
        return $this->output_file_location;
    }
}

class twodpng extends twod {

	function __construct(){
		parent::__construct();
	}

	public function record_data($one, $two, $three){
		$this->record_png_data($one, $two, $three);
	}

	function export(){

	    $this->set_tics();
	    $this->write_line_data();
	    $this->export_2dpng();
	}
}

class twodgif extends twod
{
	// instantiate variables specific to gif
	var $point_data;

	function __construct(){
		parent::__construct();

		// we need the point_data for the gif, but not the PNGs
		$this->point_data_location = $_SERVER['DOCUMENT_ROOT'] . "/msdb/queries/gnuplot_tmp_files/point" . $this->gnuplot_id . ".data";
	}

	// used to record 3 data points, for instance X, Y, Z
	public function record_data ($one, $two, $three){
		$this->record_gif_data($one, $two, $three);
	}

}

class large_twodgif extends twodgif {
	function __construct(){
		parent::__construct();

		// we need a location for the gif
		$this->animated_output_location = $_SERVER['DOCUMENT_ROOT'] . "/msdb/queries/gnuplot_tmp_files/gnuplot_animated_output" . $this->gnuplot_id . ".gif";
	}

	private function export_2dgif_but_no_exec(){

		// create the text for the gnuplot_script
		// note that if we have an indent in this text then there is an indent in the script file (which is why it's aligned so funny-looking here)
		$this->gnuplot_script = "set title \"$this->title\"

#set to create a .gif file. \"delay 100\" makes (100 * 1/100 seconds) delay between frames of the .gif file. size (width), (height) determines gif size.
set terminal gif enhanced animate delay 120 size 600, 600
set output '$this->animated_output_location'
set xlabel '$this->x_label'
set ylabel '$this->y_label'
set key bmargin

#\"set xtics\" determines the distance between tics on the x-axis
$this->xtics_string
$this->ytics_string

#the rgb colors codes are green and gold, USF's school colors.
#the sample_num variable determines the number of data points to use in the GIF. 
#choosing less data points means the gif is generated much faster and occupies much less memory, 
#choosing more data points means the gif shows more data points 
#there are some differences between the points_data_file and line_data_file. both are output to disk if you wish to view them.
#these differences give us a moving point and a line, respectively, in our gif.
do for [n=0:($this->sample_count-1)]{
    plot '$this->line_data_location' every ::0::($this->sample_count-1) using $this->x_axis:$this->y_axis linewidth 1 linecolor rgb \"#CFC493\" notitle with lines, '$this->point_data_location' index n ";

    // we have to do some fancy stuff if the user asks to plot the frame number along one of the axes in the animated plot
    if (!($this->x_axis) || !($this->y_axis)) { // one of these three axes variables will be 0 if the user asked to have the frame number along one of the variables
    	// split point_data by newline
    	$lines = preg_split("/\r\n|\n|\r/", $this->point_data);
    	//$lines = explode(PHP_EOL, $this->point_data);=
    	$this->point_data = "";
    	$i = 0;
    	foreach ($lines as $line){
    		// number the nonempty strings only
    		if (!empty($line)){
    			$this->point_data .= $i . " " . $line . "\n";
    			$i++;
    		}
    		else{
    			$this->point_data .= "\n";
    		}
    	}

    	// at this point we have made our point_data file have a series of integers as the first column
    	// conveniently, to make the axes lines up with what the user input and our new point_data file
    	// all we have to do is increment the axes by one
    	$modified_x_axis = $this->x_axis + 1;
    	$modified_y_axis = $this->y_axis + 1;
    	$this->gnuplot_script .= " using $modified_x_axis:$modified_y_axis ";
    }
   	else {
	   	// create the text for the gnuplot_script
		// note that if we have an indent in this text then there is an indent in the script file
		$this->gnuplot_script .= " using $this->x_axis:$this->y_axis ";
   	}

   	$this->write_point_data();

    $this->gnuplot_script .= "linetype 3 linewidth 2 linecolor rgb \"#006747\" title sprintf(\"$this->key_name%i\",(n+1))
}";

        // outputs gnuplot_script to disk
	    $gnuplot_animated_script_location = $_SERVER['DOCUMENT_ROOT'] . "/msdb/queries/gnuplot_tmp_files/gnuplot_animated_script" . $this->gnuplot_id . ".glp";
		file_put_contents($gnuplot_animated_script_location, $this->gnuplot_script);

        // calls gnuplot from the command line to execute the script and output the gif
        //shell_exec("gnuplot $this->gnuplot_script_file_location");

        // return the location of the gif image
        return $this->output_file_location;
    }

	function export(){
		// create the gnuplot data files needed to create the image
	    $this->write_line_data();

	    $this->set_tics();
	    $this->export_2dpng();
	    $this->export_2dgif_but_no_exec();
	}
}

class small_twodgif extends twodgif {
	
	function __construct(){
		parent::__construct();
	}

	private function export_2dgif_and_exec(){

		// create the text for the gnuplot_script
		// note that if we have an indent in this text then there is an indent in the script file (which is why it's aligned so funny-looking here)
		$this->gnuplot_script = "set title \"$this->title\"

#set to create a .gif file. \"delay 100\" makes (100 * 1/100 seconds) delay between frames of the .gif file. size (width), (height) determines gif size.
set terminal gif enhanced animate delay 120 size 600, 600
set output '$this->output_file_location'
set xlabel '$this->x_label'
set ylabel '$this->y_label'
set key bmargin

#\"set xtics\" determines the distance between tics on the x-axis
$this->xtics_string
$this->ytics_string

#the rgb colors codes are green and gold, USF's school colors.
#the sample_num variable determines the number of data points to use in the GIF. 
#choosing less data points means the gif is generated much faster and occupies much less memory, 
#choosing more data points means the gif shows more data points 
#there are some differences between the points_data_file and line_data_file. both are output to disk if you wish to view them.
#these differences give us a moving point and a line, respectively, in our gif.
do for [n=0:($this->sample_count-1)]{
    plot '$this->line_data_location' every ::0::($this->sample_count-1) using $this->x_axis:$this->y_axis linewidth 1 linecolor rgb \"#CFC493\" notitle with lines, '$this->point_data_location' index n ";

    // we have to do some fancy stuff if the user asks to plot the frame number along one of the axes in the animated plot
    if (!($this->x_axis) || !($this->y_axis)) { // one of these three axes variables will be 0 if the user asked to have the frame number along one of the variables
    	// split point_data by newline
    	$lines = preg_split("/\r\n|\n|\r/", $this->point_data);
    	//$lines = explode(PHP_EOL, $this->point_data);=
    	$this->point_data = "";
    	$i = 0;
    	foreach ($lines as $line){
    		// number the nonempty strings only
    		if (!empty($line)){
    			$this->point_data .= $i . " " . $line . "\n";
    			$i++;
    		}
    		else{
    			$this->point_data .= "\n";
    		}
    	}

    	// at this point we have made our point_data file have a series of integers as the first column
    	// conveniently, to make the axes lines up with what the user input and our new point_data file
    	// all we have to do is increment the axes by one
    	$modified_x_axis = $this->x_axis + 1;
    	$modified_y_axis = $this->y_axis + 1;
    	$this->gnuplot_script .= " using $modified_x_axis:$modified_y_axis ";
    }
   	else {
	   	// create the text for the gnuplot_script
		// note that if we have an indent in this text then there is an indent in the script file
		$this->gnuplot_script .= " using $this->x_axis:$this->y_axis ";
   	}

   	$this->write_point_data();

    $this->gnuplot_script .= "linetype 3 linewidth 2 linecolor rgb \"#006747\" title sprintf(\"$this->key_name%i\",(n+1))
}";
        // outputs gnuplot_script to disk
        file_put_contents($this->gnuplot_script_file_location, $this->gnuplot_script);

        // calls gnuplot from the command line to execute the script and output the gif
        shell_exec("gnuplot $this->gnuplot_script_file_location");

        // return the location of the gif image
        return $this->output_file_location;
    }

	function export() {
		$this->set_tics();
		$this->write_line_data();
		$this->export_2dgif_and_exec();
	}
}

class threed extends gnuplot {
	// declare variables specific to 3d images
	var $point_data;
	var $z_max, $z_min;
	var $z_label;
	var $ztics_string;
	var $z_axis;
	
	function __construct(){
		parent::__construct();

		// where the files will be temporarily located during execution
		// I think it is easier to debug the script when it is written to file, though it could be passed directly to gnuplot via the command line
		// we use .glp and .data by convention but not by necessity
		$this->point_data_location = $_SERVER['DOCUMENT_ROOT'] . "/msdb/queries/gnuplot_tmp_files/point" . $this->gnuplot_id . ".data";
	}

	protected function set_tics(){
		$this->set_xtics();
		$this->set_ytics();
		$this->set_ztics();
	}

	// the 3d plot needs to be able to change the column titles so they don't look so funny
	protected function all_axes_change_spaces_to_newline(){
		if (strcmp($this->x_label, "Frame #") == 0){
			$this->x_label = "Frame\\n#";
		}
		if (strcmp($this->x_label, "Column 1") == 0){
			$this->x_label = "Column\\n1";
		}
		if (strcmp($this->x_label, "Column 2") == 0){
			$this->x_label = "Column\\n2";
		}
		if (strcmp($this->x_label, "Column 3") == 0){
			$this->x_label = "Column\\n3";
		}
		if (strcmp($this->y_label, "Frame #") == 0){
			$this->y_label = "Frame\\n#";
		}
		if (strcmp($this->y_label, "Column 1") == 0){
			$this->y_label = "Column\\n1";
		}
		if (strcmp($this->y_label, "Column 2") == 0){
			$this->y_label = "Column\\n2";
		}
		if (strcmp($this->y_label, "Column 3") == 0){
			$this->y_label = "Column\\n3";
		}
		if (strcmp($this->z_label, "Frame #") == 0){
			$this->z_label = "Frame\\n#";
		}
		if (strcmp($this->z_label, "Column 1") == 0){
			$this->z_label = "Column\\n1";
		}
		if (strcmp($this->z_label, "Column 2") == 0){
			$this->z_label = "Column\\n2";
		}
		if (strcmp($this->z_label, "Column 3") == 0){
			$this->z_label = "Column\\n3";
		}
	}

	protected function export_3dpng(){
		$this->all_axes_change_spaces_to_newline();


		// create the text for the gnuplot_script
		// note that if we have an indent in this text then there is an indent in the script file (which is why it's aligned so funny-looking here)
		$this->gnuplot_script = "set title \"$this->title\"
set title offset 0,-3

#I think 500 wide is optimal
set terminal png enhanced size 700, 500
set output '$this->output_file_location'
set xlabel \"$this->x_label\"
set ylabel \"$this->y_label\"
set zlabel \"$this->z_label\"
set zlabel offset -1,-0.5
set key bottom left

#sets the rotation of the 3d image
set view 60,17

#\"set xtics\" determins the distance between tics on the x-axis
$this->xtics_string
$this->ytics_string
$this->ztics_string

#the rgb color code is green and yellow, two of USF's school colors.
splot '$this->line_data_location' using $this->x_axis:$this->y_axis:$this->z_axis linewidth 1 linecolor rgb \"#CFC493\" notitle with lines";

		if ($this->sample_count < 100) // plotting points with the line looks funny once sample_count gets too high
			$this->gnuplot_script .= ", '$this->line_data_location' using $this->x_axis:$this->y_axis:$this->z_axis linetype 6 linewidth 1 linecolor rgb \"#CFC493\" notitle with points";

		// outputs gnuplot_script to disk
		file_put_contents($this->gnuplot_script_file_location, $this->gnuplot_script);

		// calls gnuplot from the command line to execute the script and output the gif
        shell_exec("gnuplot $this->gnuplot_script_file_location");

        // return the location of the gif image
		return $this->output_file_location;

	}
}

class threedgif extends threed {

	var $animated_output_location;

	function __construct(){
		parent::__construct();

		// where the files will be temporarily located during execution
		// I think it is easier to debug the script when it is written to file, though it could be passed directly to gnuplot via the command line
		// we use .glp and .data by convention but not by necessity
		$this->point_data_location = $_SERVER['DOCUMENT_ROOT'] . "/msdb/queries/gnuplot_tmp_files/point" . $this->gnuplot_id . ".data";
	}

	public function record_data ($one, $two, $three){
		$this->record_gif_data($one, $two, $three);
	}
}

class large_threedgif extends threedgif {

	function __construct(){
		parent::__construct();

		// we need a location for the gif
		$this->animated_output_location = $_SERVER['DOCUMENT_ROOT'] . "/msdb/queries/gnuplot_tmp_files/gnuplot_animated_output" . $this->gnuplot_id . ".gif";
	}

	// this function generates the gnuplot script necessary to generate a gif but doesn't execute the script
	// generating the script takes little time relative to generating the gif
	// so first we generate 3dgif script and show 3dpng image to user, then generate 3dgif image, then show 3dgif image
	private function export_3dgif_but_no_exec(){
		$this->all_axes_change_spaces_to_newline();

	    $this->gnuplot_script = "set title \"$this->title\"
set title offset 0,-2

#set to create a .gif file. \"delay 100\" makes (100 * 1/100 seconds) delay between frames of the .gif file. size (width), (height) determines gif size.
set terminal gif enhanced animate delay 120 size 700,500
set output '$this->animated_output_location'
set xlabel \"$this->x_label\"
set ylabel \"$this->y_label\"
set zlabel \"$this->z_label\"
set zlabel offset -1,-0.5

#sets the rotation of the 3d image
set view 60,17

#\"set xtics\" determins the distance between tics on the x-axis
$this->xtics_string
$this->ytics_string
$this->ztics_string

#reset the title offset so that the title Frame # gets shifted to the left
set key bmargin

#the rgb colors codes are green and gold, USF's school colors.
#the sample_num variable determines the number of data points to use in the GIF. 
#choosing less data points means the gif is generated much faster and occupies much less memory, 
#choosing more data points means the gif shows more data points 
#there are some differences between the points_data_file and line_data_file. both are output to disk if you wish to view them.
#these differences give us a moving point and a line, respectively, in our gif.
do for [n=0:($this->sample_count-1)]{
    splot '$this->line_data_location' every ::0::($this->sample_count-1) using $this->x_axis:$this->y_axis:$this->z_axis linetype 1 linewidth 1 linecolor rgb \"#CFC493\" notitle with lines, '$this->point_data_location' index n";

	    // we have to do some fancy stuff if the user asks to plot the frame number along one of the axes in the animated plot
	    if (!($this->x_axis) || !($this->y_axis) || !($this->z_axis)){ // one of these three axes variables will be 0 if the user asked to have the frame number along one of the variables
	    	// split point_data by newline
	    	$lines = preg_split("/\r\n|\n|\r/", $this->point_data);
	    	//$lines = explode(PHP_EOL, $this->point_data);=
	    	$this->point_data = "";
	    	$i = 0;
	    	foreach ($lines as $line){
	    		// number the nonempty strings only
	    		if (!empty($line)){
	    			$this->point_data .= $i . " " . $line . "\n";
	    			$i++;
	    		}
	    		else{
	    			$this->point_data .= "\n";
	    		}
	    	}

	    	// at this point we have made our point_data file have a series of integers as the first column
	    	// conveniently, to make the axes lines up with what the user input and our new point_data file
	    	// all we have to do is increment the axes by one
	    	$modified_x_axis = $this->x_axis + 1;
	    	$modified_y_axis = $this->y_axis + 1;
	    	$modified_z_axis = $this->z_axis + 1;
	    	$this->gnuplot_script .= " using $modified_x_axis:$modified_y_axis:$modified_z_axis ";
	    }
	   	else {
		   	// create the text for the gnuplot_script
			// note that if we have an indent in this text then there is an indent in the script file
			$this->gnuplot_script .= " using $this->x_axis:$this->y_axis:$this->z_axis ";
	   	}


	   	$this->write_point_data();
	   	$this->gnuplot_script .= "linetype 3 linewidth 1 linecolor rgb \"#006747\" title sprintf(\"$this->key_name%i\",(n+1))
}";
		// outputs gnuplot_script to disk
		$gnuplot_animated_script_location = $_SERVER['DOCUMENT_ROOT'] . "/msdb/queries/gnuplot_tmp_files/gnuplot_animated_script" . $this->gnuplot_id . ".glp";
		file_put_contents($gnuplot_animated_script_location, $this->gnuplot_script);

		// calls gnuplot from the command line to execute the script and output the gif
        // shell_exec("gnuplot $this->gnuplot_script_file_location");

        // return the location of the gif image
		return $gnuplot_animated_script_location;
	}

	// generates gif. returns gif location on disk.
	public function export(){

		// create the gnuplot data files needed to create the image
	    $this->write_line_data();

	    $this->set_tics();

	    // it's important that export_3dpng come before export_3dgif_but_no_exec because the second replaces files the first needs to generate the png
	    // for more info look at the comments for the export_3dgif_but_no_exec function
	    $this->export_3dpng();
	    $this->export_3dgif_but_no_exec();

	}
}
class small_threedgif extends threedgif {
	
	function __construct(){
		parent::__construct();
	}

	// same as export_3dgif_but_no_exec except that it goes the distance and creates the gif file through the shell_exec command
	private function export_3dgif_and_exec(){
		$this->all_axes_change_spaces_to_newline();

	    $this->gnuplot_script = "set title \"$this->title\"
set title offset 0,-2

#set to create a .gif file. \"delay 100\" makes (100 * 1/100 seconds) delay between frames of the .gif file. size (width), (height) determines gif size.
set terminal gif enhanced animate delay 120 size 700,500
set output '$this->output_file_location'
set xlabel \"$this->x_label\"
set ylabel \"$this->y_label\"
set zlabel \"$this->z_label\"
set zlabel offset -1,-0.5

#sets the rotation of the 3d image
set view 60,17

#\"set xtics\" determins the distance between tics on the x-axis
$this->xtics_string
$this->ytics_string
$this->ztics_string

#reset the title offset so that the title Frame # gets shifted to the left
set key bmargin

#the rgb colors codes are green and gold, USF's school colors.
#the sample_num variable determines the number of data points to use in the GIF. 
#choosing less data points means the gif is generated much faster and occupies much less memory, 
#choosing more data points means the gif shows more data points 
#there are some differences between the points_data_file and line_data_file. both are output to disk if you wish to view them.
#these differences give us a moving point and a line, respectively, in our gif.
do for [n=0:($this->sample_count-1)]{
    splot '$this->line_data_location' every ::0::($this->sample_count-1) using $this->x_axis:$this->y_axis:$this->z_axis linetype 1 linewidth 1 linecolor rgb \"#CFC493\" notitle with lines, '$this->point_data_location' index n";

	    // we have to do some fancy stuff if the user asks to plot the frame number along one of the axes in the animated plot
	    if (!($this->x_axis) || !($this->y_axis) || !($this->z_axis)){ // one of these three axes variables will be 0 if the user asked to have the frame number along one of the variables
	    	// split point_data by newline
	    	$lines = preg_split("/\r\n|\n|\r/", $this->point_data);
	    	//$lines = explode(PHP_EOL, $this->point_data);=
	    	$this->point_data = "";
	    	$i = 0;
	    	foreach ($lines as $line){
	    		// number the nonempty strings only
	    		if (!empty($line)){
	    			$this->point_data .= $i . " " . $line . "\n";
	    			$i++;
	    		}
	    		else{
	    			$this->point_data .= "\n";
	    		}
	    	}

	    	// at this point we have made our point_data file have a series of integers as the first column
	    	// conveniently, to make the axes lines up with what the user input and our new point_data file
	    	// all we have to do is increment the axes by one
	    	$modified_x_axis = $this->x_axis + 1;
	    	$modified_y_axis = $this->y_axis + 1;
	    	$modified_z_axis = $this->z_axis + 1;
	    	$this->gnuplot_script .= " using $modified_x_axis:$modified_y_axis:$modified_z_axis ";
	    }
	   	else {
		   	// create the text for the gnuplot_script
			// note that if we have an indent in this text then there is an indent in the script file
			$this->gnuplot_script .= " using $this->x_axis:$this->y_axis:$this->z_axis ";
	   	}


	   	$this->write_point_data();
	   	$this->gnuplot_script .= "linetype 3 linewidth 1 linecolor rgb \"#006747\" title sprintf(\"$this->key_name%i\",(n+1))
}";
		// outputs gnuplot_script to disk
		file_put_contents($this->gnuplot_script_file_location, $this->gnuplot_script);

		// calls gnuplot from the command line to execute the script and output the gif
        shell_exec("gnuplot $this->gnuplot_script_file_location");

        // return the location of the gif image
		return $this->output_file_location;
	}

	// generates gif. returns gif location on disk.
	public function export(){

		// create the gnuplot data files needed to create the image
	    $this->write_line_data();

	    $this->set_tics();

	    // for more info look at the comments for the export_3dgif_but_no_exec function
	    $this->export_3dgif_and_exec();

	}
}

class threedpng extends threed {
	function __construct(){
		parent::__construct();

		// where the files will be temporarily located during execution
		// I think it is easier to debug the script when it is written to file, though it could be passed directly to gnuplot via the command line
		// we use .glp and .data by convention but not by necessity
		$this->line_data_location = $_SERVER['DOCUMENT_ROOT'] . "/msdb/queries/gnuplot_tmp_files/line" . $this->gnuplot_id . ".data";
	}

	function record_data ($one, $two, $three){
		$this->record_png_data($one, $two, $three);
	}

	// generates png. returns png location on disk.
	function export(){

		// create the gnuplot data files needed to create the image
	    $this->write_line_data();

	    $this->set_tics();

		$this->export_3dpng();
	}
}

// if the user chooses none for all three axes then we'll create this dummy object
// just enough functionality to create a data file, but no image
class donotplot extends gnuplot
{

    function __construct()
    {
        parent::__construct();
    }

    // we still need to create the line.data file to offer to user as download, despite not plotting
    public function record_data($one, $two, $three)
    {
    	$this->line_data .= "$one $two $three\r\n";
    }
    
    function set_max_samples($sample_max)
    {
    }

    function export()
    {
        // the line_data file will be used by gnuplot to create an image and be available to the user for download
        $this->line_data_final = "#$this->title query data organized by column\r\n" . $this->line_data;
        file_put_contents($this->line_data_location, $this->line_data_final);
    }
}

?>