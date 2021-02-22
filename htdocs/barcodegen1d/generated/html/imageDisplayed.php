<?php
require '../../../main.inc.php';

$hosts = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['SERVER_NAME']."/";
$imgdata = $hosts.DOL_URL_ROOT."/barcodegen/generated/html/image.php?codebare=".GETPOST("codebare");
$imgdata = $hosts.DOL_URL_ROOT."/barcodegen/generated/html/image.php?codebare=".GETPOST("codebare");
$im = imagecreatefrompng($imgdata);
header('Content-Type: image/png');
header('Content-Disposition:attachment;filename='.GETPOST("codebare").'.png');
imagepng($im);
imagedestroy($im);

/*ob_start();
imagepng($cardTemplateImage);
$cardTemplateImage = ob_get_contents();
ob_end_clean();
$this->loggerService->log(__CLASS__, __FUNCTION__, 'End');
return 'data:image/.png' . ';base64,' . base64_encode($cardTemplateImage);*/

