<?php
	
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	$id = service_match_param('id');
	$data = json_decode(service_match_param('data'), true);
	
	$tab_guide = "radio_guide";

	//	Solo los administradores pueden subir archivos
	if( USER_LEVEL == UserType::Admin && CanDo('radio') )
	{
		//	Checamos el directorio
		$dir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/radio/";
		if(!is_dir($dir))
			mkdir($dir);

		//	Preparamos el archivo
		$ext = '.' . array_pop( explode('.', $data['name']) );	//	Sacamos la extensión
		$tmp_name = $dir . rand(10000,99999) . $ext;
				
		$content = decode_file($data['content']);

		if($content === false)
			service_end(Status::Error, "El archivo ha sido corrompido, intente de nuevo");

		//	Checamos si hay una imagen asignada
		$current_img = false;
		$q = "SELECT img from $tab_guide WHERE id = :id LIMIT 1;";
		$p = ['id' => $id];
		$r = service_db_select($q, $p);
		if(!empty($r)){
			if(!empty($r[0]['img']))
				$current_img = $r[0]['img'];
		}
		else
			service_end(Status::Error, 'No se puede asignar la imagen');

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
					//	Actualizamos la imagen
					$q = "UPDATE $tab_guide SET img = :img WHERE id = :id";
					$p = [ 'img' => $new_name, 'id' => $id ];
					$r = service_db_insert($q, $p);

					//	En caso de que ya tuviera una imagen antes...
					if($r && $current_img !== false)
					{
						//	Revisamos si hay mas programas compartiendo la misma imagen
						$q = "SELECT id FROM $tab_guide WHERE img = :img";
						$p = ['img' => $current_img];

						//	Si no hay, entonces la borramos (la anterior)
						if( empty( service_db_select($q, $p) ) )
							@unlink($dir.$current_img);
					}

					service_end(Status::Success, $new_name);
				}
			}
		}
		@unlink($tmp_name);
		service_end(Status::Error, "Upa! algo no salió como esperaba, intenta nuevamente en un momento");
	}
	service_end(Status::Error, "No tienes autorización para entrar aqui");