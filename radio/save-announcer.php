<?php

	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	//	Tablas utilizadas 
	$TAnnouncer = "radio_announcers";

	//	Sacamos la info
	$H = json_decode( service_match_param('announcer'), true );

	if(USER_LEVEL == UserType::Admin && CanDo('radio'))
	{
		//	Si el alias viene vacio,
		if (empty ($H[alias]) ) {
			$q = "SELECT firstname FROM info_user WHERE id = :uid";
			$p = [ uid => $H[alias]];
			$r = service_db_select($q, $p);
			if(!empty( $r ))
				$H[alias] = $r[0]['firstname'];
		}
		
		//	Creamos un nuevo programa
		$q = "INSERT INTO $TAnnouncer (uid, alias, status) VALUES (:uid, :alias, 0)";
		$p = [
			uid => $H[uid], //	Es el que viene en el objeto announcer.uid, NO de user.uid
			alias => $H[alias]
		];
		
		if( service_db_insert($q, $p) )
			service_end(Status::Success, 'Locutor guardado!');
	}

	service_end(Status::Error, 'No se puede guardar el locutor');
	