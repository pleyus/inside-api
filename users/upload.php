<?php
//	PRONTO DEJARÁ DE SER UTILIZABLE ESTE MAKE
	
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	$uid = service_match_param('uid');
	$data = json_decode(service_match_param('data'), true);
	//die( print_r([$GLOBALS], true));
	
	$tab_users = "info_user";
	$tab_pictures = "inside_files";

	//	Solo los administradores pueden subir archivos
	if( USER_LEVEL == UserType::Admin && CanDo('user') )
	{
		//	Checamos el directorio
		$dir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/users/";
		if(!is_dir($dir))
			mkdir($dir);

		//	Preparamos el archivo
		$ext = '.' . array_pop( explode('.', $data['name']) );	//	Sacamos la extensión
		$tmp_name = $dir . rand(10000,99999) . $ext;
		
		
		$content = decode_file($data['content']);

		if($content === false)
			service_end(Status::Error, "El archivo ha sido corrompido, intente de nuevo");

		if( file_put_contents($tmp_name, $content ) )
		{
			
			//	Limpiamos el nombre del archivo
			$new_name = md5_file($tmp_name) . $ext;	//	Le ponemos de nombre el MD5 + Ext
			
			//	Verificamos que el archivo sea una imagen
			if( getimagesize($tmp_name) !== false )
			{
				//	Si es una imagen, entonces la procesamos...
				if( rename($tmp_name, $dir.$new_name) )
				{
					$at = time();

					$query = "INSERT INTO $tab_pictures (uid, filename, at) VALUES (:uid, :filename, :at);";
					$params = ['uid' => $uid, 'filename' => $new_name, 'at' => $at ];
					service_db_insert($query, $params);

					$query = "SELECT * FROM $tab_pictures WHERE at = :at;";
					$params = ['at' => $at];
					$pic = service_db_select($query, $params);

					$pic = !empty($pic) ? $pic[0] : ['id' => 0, 'filename' => ''];
					
					$query = "UPDATE $tab_users SET fid = :fid WHERE id = :uid";
					$params = ['fid' => $pic['id'], 'uid' => $uid];
					service_db_insert($query, $params);

					service_end(Status::Success, $pic);
				}
			}
		}
		@unlink($tmp_name);
		service_end(Status::Error, "Upa! algo no salió como esperaba, intenta nuevamente en un momento");
	}
	service_end(Status::Error, "No tienes autorización para entrar aqui");