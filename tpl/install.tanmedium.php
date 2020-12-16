<?php

require 'lib/phpFinTS/vendor/autoload.php';

$fints_config = kd_install::load_config();

$fints = require_once 'init.php';

$tanModes = $fints->getTanModes();
if (empty($tanModes)) {
    kd_install::print_msge('Your bank does not support any TAN modes!');
    return;
}

$tanModeNames = array_map(function (\Fhp\Model\TanMode $tanMode) {
    return $tanMode->getName(); }
, $tanModes);


$tanModeIndex = trim($_POST['FORM']['tanmode']);
#$tanModeIndex = 921;
if (!is_numeric($tanModeIndex) || !array_key_exists(intval($tanModeIndex), $tanModes)) {
    kd_install::print_msge('Invalid tanMode index!');
    die();
}
$tanMode = $tanModes[intval($tanModeIndex)];
echo 'You selected ' . $tanMode->getName() . "\n";

// In case the selected TAN mode requires a TAN medium (e.g. if the user picked mTAN, they may have to pick the mobile
// device on which they want to receive TANs), let the user pick that too.
if ($tanMode->needsTanMedium()) {
    $tanMedia = $fints->getTanMedia($tanMode);
    if (empty($tanMedia)) {
        kd_install::print_msge('Your bank did not provide any TAN media, even though it requires selecting one!');
        die;
    }

    $tanMediaNames = array_map(function (\Fhp\Model\TanMedium $tanMedium) {
        return $tanMedium->getName(); }
    , $tanMedia);

    #$INSTALL::echoarr($tanMediaNames);
    echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST">
<input type="hidden" name="cmd" value="select_tanmedia">
<h3>Here are the available TAN media:</h3>
<div class="form-group"><label>Which one do you want to use?</label>
    <select name="FORM[tanmedia]" class="form-control">';
    foreach ($tanMediaNames as $id => $tanMedium) {
        echo '<option value="' . $tanMedium . '">' . $tanMedium . '</option>';
    }
    echo '</select></div>
<button type="submit" class="btn btn-primary">select</button>
</form>
';
}
else {
    echo '
    <form action="" method="POST">
<input type="hidden" name="cmd" value="select_tanmedia"><input type="hidden" name="FORM[tanmdeia]" value=""/>
<button type="submit" class="btn btn-primary">save</button></form>';
}
