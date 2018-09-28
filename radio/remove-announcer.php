<?php

	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	//	Tablas utilizadas
	$tab_announcers = "radio_announcers";
	$tab_gh = "radio_guide_announcers";

	//	Sacamos la info
	$id = service_match_param('id');

	//	Si se esta actualizando...
	if( USER_LEVEL == UserType::Admin && CanDo('radio') )
	{
		//	Actualizamos los datos del programa
		$q = "DELETE FROM $tab_gh WHERE hid = :id";
		$p = [ id => $id ];
		$r = service_db_insert($q, $p);

		//	Actualizamos los datos del programa
		$q = "DELETE FROM $tab_announcers WHERE id = :id";
		$p = [ id => $id ];
		$r = service_db_insert($q, $p);
		
		service_end(Status::Success, 'Se ha eliminado correctamente el locutor.');
	}
	service_end(Status::Error, 'No tienes autorización para eliminar el locutor');