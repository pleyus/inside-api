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
			u.fid,
			f.url,
			u.status,
			c.applicants as capable
		FROM 
			info_user u
			LEFT JOIN inside_files f ON f.id = u.fid
			LEFT JOIN info_user_capabilities c ON c.uid = u.id
		WHERE
			u.type = 4";

		$r = service_db_select($query);
		service_end(Status::Success, $r);
	}
	
	else
		service_end(Status::Error, 'Se requiere autorización para realizar la operación');