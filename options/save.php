<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();
	
	$for = service_match_param('for');
	$new = service_match_param('config');
	$new = json_decode($config, true);

	//	Preparamos la configuración para la query
	$config = LoadOptions(-1);

	$qu = '';	//	Query para actualizar global
	$pu = [];
	$qp = ''; // 	Query para actualizar configuración de personal
	$pp = [];
	$qi = '';	//	Query para Insertar (nuevas configuraciones)
	$pi = [];


	$i = 0;
	foreach($new as $key => $val) {

		//	Si no existe la opcion nueva en la anterior, insertamos
		if( empty( $config[$key] ) ) {
			
			//	Query de inserción
			$qi = 
				'INSERT INTO inside_options (name, value, type, restricted) ' . 
				'VALUES (:name' . $i . ', :value' . $i . ', :type' . $i . ', :restricted' . $i . ');';

			$pi['name' . $i] 			= $val['name'];
			$pi['value' . $i] 			= $val['value'];
			$pi['type' . $i] 				= $val['type'];
			$pi['restricted' . $i] 	= $val['restricted'];
		} else {

			//	Query de global
			$qu = 
				'UPDATE inside_options SET ' . 
				', value = :value' . $i . 
				', type = :type' . $i . 
				', restricted = :restricted' . $i . 
				' WHERE id = :id' . $i . '; ';

			$pu['value' . $i] 			= $val['value'];
			$pu['type' . $i] 				= $val['type'];
			$pu['restricted' . $i] 	= $val['restricted'];
			$pu['id' . $i] 					= $val['id'];

			//	Query de usuario
			$qp = 'UPDATE inside_options SET value = :value' . $i . 
				' WHERE uid = ' . ($for > 0 ? $for : $info_user['id']) . 
				' AND oid = :oid' . $i . ';';

			$pp['value' . $i] = $val['value'];
			$pp['oid' . $i] = $val['id'];
		}
		$i++;
	}

	//	Si somos administradores y se va a guardar la configuración global
	if (USER_LEVEL >= UserType::Admin && $for == 'global') {
		$updated = service_db_insert($qu, $pu);
		$inserted = service_db_insert($qi, $pi);
	
		if($updated || $inserted)
			service_end(Status::Success, 'Se ha guardado la configuración');

		else
			service_end(Status::Error, 'No se pudo guardar la configuración. ERR:OPTIONS_SAVE');
	} 

	//	Si somos admins y vamos a guardar la configuración para un usuario ($for id > 0)
	elseif( USER_LEVEL >= UserType::Admin && $for > 0) {

		if( service_db_insert($qp, $pp) )
			service_end(Status::Success, 'Se guardó la configuración del usuario');
		else 
			service_end(Status::Error, 'No se guardó la configuración');
	}
	//	Si vamos a guardar la configuración para nosotros
	elseif( $for == 'me' ) {
		if( service_db_insert($qp, $pp) )
			service_end(Status::Success, 'Se actualizó tu configuración');
		else 
			service_end(Status::Error, 'Algo salió mal al guardar tu configuración');
	}
	
	//	Si nada se cumple... fin
	service_end(Status::Error, 'Hay un problema en el modulo, no se quien eres ni que quieres hacer');
