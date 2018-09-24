<?php
	
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	$id = service_match_param('id');
	$id = 
		USER_LEVEL == UserType::Admin 
		? ( $id > 0 ? $id : $USER->id ) 
		: $USER->id;

	$tab_users = "mdl_user";

	//	Sacamos los datos de usuario
	$U = service_db_select
	(
		"SELECT
			id, 
			firstname, 
			lastname, 
			email,

			CONCAT(firstname, ' ', lastname) link_title,
			email link_subtitle,
			idnumber link_body,
			'' link_imgurl
			
			FROM $tab_users WHERE id = :id LIMIT 1",
		['id' => $id]
	);

	//	Si viene vacio, entonces terminamos
	if( empty($U) )
		service_end(Status::Error, 'No se han encontrado datos para este id');

	//	Si no, continuamos y regresamos solo al primero
	$U = $U[0];
	
	//	End.
	service_end(Status::Success, $U);
