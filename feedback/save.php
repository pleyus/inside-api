<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();


	//	Sacamos el json
	$brute = service_get_param('data', Param::Post);
	
	//	Parseamos el json a Array
	$data = json_decode($brute, true); //<-- Ese true lo hace en array!!! <3
	

	//	Vemos si es alguien que envia un comment (algun NoAdmin)
	if( ( USER_LEVEL != UserType::Admin || !CanDo('feedback') ) && $info_user['status'] == UserStatus::Active )
	{
		//	Checamos el comentario...
		if( empty( @$data['comment'] ) )
			service_end(Status::Error, 'Se requiere el comentario que vas a hacer');


		//	Verificamos que el origen no esté baneado
		$crypt = strrev( md5( $info_user[ id ] ) );	//	La firma del id xD

		//	Consulta:
		$q = "SELECT id FROM info_feedback WHERE (uid = :uid || crypt = :crypt) AND status = :status";
		$p = 
		[ 
			crypt => $crypt, 
			uid => $info_user['id'], 
			status => FeedStatus::Banned 
		];
		//	Baneado = Si hay mas de 2 mensajes marcados como inapropiados
		$banned = service_db_select($q, $p);
		$banned = !empty($banned) ? count($banned) > 2 : false;

		//	Si está baneado... AUS!
		if($banned)
			service_end(Status::Warning, "Lo sentimos, pero el modulo ya no esta habilitado.");

		

		//	Preparamos los parametros generales
		$p = 
		[
			'at' => time(),
			'comment' => $data['comment'],
			'hide' => $data[hide] > 0 ? 1 : 0,
		];

		//	Si se esta ocultando todo:
		if( $data[ ghost ] > 0 && $data[ hide ] > 0 )
		{
			$q = "INSERT INTO info_feedback 
			(uid, crypt, at, comment, hide, response, rid, rat, status) VALUES
			(0, " . $crypt . ", :at, :comment, :hide, '', 0, 0, 0)";
		}
		else 
		{
			$q = "INSERT INTO info_feedback
				( uid, crypt, at, comment, hide, response, rid, rat, status ) VALUES
				( :uid, NULL, :at, :comment, :hide, '', 0, 0, 0 )";
			$p['uid'] = $info_user[ id ] ?: 0;
		}
		
		if(service_db_insert($q, $p))
			service_end(Status::Success, true);
		else
			service_end(Status::Warning, 'Algo salió mal al momento de enviar el comentario.');
	}

	elseif(USER_LEVEL == UserType::Admin && CanDo('feedback') && $data[ id ] > 0)
	{
		if( empty( @$data['response'] ) )
			service_end(Status::Error, 'Se requiere una respuesta para el comentario');

		//	Prparamos el insert
		$query = 
			"UPDATE info_feedback SET
				response = :response, 
				rid = :rid, 
				rat = :rat
			WHERE id = :id";
		
		$params = 
		[
			'response' => $data['response'],
			'rid' => $info_user[ id ],
			'rat' => time(),
			'id' => $data['id']
		];
		
		if(service_db_insert($query, $params))
			service_end(Status::Success, $params);
		else
			service_end(Status::Warning, 'No se pudo guardar su respuesta<br> –' . service_db_error()[2]);
	}
	else
		service_end(Status::Error, 'No tiene autorización para realizar la operación, contacte a un administrador');