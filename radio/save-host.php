<?php

	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	//	Tablas utilizadas 
	$THost = "radio_hosts";

	//	Sacamos la info
	$H = json_decode( service_match_param('host'), true );

	if(USER_LEVEL == UserType::Admin && CanDo('radio'))
	{
		//	Creamos un nuevo programa
		$q = "INSERT INTO $THost (uid, alias, status) VALUES (:uid, :alias, 0)";
		$p = [
			uid => $H[uid], //	Es el que viene en el objeto host.uid, NO de user.uid
			alias => $H[alias]
		];
		
		if( service_db_insert($q, $p) )
			service_end(Status::Success, 'Hoster guardado!');
	}

	service_end(Status::Error, 'No se puede guardar el hoster');
	