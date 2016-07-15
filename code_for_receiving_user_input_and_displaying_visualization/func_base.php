class func_base{
    function __construct(){
        
    }
    public function func_info(){
        ?>
        <br/>
        <table class='queryInformation' cellpadding='3' cellspacing='1'>

            <tr class='queryInfoTableTR' width='22%'>
                <td colspan='2' width='78%'><b><?= $this->func_name ?> Information</b></td>
            </tr>
            <tr class='queryInfoTableTRAlt'>
                <td><b>Function Name</b></td>
                <td><?= $this->func_name ?></td>
            </tr>
            <tr>
                <td align='left'>
                    <b>Function Description</b>
                </td>
                <td align='left'>
                    <?= $this->func_desc ?>
                </td>
            </tr>

        </table>
        <?php
    }
    protected function getAtomType(){
        $query = "SELECT  DISTINCT type FROM $this->schema.atominfo ORDER BY type ASC";

        $result = pg_query($query);
        while ($sim_info = pg_fetch_assoc($result))
            $dataQuery[] = array('type' => $sim_info['type']);
        return $dataQuery;
    }
    protected function getAtomName(){
        $query = "SELECT DISTINCT atomname FROM $this->schema.atominfo ORDER BY atomname ASC ";

        $result = pg_query($query);
        while ($sim_info = pg_fetch_assoc($result))
            $dataQuery[] = array('name' => $sim_info['atomname']);

        return $dataQuery;
    }
    public function set_schema($schema){
        $this->schema = $schema;
    }
    // this function needs to be displayed before any of the functions that display the query param fields. it starts the table that the other query param fields belong to.
    public function beginning_params(){
        ?>
            <div style="text-align: center;">
                <table class='dynamicTable' cellpadding='2' cellspacing='0'>
                    <tr class='dynamicInfoTableTR'>
                        <td>
                            <b><?= $this->func_name ?> Parameters</b>
                        </td>
                    </tr>
                    <tr>
                <td>
            <div id='dynaminParams'>
            <form id="param_form" action="./function_files/<?= $this->func_nickname ?>function.php" method="post" class="func_param">
            <input type="hidden" name="func_name" id='func_name' value="<?= $this->func_nickname ?>">
            <input type="hidden" name="simschema" value="<?= $this->schema ?>">
        <?php
    }
    protected function display_text($param_name, $param_desc, $default_val){
        ?>
        <div id='<?= $param_name ?>Div' class='dynamicDiv'>
            <b><?= $param_desc ?></b>
            <br/>
            <input type='Text' id='<?= $param_name ?>' name='<?= $param_name ?>'
                   value='<?= $default_val ?>' class='dynamicTextBox'>
        </div>
        <?php
    }
    protected function display_xyz($param_name, $param_desc){
        // if down the road we decide to have a $default value, we can set it here, or wherever this function is called
        $default_val = "";
        ?>
        <div id='<?= $param_name ?>Div' class='dynamicDiv'>
            <b><?= $param_desc ?></b>
            <b> (X, Y, Z) </b><br/>
            <input type='Text' id='<?= $param_name ?> X'
                   name='<?= $param_name ?> X'
                   value='<?= $default_val ?>' size='2' display='inline'>
            <input type='Text' id='<?= $param_name ?> Y'
                   name='<?= $param_name ?> X'
                   value='<?= $default_val ?>' size='2' display='inline'>
            <input type='Text' id='<?= $param_name ?> Z'
                   name='<?= $param_name ?> X'
                   value='<?= $default_val ?>' size='2' display='inline'>
        </div>
        <?php
    }
    protected function display_selecttype($param_name, $param_desc){
        if ($this->schema != '') {

            $atomList = $this->getAtomType();

            if (count($atomList) > 0) { ?>

                <div id="atomTypeDiv" name="atomTypediv" class="dynamicDiv">
                    <b><?= $param_desc ?></b><br/>
                    <select multiple id="atomTypeDll" name="atomType[]" size="5"
                            class="atomTypeSelect">
                        <?php
                        foreach ($atomList as $key => $value) {
                            ?>
                            <option value="<?= $value["type"] ?>"><?= $value["type"] ?></option>
                        <?php
                        }

                        ?>
                    </select></div>&nbsp;&nbsp;
            <?php
            }
        }
    }
    protected function display_selectname($param_name, $param_desc){
        if ($this->schema != '') {

            $atomList = $this->getAtomName();
            if (count($atomList) > 0) {
                ?>
                <div id="atomNameDiv" name="atomNamediv" class="dynamicDiv">
                    <b><?= $param_desc ?></b>
                    <br/>
                    <select multiple id="molName" name="atomName[]" size="5"
                            class="atomTypeSelect">
                        <?php

                        foreach ($atomList as $key => $value) {
                            ?>
                            <option value="<?= $value["name"] ?>"><?= $value["name"] ?></option>
                        <?php
                        }

                        ?>
                    </select>
                </div>
                &nbsp;&nbsp;
            <?php
            }
        }
    }
    protected function display_boolean($param_name, $param_desc, $default_val){
        if ($default_val == 0) {
            ?>
            <div id='<?= $param_name ?>Div' class='dynamicDiv'>
                <b><?= $param_desc ?></b>
                <br/>
                <select id='<?= $param_name ?>' name='<?= $param_name ?>'
                        class='atomTypeSelect'>
                    <option value=''>>>Select<<</option>
                    <option value='true'>Yes</option>
                    <option value='false' selected>No</option>
                </select>
            </div>
        <?php
        } else {
            ?>
            <div id='<?= $param_name ?>Div' class='dynamicDiv'>
                <b><?= $param_desc ?></b><br/>
                <select id='<?= $param_name ?>' name='<?= $param_name ?>'
                        class='atomTypeSelect'>
                    <option value=''>>>Select<<</option>
                    <option value='true' selected>Yes</option>
                    <option value='false'>No</option>
                </select>
            </div>
            <?php
        }
    }
    // it is important that we use values 0, 4, 1, 2, 3, as they are (unless they changed since I wrote this!) b/c they each have some meaning within the other scripts
    protected function display_xyaxes($xAxis_name, $yAxis_name){
        ?>
        <div id='xAxisDiv' class='dynamicDiv'>
                    <b>X axis</b>
                    <br/>
                    <select id='xAxis' name='xAxis'>
                        <option value='1'><?= $yAxis_name ?></option>
                        <option value='4' selected><?= $xAxis_name ?></option>
                        <option value='0'>None</option>
                    </select>
                </div>
                <div id='yAxisDiv' class='dynamicDiv'>
                    <b>Y Axis</b>
                    <br/>
                    <select id='yAxis' name='yAxis'>
                        <option value='1' selected><?= $yAxis_name ?></option>
                        <option value='4'><?= $xAxis_name ?></option>
                        <option value='0'>None</option>
                    </select>
                </div>
                <div id="animateDiv" class="dynamicDiv">
                    <b>Animate</b>
                    <br/>
                    <select id='animate' name='animate'>
                        <option value='1' selected>Yes</option>
                        <option value='0'>No</option>
        </div>
        <?php
    }
    // it is important that we use values 0, 4, 1, 2, 3, as they are (unless they changed since I wrote this!) b/c they each have some meaning within the other scripts
    protected function display_xyzaxes($xAxis_name, $yAxis_name, $zAxis_name){
        ?>
        <div id='xAxisDiv' class='dynamicDiv'>
            <b>X axis</b>
            <br/>
            <select id='xAxis' name='xAxis'>
                <option value='1'>>>Select<<</option>
                <option value='1' selected><?= $xAxis_name ?></option>
                <option value='2'><?= $yAxis_name ?></option>
                <option value='3'><?= $zAxis_name ?></option>
                <option value='4'>Frame #</option>
                <option value='0'>None</option>
            </select>
        </div>
        <div id='yAxisDiv' class='dynamicDiv'>
            <b>Y Axis</b>
            <br/>
            <select id='yAxis' name='yAxis'>
                <option value='2'>>>Select<<</option>
                <option value='1'><?= $xAxis_name ?></option>
                <option value='2' selected><?= $yAxis_name ?></option>
                <option value='3'><?= $zAxis_name ?></option>
                <option value='4'>Frame #</option>
                <option value='0'>None</option>
            </select>
        </div>
        <div id='zAxisDiv' class='dynamicDiv'>
            <b>Z axis</b>
            <br/>
            <select id='zAxis' name='zAxis'>
                <option value='3'>>>Select<<</option>
                <option value='1'><?= $xAxis_name ?></option>
                <option value='2'><?= $yAxis_name ?></option>
                <option value='3' selected><?= $zAxis_name ?></option>
                <option value='4'>Frame #</option>
                <option value='0'>None</option>
            </select>
        </div>
        <div id="animateDiv" class="dynamicDiv">
            <b>Animate</b>
            <br/>
            <select id='animate' name='animate'>
                <option value='0'>>>Select<<</option>
                <option value='1' selected>Yes</option>
                <option value='0'>No</option>
        </div>
        <?php
    }
    public function display_end(){
        ?> 
                        </div>
                       </td>
                    </tr>
                    <br/>
                    <br/>
                </table>
                <div style="text-align:left;">
                <input type="Submit" value="Query Data" align="left" class='dynamicParamButton' id="param_submit">
                            </div>
                            </form>

            </div>
            <br/>
        <?php
    }
    protected function skip(){
        $this->display_text("skip", "Skip Frame", "0");
    }
    protected function firstFrame(){
        $this->display_text("firstFrame", "First Frame", "0");
    }
    protected function lastFrame(){
        $this->display_text("lastFrame", "Last Frame", "15");
    }
    protected function minimum(){
        $this->display_xyz("min", "Minimum");
    }
    protected function maximum(){
        $this->display_xyz("max", "Maximum");
    }
    protected function whole(){
        $this->display_boolean("whole", "Whole", "0");
    }
    protected function whole_pcb(){
        $this->display_boolean("whole_pcb", "Whole PCB", "0");
    }
    protected function atomType(){
        $this->display_selecttype("atomType", "Atom type");
    }
    protected function molName(){
        $this->display_selectname("molName", "Mol Name", "ALL");
    }
    protected function atomID(){
        $this->display_text("atomID", "Atom ID", "ALL");
    }
    // if you look in the table, the molID has a default value other than ALL for several functions
    protected function molID(){
        $this->display_text("molID", "Mol ID", "ALL");
    }
    protected function XYZaxes(){
        $this->display_xyzaxes("X axis", "Y axis", "Z axis");
    }
    protected function XYaxes(){
        $this->display_xyaxes("X axis", "Y axis");
    }
    protected function binWidth(){
        $this->display_text("binWidth", "Bin Width", "50");
    }
};