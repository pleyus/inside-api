<?php
	
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	$id = service_match_param('id');
	
	$tab_pictures = "inside_files";

	if( $USER->id < 2 ) // Si es que esta loggeado
		service_end(Status::Warning, 'Modulo protegido');

	$id = $id < 1 || USER_LEVEL != UserType::Admin || !CanDo('user')
		? $USER->uid
		: $id;

	//	Sacamos las imagenes
	$pics = service_db_select(
		"SELECT 
			* 
		FROM 
			$tab_pictures 
		WHERE 
			uid = :uid
		ORDER BY at DESC;", 
		[ uid => $id ]
	);

	//	Terminamos
	service_end(Status::Success, $pics ?: []);