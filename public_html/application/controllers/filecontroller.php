<?php
class FileController extends Controller{
	function default_action(){
		exit('Invalid request!!');
	}
	function download_attachment($file = ''){
		Utilities::outputFile('front-users/'.$file,false,false,'',false);
	}
}
