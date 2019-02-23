<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	if (USER_LEVEL == UserType::Admin) {
		$year = date('Y');
		$month = date('m');
		
		$root = $_SERVER['DOCUMENT_ROOT'];
		$dir = '/uploads/' . $year . '/' . $month . '/';

		//	Checamos el directorio
		if( !is_dir($root . $dir ) )
			mkdir($root . $dir);
				
		//  Si no existe el archivo htaccess para proteger la ejecución en upload
		if( !file_exists($root . '/uploads/.htaccess') )
			//  Lo creamos
			file_put_contents(
				$root . '/uploads/.htaccess',
				'<Files *.php> deny from all </Files>'
			);

		//	Preparamos el archivo
		$tmp_name = $_FILES['the-file']['tmp_name'];
		$org_name = $_FILES["the-file"]["name"];
		$ext = '.' . array_pop( explode('.', $org_name) );	//	Sacamos la extensión
		$name = md5_file($tmp_name) . $ext;

		//  Movemos el archivo
		if(move_uploaded_file($tmp_name, $root . $dir . $name)) {

			//  Preparamos la info
			$at = time();
			$filename = service_match_param('filename') | basename($org_name) | $at;
			$mime = finfo::file($dir . $name, FILEINFO_MIME_TYPE);
			$description = service_match_param('description') | '';
			
			//  Preparamos la query
			$query = "INSERT INTO inside_files (filename, description, at, url, mimetype, uid) VALUES (:filename, :description, :at, :url, :mimetype, :uid);";
			$params = [ 
				'filename' => $filename,
				'description' => $description,
				'at' => $at,
				'url' => $_INSIDE_CONF['host'] . $dir . $name,
				'mimetype' => $mimetype,
				'uid' => $info_user['id']
			];
			if( service_db_insert($query, $params) )
					service_end(Status::Success, 'Subida exitosa.');
		}

		@unlink($dir . $name);
		service_end(Status::Error, 'No se pudo guardar el archivo.');
	}
	else
			service_end(Status::Error, 'Se requiere permiso de administrador para subir archivos');