<?php
ini_set('display_errors', true);
	$mensagem['mensagem'] = "";
	$post_id = $_POST['post_id'];
	$pasta = 'arquivos/'.$post_id;

	if ( 0 < $_FILES['file']['error'] ) {
		$mensagem['mensagem'] = "erro-arquivo";
	}
	else {
		if (!file_exists($pasta)) {
			$mensagem['mensagem'] = "pasta não existe";
			mkdir($pasta, 0777, true);
		}
		else {
			$mensagem['mensagem'] = "pasta existe";
			delete_files($pasta.'/');
			mkdir($pasta, 0777, true);
		}

		$filename = $_FILES['file']['name'];
		$source = $_FILES["file"]["tmp_name"];
		$type = $_FILES["file"]["type"];

		$name = explode(".", $filename);

		if($_POST['extensao'] == "html") {
			$target_path = $pasta."/index.html";
		}
		else {
			$target_path = $pasta."/".$filename;
		}


		if(move_uploaded_file($source, $target_path)) {
			if($_POST['extensao'] != "html") {
				$zip = new ZipArchive();
				$x = $zip->open($target_path);
				if ($x === true) {
					$zip->extractTo($pasta);
					$zip->close();

					unlink($target_path);
				}
			}
			$mensagem['mensagem'] .= " - sucesso";
		} else {	
			$mensagem['mensagem'] .= " - erro";
		}
	}
	echo json_encode($mensagem);

	function delete_files($target) {
		if(is_dir($target)){
			$files = glob( $target . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned

			foreach( $files as $file ){
				delete_files( $file );      
			}
			rmdir($target);
		} elseif(is_file($target)) {
			unlink( $target );  
		}
	}
	
	?>