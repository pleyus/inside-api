<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();
	
	//	Solo pueden obtenerlos los administradores
	if( USER_LEVEL == UserType::Admin && CanDo('applicants'))
	{
		$query = 
		"SELECT
			u.id,
			u.firstname,
			u.lastname,
			u.pid,
			p.filename,
			u.status,
			c.applicants as capable
		FROM 
			info_user u
			LEFT JOIN info_user_pictures p ON p.id = u.pid
			LEFT JOIN info_user_capabilities c ON c.uid = u.id
		WHERE
			u.type = 4";

		$r = service_db_select($query);
		service_end(Status::Success, $r);
	}
	
	else
		service_end(Status::Error, 'Se requiere autorización para realizar la operación');