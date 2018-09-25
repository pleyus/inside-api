<?php

	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	//	Tablas utilizadas
	$tab_guide = "radio_guide";
	$tab_gh = "radio_guide_announcers";

	//	Sacamos la info
	$id = service_match_param('id');

	//	Si se esta actualizando...
	if(USER_LEVEL == UserType::Admin && CanDo('radio'))
	{
		//	Actualizamos los datos del programa
		$q = "SELECT * FROM $tab_guide WHERE id = :id";
		$p = [ id => $id ];
		$r = service_db_select($q, $p);
		
		if( !empty($r) ){
			//	Quitamos los locutores y la info de la guia
			$q = "DELETE FROM $tab_gh WHERE gid = :id";
			$p = [ id => $id ];
			service_db_insert($q, $p);
			
			$q = "DELETE FROM $tab_guide WHERE id = :id;";
			$p = [ id => $id ];
			service_db_insert($q, $p);

			$img = $r[0][ img ];
			$q = "SELECT id FROM $tab_guide WHERE img = :img";
			$p = [ img => $img ];
			$r = service_db_select($q, $p);
			
			if(empty($r))
				unlink( $_SERVER['DOCUMENT_ROOT'] . "/uploads/radio/" . $img);

			service_end(Status::Success, 'Se ha eliminado correctamente el programa.');
		}
		service_end(Status::Warning, 'No hay nada que eliminar');
	}
	service_end(Status::Error, 'No tienes autorización para eliminar el programa.');