<?php

	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	//	Tablas utilizadas
	$tab_users = "info_user";

	//	Sacamos los datos
	$data = json_decode(
		service_get_param('data', Param::Post),
		true
	);


	//	Solo admins...
	if( USER_LEVEL == UserType::Admin && CanDo('user') )
	{
		//	Si no vienen los datos basicos de las personas, salimos
		if( empty($data['email']) || empty($data['email']) || empty($data['email']) )

			// Termina como error
			service_end(Status::Error, 'Hacen falta algunos campos requeridos');
		

		// Sacamos el id. Si viene...
		$id = @$data['id'] > 0 ? $data['id'] : 0;
		$at = time();

		//	Preparamos las queries
		$user_update = "UPDATE $tab_users SET ";
		$user_preinsert = "INSERT INTO $tab_users (";
		$user_insert = ") VALUES (";
		$user_params = [];
		
		//	Strings
			if( $data['idnumber'] )
			{
				$user_update .= "idnumber = :idnumber, ";
				$user_insert .= ":idnumber, ";
				$user_preinsert .= "idnumber, ";
				$user_params['idnumber'] = $data['idnumber'];
			}
			if( $data['lastname'] )
			{
				$user_update .= "lastname = :lastname, ";
				$user_insert .= ":lastname, ";
				$user_preinsert .= "lastname, ";
				$user_params['lastname'] = $data['lastname'];
			}
			if( $data['address'] )
			{
				$user_update .= "address = :address, ";
				$user_insert .= ":address, ";
				$user_preinsert .= "address, ";
				$user_params['address'] = $data['address'];
			}
			// if( $data['tutor_phone'] )
			// {
			// 	$user_update .= "tutor_phone = :tutor_phone, ";
			// 	$user_insert .= ":tutor_phone, ";
			// 	$user_preinsert .= "tutor_phone, ";
			// 	$user_params['tutor_phone'] = $data['tutor_phone'];
			// }

		//	Ints
			if( isset($data['type']) ) 
			{
				$user_update .= "type = :type, ";
				$user_insert .= ":type, ";
				$user_preinsert .= "type, ";
				$user_params['type'] = $data['type'] > 0 ? $data['type'] : 0;
			}
			if( isset( $data[ sex ] ) ) 
			{
				$user_update .= "sex = :sex, ";
				$user_insert .= ":sex, ";
				$user_preinsert .= "sex, ";
				$user_params['sex'] = $data['sex'] > 0 ? $data['sex'] : 0;
			}
			if( $data['birthday'] !== 0 ) 
			{
				$user_update .= "birthday = :birthday, ";
				$user_insert .= ":birthday, ";
				$user_preinsert .= "birthday, ";
				$user_params['birthday'] = $data['birthday'];
			}
			if( $data['eid'] > 0 ) 
			{
				$user_update .= "eid = :eid, ";
				$user_insert .= ":eid, ";
				$user_preinsert .= "eid, ";
				$user_params['eid'] = $data['eid'];
			}
			if( $data['mid'] > 0 ) 
			{
				$user_update .= "mid = :mid, ";
				$user_insert .= ":mid, ";
				$user_preinsert .= "mid, ";
				$user_params['mid'] = $data['mid'];
			}
			if( $data['lid'] > 0 ) 
			{
				$user_update .= "lid = :lid, ";
				$user_insert .= ":lid, ";
				$user_preinsert .= "lid, ";
				$user_params['lid'] = $data['lid'];
			}
			
			$user_update .= "uid = :uid, ";
			$user_insert .= ":uid, ";
			$user_preinsert .= "uid, ";
			$user_params['uid'] = $data[ uid ] > 0 ? $data[ uid ] : 0;
			
			if( $data['iid'] > 0 ) 
			{
				$user_update .= "iid = :iid, ";
				$user_insert .= ":iid, ";
				$user_preinsert .= "iid, ";
				$user_params['iid'] = $data['iid'];
			}
			if( $data['cid'] > 0 ) 
			{
				$user_update .= "cid = :cid, ";
				$user_insert .= ":cid, ";
				$user_preinsert .= "cid, ";
				$user_params['cid'] = $data['cid'];
			}
			if( $data['level'] > 0 ) 
			{
				$user_update .= "level = :level, ";
				$user_insert .= ":level, ";
				$user_preinsert .= "level, ";
				$user_params['level'] = $data['level'];
			}
			if($id > 0)
			{
				$user_update .= "status = :status, ";
				$user_params['status'] = $data['status'];
			}
			

		//	Parametros requeridos
			$user_insert .= ":at, ";
			$user_preinsert .= "at, ";

			$user_update .= "email = :email, ";
			$user_insert .= ":email, ";
			$user_preinsert .= "email, ";
			$user_params['email'] = $data['email'];

			$user_update .= "personal_phone = :personal_phone, ";
			$user_insert .= ":personal_phone, ";
			$user_preinsert .= "personal_phone, ";
			$user_params['personal_phone'] = $data['personal_phone'];

			$user_update .= "firstname = :firstname ";
			$user_insert .= ":firstname );";  
			$user_preinsert .= "firstname ";  
			$user_params['firstname'] = $data['firstname'];

			$user_insert = $user_preinsert . $user_insert;

		//	Where for UPDATE
			$user_update .= " WHERE id = :id;";
			if($id > 0)
				$user_params['id'] = $id;
			else
				$user_params['at'] = $at;

		
		//	Ejecutamos la consulta, dependiendo si $id > 0 (si se esta actualizando) o no
		
		if( service_db_insert( ($id > 0 ? $user_update : $user_insert), $user_params) )
		{
			//	En caso de que se valla a mover algo de plataforma
			if($user_params['uid'] > 0)
			{
				$temp_uid = $user_params['uid'];
				$temp_status = $user_params['status'];
				$temp_status = ($temp_status == UserStatus::Active || $temp_status == UserStatus::Graduated)
					? 0
					: 1;
				$platform_query = "UPDATE mdl_user SET suspended = $temp_status WHERE id = $temp_uid;";
				service_db_insert($platform_query);
			}

			$U = ['id' => $id];
			//	Si se está insertando... (Para extraer los datos una vez creados)
			if($id < 1)
			{
				$q = "SELECT id AS uid, u.* FROM $tab_users u WHERE at = $at LIMIT 1;";
				$u = service_db_select($q);
				$U = !empty($u) ? $u[0] : $U;
			}

			//	Actualizamos los telefonos
			$phones = $data[ phones];
			if( !empty( $phones ) )
			{
				//	Primero eliminamos los numeros existentes...
				$q = "DELETE FROM info_user_phones WHERE uid = :uid";
				$p = [ uid => $U[ id ] ];
				service_db_insert($q, $p);

				//	Preparamos la query de inserción de los nuevos telefonos
				$q = "INSERT INTO info_user_phones (uid, name, phone) VALUES ";
				$p = [];
				for($i = 0; $i < count($phones); $i++)
				{
					$q .= '(:uid_' . $i . ', :name_' . $i . ', :phone_' . $i . '), ';
					$p['uid_' . $i] = $U[ id ];
					$p['name_' . $i] = $phones[$i]['name'];
					$p['phone_' . $i] = $phones[$i]['phone'];
				}
				$q = substr($q, 0, -2);
				service_db_insert($q, $p);
				// $U['debug'] = service_db_error()[2];
			}

			//	Actualizamos permisos
			if( !empty( $data['capabilities'] ) && $id > 0 )
			{
				$c = $data['capabilities'];
				$c =[
					uid => $id,
					payment => $c[ payment ] ? 1 : 0,
					_payment => $c[ payment ] ? 1 : 0,
					
					user => $c[ user ] ? 1 : 0,
					_user => $c[ user ] ? 1 : 0,
					
					applicants => $c[ applicants ] ? 1 : 0,
					_applicants => $c[ applicants ] ? 1 : 0,
					
					courses => $c[ courses ] ? 1 : 0,
					_courses => $c[ courses ] ? 1 : 0,
					
					vias => $c[ vias ] ? 1 : 0,
					_vias => $c[ vias ] ? 1 : 0,
					
					campaigns => $c[ campaigns ] ? 1 : 0,
					_campaigns => $c[ campaigns ] ? 1 : 0,
					
					institutions => $c[ institutions ] ? 1 : 0,
					_institutions => $c[ institutions ] ? 1 : 0,
					
					radio => $c[ radio ] ? 1 : 0,
					_radio => $c[ radio ] ? 1 : 0,
					
					docs => $c[ docs ] ? 1 : 0,
					_docs => $c[ docs ] ? 1 : 0,

					messages => $c[ messages ] ? 1 : 0,
					_messages => $c[ messages ] ? 1 : 0,

					feedback => $c[ feedback ] ? 1 : 0,
					_feedback => $c[ feedback ] ? 1 : 0
				];


				$q = "INSERT INTO 
					info_user_capabilities ( uid, payment, user, applicants, courses, vias, campaigns, institutions, radio, docs, messages, feedback) 
					VALUES(:uid, :payment, :user, :applicants, :courses, :vias, :campaigns, :institutions, :radio, :docs, :messages, :feedback ) 
					ON DUPLICATE KEY UPDATE
					payment = :_payment,
					user = :_user,
					applicants = :_applicants,
					courses = :_courses,
					vias = :_vias,
					campaigns = :_campaigns,
					institutions = :_institutions,
					radio = :_radio,
					docs = :_docs,
					messages = :_messages,
					feedback = :_feedback";
				
				service_db_insert($q, $c);
			}

			//	saccess madafacas!!
			service_end(Status::Success, $U);
		}
		else
			//	Flaqueó
			service_end(Status::Warning, 'No se pudo guardar el registro.' );
	}

	//	Si no quiere decir que se está creando un registro nuevo
	else
		service_end(Status::Error, 'Error de permisos');
		