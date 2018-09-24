<?php
	
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	$id = service_match_param('id');
	
	$tab_guide = "radio_guide";
	$dir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/radio/";

	//	Solo admins pueden quitar las imagenes
	if( USER_LEVEL == UserType::Admin && CanDo('radio') )
	{
		$q = "SELECT img from $tab_guide WHERE id = :id LIMIT 1;";
		$p = ['id' => $id];
		$r = service_db_select($q, $p);
		$img = !empty($r) ? $r[0]['img'] : false;

		//	Le decimos que ya no va a tener la imagen
		$q = "UPDATE $tab_guide SET img = '' WHERE id = :id";
		$p = ['id' => $id ];
		service_db_insert($q, $p);


		//	Si existe la imagen
		if( file_exists($dir.$img) )
		{
			//	Checamos si es que nadie mas lo usa
			$q = "SELECT id FROM $tab_guide WHERE img = :img";
			$p = [ img => $img ];
			$r = service_db_select($q, $p);

			//	Si no hay mas usandola, entonces la borramos
			if( empty( $r ) )
				unlink($dir.$img);
			
		}

		service_end(Status::Success, "Imagen quitada del programa");
	}
	service_end(Status::Error, "No tienes autorización quitar la imagen.");