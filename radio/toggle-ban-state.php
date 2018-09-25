<?php
	
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	//	Sacamos los parametros
	$listid = service_match_param('listid');
	
	//	Tablas a utilizar
	$tab_messages = "radio_messages";
	
	if( (USER_LEVEL == UserType::Admin && CanDo('radio') ) || ImAnnouncer()){
		$q = 
		"UPDATE $tab_messages SET status = IF(status = 1, 0, 1) WHERE listid = :listid";

		$p = ['listid' => $listid ];

		$r = service_db_insert( $q, $p );
		if($r)
			service_end(Status::Success, "Se ha bloqueado/desbloqueado correctamente el emisor.");
		else
			service_end(Status::Warning, "Hubo un problema al intentar bloquear/desbloquear al emisor");
	}
	service_end(Status::Error, 'No tiene autorización para realizar la acción');
