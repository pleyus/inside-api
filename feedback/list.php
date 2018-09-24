<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	$last = service_get_param('last', Param::Post);
	$search = service_get_param('search', Param::Post);

	//	Preparamos los parametros
	$params = ['last' => $last];

	//
	if( !empty($search) )
		$params['like'] = '%' . $search . '%';

	//	Preparamos la consulta
	$query = "SELECT id, uid, at, comment, response, rat, hide, status FROM 
			info_feedback WHERE 1 " . 

			(USER_LEVEL == UserType::Admin && CanDo('feedback') ? '' : ' AND uid = ' . $info_user['id'] ).
			( 
				!empty($search) 
					? " AND CONCAT(comment, response) LIKE :like AND "
					: '' 
			) .
		" ORDER BY at DESC LIMIT :last, 10"; 

	// Buscamos el pago
	#service_end(Status::Error, get_prepared_query($query, $params));
	$P = service_db_select
	(
		$query,
		$params
	);
	
	//	Fin :D
	service_end(Status::Success, $P);
