<?php

	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	//	Tablas utilizadas
	$tab_notes = "inside_applicants_notes";
	
	$aid = service_get_param('aid', Param::Post);
	$note = service_get_param('note', Param::Post);

	//	Si es que el nivel de usuario es Administrador 
	//	y el id del registro es > que 0 (se esta actualizando)
	if(  USER_LEVEL == UserType::Admin && CanDo('applicants') )
	{
		if(strlen($note) < 5)
			service_end(Status::Warning, 'La nota es muy corta, sea mas especifico.');
	
		//	Preparamos el query de actualización
		$query = "INSERT INTO $tab_notes (aid, uid, at, note) VALUES (:aid, :uid, :at, :note)";

		//	Acomodamos los parametros para evitar una posible inyeccion
		$params = 
		[
			'aid' => $aid,
			'uid' => $USER->uid,
			'at' => time(),
			'note' => $note	
		];
		
		
		if(service_db_insert($query, $params)){
			InsideLog(Actions::Create, Module::ApplicantsNotes, $aid);
			service_end(Status::Success, $params);
		}
		else{
			InsideLog(Actions::TryCreate, Module::ApplicantsNotes, $aid);
			service_end(Status::Warning,'No se pudo guardar la nota<br> – QUERY_ERROR') ;
		}
	}

	//	Si no quiere decir que se está creando un registro nuevo
	else{
		InsideLog(Actions::TryCreate, Module::ApplicantsNotes, $aid, 'Sin permisos');
		service_end(Status::Error, 'No se pudo guardar la nota<br> –ADMIN_LEVEL_REQUIRED' );
	}