<?php

	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	//	Tablas utilizadas
	$tab_notes = "inside_applicants_notes";
	$tab_users = "info_user";
	$aid = service_get_param('aid', Param::Post);

	//	Si es que el nivel de usuario es Administrador 
	//	y el id del registro es > que 0 (se esta actualizando)
	if(  USER_LEVEL == UserType::Admin && CanDo('applicants') )
	{
	
		//	Preparamos el query de actualización
		$query = "SELECT n.id, n.aid, n.uid, n.at, n.note, u.firstname, u.lastname 
		FROM $tab_notes n
		LEFT JOIN $tab_users u on u.id = n.uid
		WHERE aid = :aid ORDER BY at DESC;";

		//	Acomodamos los parametros para evitar una posible inyeccion
		$params = [ 'aid' => $aid ];
		
		$N = service_db_select($query, $params);
		InsideLog(Actions::View, Module::ApplicantsNotes, $aid);

		service_end(Status::Success, $N);
	}

	//	Si no quiere decir que se está creando un registro nuevo
	else{
		InsideLog(Actions::TryView, Module::ApplicantsNotes, $aid, 'Sin permiso');
		service_end(Status::Error, 'No se pudieron obtener las notas del aspirante<br> –ADMIN_LEVEL_REQUIRED' );
	}