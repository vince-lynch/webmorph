<?php

// symmetrise selected images and tems

require_once $_SERVER['DOCUMENT_ROOT'] . '/include/main_func.php';
auth();
checkAllocation();

$return = array(
    'error' => true,
    'errorText' => '',
    'post' => $_POST
);

include_once DOC_ROOT . '/include/classes/psychomorph.class.php';

$img = new PsychoMorph_ImageTem($_POST['img']);
$shape = ($_POST['shape'] == 'false') ? 0 : 0.5;
$colortex = ($_POST['color'] == 'false') ? 0 : 0.5;
$img->sym($shape, $colortex);

$newFileName = array(
    'subfolder' => $_POST['subfolder'],
    'prefix' => $_POST['prefix'],
    'suffix' => $_POST['suffix'],
    'name' => pathinfo($_POST['img'], PATHINFO_FILENAME),
    'ext' => $_POST['ext']
);

if ($img->save($newFileName)) {
    $return['error'] = false;
    $return['newFileName'] = $img->getImg()->getURL();
} else {
    $return['errorText'] .= 'The image was not saved. ';
    $return['newFileName'] = '';
}

scriptReturn($return);

exit;

?>