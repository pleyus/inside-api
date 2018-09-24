<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	$tab = "services_contact";
	$query_concat = "";

	$last = service_match_param('last');
	$last = $last ?: 0;
	$params = ['last' => $last ];
	
	$s = str_replace(' ', '%', service_match_param('s'));
	if(!empty($s))
	{
		$params['s'] = '%' . $s . '%';
		$query_concat = " WHERE CONCAT_WS(' ', m.firstname, m.lastname, m.phone, m.email, m.message, ip) like :s ";
	}


	//	Solo pueden obtenerlos los administradores
	if( USER_LEVEL >= UserType::Admin )
	{
		$query = "SELECT 
			m.*,
			IF(u.status = 0, 1, 0) enrolled
		FROM 
			services_contact m
			LEFT JOIN inside_applicants a ON a.id = m.aid
			LEFT JOIN info_user u ON u.id = a.uid
		
		$query_concat 
		
		ORDER BY at DESC LIMIT :last, 10";
		
		$data = service_db_select( $query, $params );
		
		service_end(Status::Success, $data);
	}
	else
		service_end(Status::Error, 'Se requiere nivel de administrador para realizar la operación');