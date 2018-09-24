<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	$id = service_match_param('id');

	//	Solo pueden obtenerlos los administradores
	if( USER_LEVEL >= UserType::Admin && CanDo('messages') )
	{
		$p = [
			'uid' => $info_user['id'],
			'at' => time(),
			'id' => $id
		];
		$q = "UPDATE
			services_contact
		SET
			seen_by = :uid, 
			seen_at = :at
		WHERE id = :id";

		if( service_db_insert($q, $p) )
			service_end(Status::Success, $p);
		else
			service_end(Status::Warning, "No se pudo marcar el mensaje como visto.<br><br> –" . service_db_error()[2]);
	}
	else
		service_end(Status::Error, 'Se requiere nivel de administrador para realizar la operación');