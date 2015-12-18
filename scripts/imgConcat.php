<?php

// paste together selected images in a 2D or 3D array

require_once $_SERVER['DOCUMENT_ROOT'] . '/include/main_func.php';
auth();
checkAllocation();

$return = array(
	'error' => true,
	'errorText' => '',
	'post' => $_POST
);

ini_set('memory_limit','256M');

$grid = $_POST['gridNames'];
$savedir = $_POST['savedir'];

//get first image to set image dimensions
$filename = IMAGEBASEDIR . $grid[0][0] . '.jpg';

if (!file_exists($filename)) {
	$return['errorText'] .= "$filename does not exist.";
} else if (!$firstimage = imagecreatefromjpeg($filename)) {
	$return['errorText'] .= "Could not open the image $filename";
} else {
	// load original image
	$w = imagesx($firstimage);
	$h = imagesy($firstimage);
	imagedestroy($firstimage);
	
	$cols = count($grid);
	$rows = count($grid[0]);
	
	// CHECK FINAL image dimensions
	$scale = ($h*$cols > 4400) ? (4400/$cols)/$h : 1;

	// create new concat image
	$concatimg = imagecreatetruecolor($w * $rows * $scale, $h * $cols * $scale); 
	
	// add each image to the concat image
	foreach ($grid as $c => $col) {
		foreach ($col as $r => $img) {
			$filename = IMAGEBASEDIR. $img . '.jpg';
			if (!file_exists($filename)) {
				$return['errorText'] .= "$filename does not exist.";
			} else if (!$gridimg = imagecreatefromjpeg($filename)) {
				$return['errorText'] .= "Could not open the image $filename";
			} else if ($scale == 1) {
				// copy this image onto the concat image
				imagecopy(
					$concatimg,
					$gridimg,
					$r*$w, $c*$h,
					0, 0, $w, $h
				);
				imagedestroy($gridimg);
			} else {
				// copy this image onto the concat image with resizing
				imagecopyresampled(
					$concatimg,
					$gridimg,
					$r*$w*$scale, $c*$h*$scale,
					0, 0, 
					$w*$scale, $h*$scale,
					$w, $h
				);
				imagedestroy($gridimg);
			}
		}
	}
	
	// save concat image
	$newfilename = IMAGEBASEDIR . $savedir . '/_array.jpg';
	imagejpeg($concatimg, $newfilename, 80);
	imagedestroy($concatimg);
	
	include_once DOC_ROOT . '/include/classes/psychomorph.class.php';

	$img = new PsychoMorph_Image($newfilename);
	$img->setDescription(array('concat' => array(
		'top left' => $_POST['topL'],
		'top right' => $_POST['topR'],
		'bottom left' => $_POST['botL'],
		'bottom right' => $_POST['botR'],
	)));
	$img->save($newfilename);
	
	// return concat file name
	$return['newfilename'] = $savedir . '/_array.jpg';
	$return['error'] = false;
}

scriptReturn($return);

exit;

?>