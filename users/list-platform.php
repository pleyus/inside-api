<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	$tab_users = "mdl_user";

	//	Solo pueden obtenerlos los administradores
	if( USER_LEVEL == UserType::Admin && CanDo('user') )
	{
		$query = 
		"SELECT
			id, firstname, lastname, email
		FROM 
			$tab_users " . 
			
			( 
				!empty($filter) 
					? " AND CONCAT_WS(' ', u.firstname, u.lastname, u.idnumber, u.email, u.firstname, u.lastname, u.idnumber, u.email, u.firstname) like :s"
					: '' 
			) .
		" ORDER BY 
			u.id DESC 
		LIMIT 5";

		$params = ['last' => $last ];

		if( !empty($filter) )
			$params['s'] = '%' . $filter . '%';

		if( $filter_type > -1 )
			$params['filter_type'] = $filter_type;
		
		$data = service_db_select( $query, $params );

		service_end(Status::Success, $data);
	}
	else
		service_end(Status::Error, 'Se requiere autorización para realizar esta operación');