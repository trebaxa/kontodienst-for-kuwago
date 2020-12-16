<?php

require 'lib/phpFinTS/vendor/autoload.php';

$fints_config = kd_install::load_config();

$fints = require_once 'init.php';


$tanModes = $fints->getTanModes();
if (empty($tanModes)) {
    kd_install::print_msge('Your bank does not support any TAN modes!');
    die();
}

echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST">
<input type="hidden" name="cmd" value="select_tanmode">
<h3>Here are the available TAN modes:</h3><br>';
$tanModeNames = array_map(function (\Fhp\Model\TanMode $tanMode) {
    return $tanMode->getName(); }
, $tanModes);

#$INSTALL::echoarr($tanModeNames);

echo '<div class="form-group">
    <label>Which one do you want to use?</label>
    <select name="FORM[tanmode]" class="form-control">';
foreach ($tanModeNames as $id => $tanMode) {
    echo '<option value="' . $id . '">' . $tanMode . '</option>';
}
echo '</select></div>
<button type="submit" class="btn btn-primary">select</button>
</form>
';
