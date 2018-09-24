<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	if( USER_LEVEL >= UserType::User )
	{
		$id = service_match_param('id');

		if( !($id > 0 ) )
			service_end(Status::Error, 'No se puede obtener ' . (USER_LEVEL == UserType::Student ? 'tu' : 'la') . ' información');


		// Buscamos
		$P = service_db_select
		(
			"SELECT * FROM info_feedback WHERE id = :id",
			['id' => $id]
		);

		//	Si NO lo encontramos terminamos
		if( empty($P) )
			service_end(Status::Error, 'No se han encontrado datos para este id');

		//	Sacamos el primero
		$P = $P[0];
		$P['user'] = false;
		// $P['responder'] = false;
		$P['rid'] = 0;

		//	Si no es anonimo
		if ( !( $P[ hide ] > 0 ) )
		{
			//	Obtenemos los datos de la persona quien lo realizó
			$U = service_db_select
			(
				"SELECT
					u.id,
					u.firstname,
					u.lastname,
					u.personal_phone,
					u.level,
					c.name course,
					u.cid,
					u.idnumber
				FROM
					info_user u
					LEFT JOIN info_categories c ON c.id = u.cid
					
				WHERE u.id = :id",
				['id' => $P['uid']]
			);
			$P['user'] = $U[0] ?: false;
		}
		else
			$P['uid'] = 0;
		
		//	Si se ha etiquetado a un usuario
		// if($P['aid'])
		// {
		// 	//	Obtenemos los datos de la persona quien contestó
		// 	$U = service_db_select
		// 	(
		// 		"SELECT id, firstname, lastname FROM info_user WHERE id = :id",
		// 		['id' => $P['aid']]
		// 	);
		// 	$P['assigned'] = !empty($U) ? $U[0] : false;
		// }

		// if($USER->id == 2)
		// {
		// 	$U = service_db_select
		// 	(
		// 		"SELECT id, firstname, lastname FROM info_user WHERE id = :id",
		// 		['id' => $P['rid']]
		// 	);
		// 	$P['responder'] = !empty($U) ? $U[0] : false;
		// }

		//	Fin :D
		service_end(Status::Success, $P);
	}
	else
		service_end(Status::Error, 'Se requiere nivel de administrador para realizar la operación');