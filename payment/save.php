<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	//	Solo admin puede accesar aqui
	if( USER_LEVEL >= UserType::Admin && CanDo('payment') )
	{
		//	Sacamos el json
		$data = json_decode(
			service_get_param('data'),
			true
		);
		
		//	Alistamos los parametros
		$paid = $data['cid'] === 'ME' || $data['cid'] === -1;
		$at = time();
		$id = $data['id'];
		$user = $data['user'];

		$params['payment'] = @$data['payment'] ?: '';
		$params['concept'] = @$data['concept'] ?: 'Pago generico';
		$params['cid'] = 
			$paid 
				? $USER->uid 
				: ( $data['cid'] > 0 ? $data['cid'] : 0 );

		$params['cat'] = 
			$paid 
				? $at 
				: ( $data['cat'] > 0 ? $data['cat'] : 0);

		$params['status'] =
			$paid || $data['cid'] > 0 	#Si está pagado entonces
				? PayStatus::Paid 		#Estado Paid
				: PayStatus::Pending; 	#Si no, pendiente

		//	Iniciamos
		if($id > 0)	#update
		{
			//	Preparamos el query de actualización
			$query = "UPDATE services_payments SET
						payment = :payment, 
						concept = :concept,
						cid = :cid, 
						cat = :cat, 
						status = :status
					WHERE id = :id";

			//	Parametros por default
			$params['id'] = $id;

		}

		else	#insert
		{
			if( !($data['uid'] > 0) )	# en caso de que no venga el user
				service_end(Status::Error, 'No se puede crear un pago sin asignar un usuario');
			
			//	Entonces lo insertamos
			$query = 
			"INSERT INTO services_payments
			( uid, gid, at, ref, concept, amount, charge, payment, cid, cat, tid, hcid, hlevel, htype, status )
			VALUES
			( :uid, :gid, :at, :ref, :concept, :amount, :charge, :payment, :cid, :cat, :tid, :hcid, :hlevel, :htype, :status )";
			
			//	Agregamos parametros de primer acceso
			//	Parametros historicos de ficha
				$params['hcid'] = $user['cid'] ?: 0;	// Id del curso en que se crea la ficha
				$params['hlevel'] = $user['level'] ?: 0;// Id del nivel en que se crea la ficha
				$params['htype'] = $user['type'] ?: 0;	// Id del typo de usuario en que se crea la ficha

			$params['uid'] = $data['uid'];
			$params['gid'] = $USER->uid;
			$params['at'] = $at;
			$params['ref'] = @$data['ref'] ?: '[*** NOREF ***]';
			$params['amount'] = @$data['amount'] ?: 0;
			$params['charge'] = @$data['charge'] ?: 0;
			$params['tid'] = @$data['tid'] ?: 0;
		}
		
		//	Ejecutamos el UPDATE/INSERT		
		if(service_db_insert($query, $params))
		{
			//	Actualizamos el status, si es que era un aspirante...
			if( $params['status'] == PayStatus::Paid && $user['status'] == UserStatus::Applicant )
				service_db_insert(
					"UPDATE info_user SET status = :status WHERE id = :uid",
					['status' => UserStatus::Active, 'uid' => $data['uid']]
				);
			
			//	preparamos el retorno, en caso de que se valla a actualizar
			$return = $params;

			//	en caso de que no venga el id (se acaba de crear)
			if( !($id > 0) )
			{
				$return = service_db_select
				(
					"SELECT * FROM services_payments WHERE at = :at",
					['at' => $at]
				);
				$return = !empty($return) ? $return[0] : $params;
			}

			/*$return['debug'] = 
			[
				'params' => $params,
				'user' => $user
			];*/

			service_end(Status::Success, $return);
		}
		else
			service_end(Status::Warning, 'Error al guardar el registro: <br><i>' . service_db_error()[2] . "</i><br>Intente de nuevo en un momento.") ;
			
	}
	else
		service_end(Status::Error, 'Se requiere nivel de administrador para realizar la operación');