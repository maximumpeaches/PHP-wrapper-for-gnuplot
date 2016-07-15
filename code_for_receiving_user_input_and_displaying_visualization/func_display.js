// these are some functions associated with the gnuplot functionality of the webpage
function displayResults(random_num, big, data, xAxis, yAxis, zAxis){
    $("#waiting_img").hide();
    $("#waiting_text").hide();

    var yes = Number(xAxis) || Number(yAxis) || Number(zAxis);

    userWantsImage(yes, random_num);

    // display any output from the php file that did the query, for instance debugging-type echo messages
    $('div#message').html(data).show(500);
    $('div#message').html(data).fadeIn({duration: 700});

    $('html,body').animate({
        scrollTop: $('div#message').offset().top
    });
    if (big){
    	andLoadGif(random_num);	
    }    
}
function showResultReady(data) {
    // hide results from previous query
    $("#data_file").hide(0);
    $("#alt_data_file").hide(0);
    $("#waiting_img").hide();
    $("#waiting_text").hide();
    $("#results").hide();

    $('div#message').html(data).show(500);
    $('div#message').html(data).fadeIn({duration: 700});

    $('html,body').animate({
            scrollTop: $('div#message').offset().top
    });
}
function andLoadGif(random_num){
    // if the user entered a large number of points and selected animate
    // then we want to first show the png, which we should have already done, and then below we will generate the gif, and show it once it is ready
    $("#waiting_img").show();
    $("#waiting_text").show();
    // ask the file generate_gif.php to generate the GIF image in the background
    $.post('./generate_gif.php',
        {
            random_num: random_num
        },
        function () {
            $("#waiting_img").hide();
            $("#waiting_text").hide();

            // set the gnuplot image to the new GIF that was generated
            $("#gnuplot_img").attr("src", "../queries/gnuplot_tmp_files/gnuplot_animated_output" + random_num + ".gif");
        }
    );
}
function userWantsImage(yes, random_num){
    if (yes){
        var gnuplot_img_results = document.getElementById("gnuplot_img");
        gnuplot_img_results.src = "../queries/gnuplot_tmp_files/gnuplot_output" + random_num;
        $("#results").show(500);
        var dataFile = $("#data_file");
    } else {
        $("#results").hide();
        $("#alt_data_div").show(500);
        var dataFile = $("#alt_data_file");
    }
    // change the data_file link to a url that when clicked will send a get request to force_dl.php to force the txt data file to be downloaded rather than opened in a new page
    console.log("userWantsImage has random num" + random_num);
    $(dataFile).attr("href", "../scripts/force_dl.php?random_num=" + random_num);
}
function showWaiting(){
    $("#waiting_img").show();
    $("#waiting_text").html("Your query is being processed.<br/>");
    $("#waiting_text").show();
    $("#interface").hide();
    $("#alt_data_div").hide();
    $("#results").hide();
}

// this function is used to gather the user input from the form and send it en masse to the PHP files that will process it
$("#param_submit").click( function(){
	/*	This function runs when the user clicks the Submit button for a function. 
	*/
	var lastFrame = $('#lastFrame').val();
	var firstFrame = $('#firstFrame').val();
	var animate = $('#animate').val();
	var xAxis = $('#xAxis').val();
	var yAxis = $('#yAxis').val();
	var zAxis = $('#zAxis').val();
	var func_name = $('#func_name').val();

	if (!func_name || func_name.trim().length === 0) {
        $("#QueryInfo").html("<p>Error Please enter valid function name</p>");
        return;
    }
    
    /* Here we branch depending on whether our object is 3d (zAxis is defined) or 2d (zAxis is undefined) */
	if (zAxis === undefined){
		if (func_name === "density"){
			var big = (Number($('#binWidth').val()) > 250) && Number(animate) && (Number(xAxis) || Number(yAxis));
		} else {
			var big = (Number(lastFrame) - Number(firstFrame) > 250) && Number(animate) && (Number(xAxis) || Number(yAxis));
		}
		// The reason I put showWaiting inside the if branch is to emphasize that it comes at this point in the sequence of events.
		showWaiting();
	} else if (zAxis) {
		var big = (Number(lastFrame) - Number(firstFrame) > 250) && Number(animate) && (Number(xAxis) || Number(yAxis) || Number(zAxis));
		// if the user tries to plot a two-dimensional graph with the z-axis, print an error message
	    if ((!Number(xAxis) || !Number(yAxis)) && Number(zAxis)) {
	        $("#QueryInfo").html("<p>A 2-dimensional plot cannot have a Z-axis. Please select None for the Z-axis if you wish to create a 2-dimensional image.</p>");
	        return;
    	}
    	showWaiting();
	} else {
		console.log("z has unexpected value");
	}
	
	var random_num = Math.floor((Math.random() * 1000000) + 1);
	
	var form_inputs = $("#param_form :input").serializeArray();
	form_inputs.push({ "name": "random_num", "value": random_num.toString() });
	form_inputs.push({ "name": "big", "value": big.toString() });
	
	$.post( $("#param_form").attr("action"), 
		form_inputs,
		function(data){
			displayResults(random_num, big, data, xAxis, yAxis, zAxis);
			//showResultReady will output just error messages without any gnuplot image plotting
			//showResultReady(data);
		});
	$("#param_form").submit( function(){
		return false;
	});
});