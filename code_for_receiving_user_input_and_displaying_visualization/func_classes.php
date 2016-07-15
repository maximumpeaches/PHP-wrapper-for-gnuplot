<?php

include("func_base.php");

class centerofmass extends func_base{
    var $func_nickname = "centerofmass";
    var $func_name = "Center of Mass";
    var $func_desc = "The center of mass of the whole simulation system is computed as below. It is computed at every time
                    instant.
                    Following equation shows the effect on the mass center <em><b>R(t)</b></em> of the particle space at
                    a given
                    time instant <em><b>t</b></em> :<br/>
                    <img src='../images/mass_center.jpg' style='width: auto; height: auto;' title='Mass Center'/><br/>";
    function __construct(){
        parent::__construct();
    }
    function display(){
        $this->firstFrame();
        $this->lastFrame();
        $this->skip();
        $this->minimum();
        $this->maximum();
        $this->whole();
        $this->whole_pcb();
        $this->atomType();
        $this->molName();
        $this->atomID();
        $this->molID();
        $this->XYZaxes();
    }
}

class rdf extends func_base{
    var $func_nickname = "rdf";
    var $func_name = "Radial Distribution";
    var $func_desc = "Radial Distribution";
    function __construct(){
        parent::__construct();
    }
    function display(){
        $this->firstFrame();
        $this->lastFrame();
        $this->skip();
        $this->minimum();
        $this->maximum();
        $this->whole();
        $this->whole_pcb();
        $this->atomType();
        $this->molName();
        $this->molID();
        $this->atomID();
        $this->binWidth();
        $this->XYaxes();
    }
}

class radiusofgyration extends func_base{
    var $func_nickname = "radiusofgyration";
    var $func_name = "Radius of Gyration";
    var $func_desc = "Radius of Gyration";
    function __construct(){
        parent::__construct();
    }
    function display(){
        $this->firstFrame();
        $this->lastFrame();
        $this->skip();
        $this->minimum();
        $this->maximum();
        $this->display_xyz("vectorP1", "Vector P1");
        $this->molName();
        $this->atomType();
        $this->molID();
        $this->atomID();
        $this->whole();
        $this->whole_pcb();
        $this->XYaxes();
    }
}

class density extends func_base{
    var $func_nickname = "density";
    var $func_name = "Density";
    var $func_desc = "Density";
    function __construct(){
        parent::__construct();
    }
    private function display_densityType(){
        ?>
        <div id='densityTypeDiv' class='dynamicDiv'>
            <b>Density Type</b>
            <br/>
            <select id='densityType' name='densityType'
                    class='dynamicTextBox'>
                <option value=''>>>Select<<</option>
                <option value='0'>electron</option>
                <option value='1' selected>mass</option>
                <option value='2'>number</option>
                <option value='3'>charge</option>
            </select>
        </div>
        <?php
    }
    private function display_axis(){
        ?>
        <div id='axisDiv' class='dynamicDiv'>
            <b>Axis</b><br/>
            <select id='axis' name='axis'
                    class='dynamicTextBox'>
                <option value=''>>>Select<<</option>
                <option value='0'>X</option>
                <option value='1'>Y</option>
                <option value='2' selected>Z</option>
            </select></div>

        <?php
    }

    function display(){
        $this->firstFrame();
        $this->lastFrame();
        $this->skip();
        $this->minimum();
        $this->maximum();
        $this->whole();
        $this->whole_pcb();
        $this->atomType();
        $this->molName();
        $this->atomID();
        $this->molID();
        $this->display_axis();
        $this->display_densityType();
        $this->display_boolean("normalize", "Normalize", "1");
        $this->binWidth();
        $this->XYaxes();   
    }
}

class sdh extends func_base{
    var $func_nickname = "sdh";
    var $func_name = "Spatial Distance Histogram";
    var $func_desc = "
    				The SDH is one of the important queries in the computation of radial distribution function (RDF) and
                    other m(ulti)-body correlation functions. The SDH is a fundamental tool in the validation and
                    analysis of
                    particle simulation data. The RDF can be viewed as a normalized SDH. The basic problem of SDH
                    computation
                    is defined as follows:
                    <br/>

                    Given the coordinates of <em><b>N</b></em> points and a user-specified distance <em><b>w</b></em>,
                    we are required to compute the number of point-to-point distances that fall into a series of ranges
                    (i.e., buckets) of width <em><b>w: [0,w), [w, 2w),...,[(l-1)w, lw]</b></em>.
                    <br/>

                    In other words, the SDH computation gives an ordered list of non-negative integers <em><b>H = (h1,
                            h2,...,hl),
                            where each hi(0 < i = l) </b></em> is the number of distances that fall into the bucket
                    (distance range)
                    <em><b>[(i - 1)w, iw)</b></em>. As SDH is a basic tool in the validation and analysis of simulation
                    systems, its variations over a period of time during the simulation often become critical
                    information.";
    function __construct(){
        parent::__construct();
    }
    function display(){
        $this->firstFrame();
        $this->lastFrame();
        $this->skip();
        $this->minimum();
        $this->maximum();
        $this->whole();
        $this->whole_pcb();
        $this->molName();
        $this->atomType();
        $this->molID();
        $this->atomID();
        $this->binWidth();
    }
}

class msd extends func_base{
    var $func_nickname = "msd";
    var $func_name = "Mean Square Distance";
    var $func_desc = "The displacement of particles in the system at any time instant during the simulation is given by
                    mean square
                    displacement (MSD). The following equation gives the computation method :<br/>
                    <img src='../images/mean_square_displacement.jpg' style='width: auto; height: auto;'
                         title='Mass Center'/>";
    function __construct(){
        parent::__construct();
    }
    function display(){
        $this->firstFrame();
        $this->lastFrame();
        $this->skip();
        $this->minimum();
        $this->maximum();
        $this->whole();
        $this->whole_pcb();
        $this->molName();
        $this->atomType();
        $this->molID();
        $this->atomID();
        $this->binWidth();
        $this->display_text("trestart", "Trestart", "");
        $this->display_boolean("coeff", "Diffusion Coef", "");
        $this->display_text("begfit", "Beg Fit", "");
        $this->display_text("endfit", "End Fit", "");
        $this->XYZaxes();
    }    
}

class angle extends func_base{
    var $func_nickname = "angle";
    var $func_name = "Angle";
    var $func_desc = "Angle";
    function __construct(){
        parent::__construct();
    }
    function display(){
        $this->firstFrame();
        $this->lastFrame();
        $this->skip();
        $this->display_text("atomID1", "Atom ID1", "-1");
        $this->display_text("atomID2", "Atom ID2", "-1");
        $this->display_text("atomID3", "Atom ID3", "-1");
    }    
}

class bond extends func_base{
    var $func_nickname = "bond";
    var $func_name = "Bond";
    var $func_desc = "Bond";
    function __construct(){
        parent::__construct();
    }
    function display(){
        $this->firstFrame();
        $this->lastFrame();
        $this->skip();
        $this->display_text("atomID1", "Atom ID1", "-1");
        $this->display_text("atomID2", "Atom ID2", "-1");
    }    
}

class torsion extends func_base{
    var $func_nickname = "torsion";
    var $func_name = "Torsion";
    var $func_desc = "Torsion";
    function __construct(){
        parent::__construct();
    }
    function display(){
        $this->firstFrame();
        $this->lastFrame();
        $this->skip();
        $this->display_text("atomID1", "Atom ID1", "-1");
        $this->display_text("atomID2", "Atom ID2", "-1");
        $this->display_text("atomID3", "Atom ID3", "-1");
        $this->display_text("atomID4", "Atom ID4", "-1");
    }    
}