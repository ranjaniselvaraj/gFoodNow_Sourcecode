<?php echo "here";
$zip = new ZipArchive;
$res = $zip->open('yokart-v7.1.zip');
if ($res === TRUE) {
 $zip->extractTo('/home/87929.cloudwaysapps.com/ubhwurhtdh/public_html/');
 $zip->close();
 echo 'woot!';
} else {
 echo 'doh!';
}

?>