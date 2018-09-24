<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();
	//error_reporting(0);

	$s = str_replace(' ', '%', service_match_param('s') );
	$tab_user = "mdl_user";

	//	Solo pueden obtenerlos los administradores
	if( USER_LEVEL >= UserType::Admin && CanDo('user') )
	{
		
		$data = service_db_select(
			"SELECT
				*,
				
				CONCAT(firstname, ' ', lastname) link_title,
				email link_subtitle,
				idnumber link_body,
				'' link_imgurl

			FROM 
				$tab_user
				
			WHERE
				CONCAT_WS(' ', firstname, lastname, idnumber, email, firstname, lastname, idnumber, email) like :s

			ORDER BY idnumber DESC
			LIMIT 10", 
			['s' => '%' . $s . '%'] );		
		service_end(Status::Success, $data);
	}
	else
		service_end(Status::Error, 'Se requiere nivel de administrador para realizar la operación');