<?php
	
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	//	Sacamos los parametros
	$last = service_match_param('last');
	$last = $last > -1 ? $last : 0;
	
	//	Parametro At, indica la fecha del mas reciente mensaje que se tiene abierto,
	//	Si llega este parametro, devolvemos todos los registros que haya despues de...
		$at = service_match_param('at');
	
	//	Tablas a utilizar
	$tab_messages = "radio_messages";
	
	if( ( USER_LEVEL == UserType::Admin && CanDo('radio') ) || ImAnnouncer()){
		//	Si se solicitan los recientes...
		if($at > 0)
		{
			$q = 
			"SELECT * FROM $tab_messages WHERE at > :at ORDER BY at DESC";

			$p = ['at' => $at ];
		}

		//	Si no procedemos de manera normal
		else
		{
			$q = 
			"SELECT * FROM $tab_messages
			ORDER BY 
				at DESC 
			LIMIT :last, 10";

			$p = ['last' => $last ];
		}

		$r = service_db_select( $q, $p );
		service_end(Status::Success, $r);
	}
	service_end(Status::Error, 'No tienes autorización para leer los mensajes.');
