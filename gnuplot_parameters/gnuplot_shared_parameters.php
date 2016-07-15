<?php


if (isset($_POST['animate']) && is_numeric($_POST['animate']))
    if ((int) $_POST['animate'] == 1)
        $animate = true;
    else {
        $animate = false;
    }
else
    $animate = false;
if (isset($_POST['big']) && is_numeric($_POST['big'])){
	// we get values of like 1 and 4 and stuff for big so I dunno I'm just assigning it to true if it's a number. the other case it returns undefined so it wouldn't be set then anyways.
    $big = true;
}
else {
    $big = false;
}

?>