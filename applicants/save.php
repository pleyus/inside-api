<?php
	
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	//	Tablas utilizadas
	$tab_users = "info_user";
	$tab_applicants = "inside_applicants";
	
	//	Campaña de promocion
	$campaign = service_get_param('campaign', Param::Cookie, 0);
	$campaign = get_category_by_slug( $campaign );
	$campaign = $campaign ? $campaign['id'] : 0;

	//	Parseamos el json a Array
	$data = service_match_param('data');
	$data = json_decode($data, true);
	$U = $data[ user ];

	$landing = !empty( $data['send'] );
	$contactId = service_match_param('contact-id');

	//	La fecha y hora actual
	$at = time();

	$error_required = '';
		if( empty( $U[ firstname ] ) )
			$error_required .= ' – Nombre<br>';
		if( empty( $U[ personal_phone ] ) )
			$error_required .= ' – Teléfono<br>';
		if( empty( $U[ email ] ) )
			$error_required .= ' – Email<br>';
			
		if($error_required !== '' && !$landing)
			service_end(Status::Warning, 'Algunos campos son necesarios:<br>');


	//	Si viene de landing...
	if( $landing )
	{
		# 	Revisamos si ya han enviado el form...
		$sent = service_get_param( 'reg_sent', Param::Cookie );
		if( !empty( $sent ) )
		{
			//	Termina la consulta
			setcookie('reg_sent', time(), _5MINS );
			service_end( Status::Warning, "Ya te haz registrado, espera 5 min." );
		}

		//	Revisamos los parametros requeridos
		if( empty( $data['firstname'] ) || empty( $data['personal_phone'] ) || empty( $data['email'] ) )
			service_end( Status::Warning, "Algunos parametros son requeridos: Nombre, Telefono y Email" );


		//	Query de inserción
		$user_query = 
		"INSERT INTO $tab_users 
		(
			idnumber, type, firstname, lastname, sex,
			address, eid, mid, lid,
			email, personal_phone,
			uid, rid, iid, cid, `level`, `at`, `status`
		) 
		VALUES 
		(
			:idnumber, :type, :firstname, :lastname, :sex,
			:address, :eid, :mid, :lid,
			:email, :personal_phone,
			:uid, 0, :iid, :cid, :level, :at, :status
		)";

		//	Parametros del query
		$user_params = 
		[
			'idnumber' => 'XXESP000',
			'type' => UserType::Student,

			'firstname' => $data['firstname'],
			'lastname' => @$data['lastname'] ?: '',
			'sex' => @$data['sex'] > 0 ? $data['sex'] : 0,
			
			'email' => $data['email'],
			'personal_phone' => $data['personal_phone'],
						
			'eid' => @$data['eid'] > 0 ? @$data['eid'] : 0,
			'mid' => @$data['mid'] > 0 ? @$data['mid'] : 0,
			'lid' => @$data['lid'] > 0 ? @$data['lid'] : 0,
			'address' => @$data['address'] ?: '',
			
			'uid' => @$data['uid'] > 0 ? @$data['uid'] : 0,
			'iid' => @$data['iid'] > 0 ? @$data['iid'] : 0,
			'cid' => @$data['cid'] > 0 ? @$data['cid'] : 0,
			'level' => 0,
			'at' => time(),
			'status' => UserStatus::Applicant
		];

		//	Insertamos los datos del usuario
		if( service_db_insert($user_query, $user_params) )
		{
			//	Conseguimos el id del usuario registrado con base a la fecha que pusimos
			$U = service_db_select("SELECT id FROM $tab_users WHERE at = " . $user_params['at'] . " LIMIT 1");

			//	Si hay datos, entonces quiere decir que lo encontro
			if( !empty($U) )
			{
				//	Sacamos su id y lo asignamos directamente
				$U = $U[0]['id'];
				
				//	Insertamos los detalles del aspirantes
				$applicant_query = 
					"INSERT INTO $tab_applicants
					( origin, via, uid, campaign )
					VALUES
					(
						:origin,
						:via,
						:uid,
						:campaign
					)";

				$applicant_params = 
				[
					'origin' => @$data['origin'] ?: '',
					'via' => @$data['via'] ?: 0,
					'uid' => $U,
					'campaign' => $campaign
				];

				# Registramos los datos del aspirante
				if( service_db_insert($applicant_query, $applicant_params) ){
					//	applicant id
						$q = "SELECT id FROM $tab_applicants WHERE uid = :uid;";
						$p = ['uid' => $U];
						$aid = service_db_select($q, $p);
						$aid = empty($aid) ? 0 : $aid[0]['id'];
					# Guardamos una cookie que dura 5 minutos, con el fin de reducir el SPAM
					setcookie('reg_sent', time(), time() + _5MINS, '/' );

					# Guardamos la informacion del usuario por 3 dias, para facilitar su registro
					setcookie('user_firstname', $user_params['firstname'], time() + _3DAYS, '/' );
					setcookie('user_lastname',  $user_params['lastname'],  time() + _3DAYS, '/' );
					setcookie('user_phone', 	$user_params['personal_phone'], 	time() + _3DAYS, '/' );
					setcookie('user_email', 	$user_params['email'], 	time() + _3DAYS, '/' );

					# Guardamos el nombre de la persona que se registró, por 3 dias parara que no lo vuelva a hacer...
					setcookie('firstname_registered', 	$user_params['firstname'], 	time() + _3DAYS, '/' );

					# Terminamos
					InsideLog(Actions::Create, Module::Applicants, $aid);
					service_end ( Status::Success, "Se completo tu registro, pronto nos pondremos en contacto contigo." );
				}
			}
			InsideLog(Actions::Create, Module::Applicants, 0, 'Registro Incompleto');
			service_end(Status::Warning, 'Tu información se guardó <i>parcialmente</i>, pronto nos pondremos en contacto contigo.' );
		}
		InsideLog(Actions::Create, Module::Applicants, 0, $data['firstname']);
		service_end(Status::Error, 'No se pudo registrar su información, intente de nuevo en un momento' );
	}
	//	Si se esta creando desde applicants con permisos
	elseif(USER_LEVEL == UserType::Admin && CanDo('applicants'))
	{
		//	Update
		if($data[ id ] > 0)
		{
			$q = 
				"UPDATE info_user SET
					firstname = :firstname,
					lastname = :lastname,
					personal_phone = :personal_phone,
					email = :email,
					cid = :cid,
					sex = :sex,
					iid = :iid,
					lid = :lid,
					address = :address
				WHERE id = :id";
			$p = 
			[
				firstname => $U[ firstname ],
				lastname => $U[ lastname ],
				personal_phone => $U[ personal_phone ],
				email => $U[ email ],
				cid => $U[ cid ],
				sex => $U[ sex ],
				iid => $U[ iid ],
				lid => $U[ lid ],
				address => $U[ address ],
				id => $U[ id ]
			];

			if(service_db_insert($q, $p))
			{
				$q = 
					"UPDATE inside_applicants SET
						via = :via,
						excluded = :excluded
					WHERE id = :id";
				$p = 
				[ 
					via => $data[via] > 0 ? $data[via] : 0,
					excluded => $data[excluded] > 0 ? $data[excluded] : 0,
					id => $data[ id ] 
				];
				
				if( service_db_insert( $q, $p ) ){
					InsideLog(Actions::Update, Module::Applicants, $data['id']);
					service_end(Status::Success, "Se actualizó correctamente el aplicante");
				}
			}
			InsideLog(Actions::TryUpdate, Module::Applicants, $data['id']);
			service_end(Status::Error, "No se pudo actualizar el aspirante<br><b>Error:</b> – " . service_db_error()[2]);
		}
		// Create
		else
		{
			$q = 
				"INSERT INTO info_user 
				(
					idnumber, type, firstname, lastname,
					sex, pid, birthday, address, lid,
					email, personal_phone,
					uid, rid, iid, cid, level, at, status
				)VALUES(
					'XXESP000', 2, :firstname, :lastname, 
					:sex, 0, 0, :address, :lid,
					:email, :personal_phone,
					0, :rid, :iid, :cid, 0, :at, 4
				)";
			$p = 
			[
				firstname => $U[ firstname ],
				lastname => $U[ lastname ],
				sex => $U[ sex ] ?: 0,
				personal_phone => $U[ personal_phone ],
				email => $U[ email ],
				cid => $U[ cid ],
				at => $at,
				address => $U[ address ],
				rid => $info_user[id],
				lid => $U[ lid ],
				iid => $U[ iid ]
			];

			#	Sí se pudo crear el usuario entonces...
			if(service_db_insert($q, $p))
			{
				#	Cargamos el id del usuario recien creado
				$q = "SELECT id FROM info_user WHERE at = :at";
				$p = [ at => $at ];
				$u = service_db_select($q, $p);

				#	Si viene la información entonces...
				if(!empty($u))
				{
					//	Sacamos el id del usuario
					$u = $u[0]['id'];
					$q = "INSERT INTO inside_applicants (uid, via, campaign, excluded) VALUES (:uid, :via, -1, :excluded)";
					$p = [
						uid => $u,
						via => $data[via] ?: 0,
						excluded => $data[excluded] > 0 ? $data[excluded] : 0
					];

					#	Guardamos los datos como applicant
					if(service_db_insert($q, $p))
					{
						#	Sacamo
						$q = "SELECT id FROM inside_applicants WHERE uid = :uid";
						$p = [ uid => $u ];
						$aid = service_db_select($q, $p);
						$aid = !empty( $aid ) ? $aid[0]['id'] : 0;

						#	Revisamos si es un aspirante que se crea ligado a un mensaje
						if( $contactId > 0)
						{
							#	Sacamos la info del mensaje para asignar a todos
							#	los mensajes que lleguen con el mismo numero o email
							$p = [ 'id' => $contactId ];
							$q = "SELECT * FROM services_contact WHERE id = :id";
							$contact = service_db_select($q, $p);
							if( !empty($contact) )
							{

								$email = $contact[0]['email'];
								$phone = $contact[0]['phone'];

								#	Actualizamos todos los que concuerden con el id, email o phone
								$q = "UPDATE services_contact SET aid = :aid WHERE id = :id OR email = :email OR phone = :phone";
								$p = [
									'email' => $email,
									'phone' => $phone,
									'id' => $contactId,
									'aid' => $aid
								];
								InsideLog(Actions::Create, Module::Applicants, $aid, 'Desde mensaje de contacto');
								service_db_insert($q, $p);
							}
						}
						else
							InsideLog(Actions::Create, Module::Applicants, ['aid' => $aid, 'uid' => $u]);

						#	Fin...
						service_end(Status::Success, ['aid' => $aid, 'uid' => $u]);
					}
					else
						service_db_insert("DELETE FROM info_user WHERE id = :id", [id => $u[id]]);
				}
			}
			InsideLog(Actions::TryCreate, Module::Applicants);
			service_end(Status::Error, "No se pudo registrar el aspirante<br><b>Error:</b> – " . service_db_error()[2]);
		}
	}
	else{
		InsideLog(Actions::TryCreate, Module::Applicants);
		service_end(Status::Error, 'No tiene privilegios suficientes para realizar la operación');
	}