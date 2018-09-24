<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	$user = service_match_param('user');
	$pass = service_match_param('pass');

	if( auth_user_login($user, $pass) )
		service_end(Status::Success, 'Se ha iniciado su sesión');
	
	service_end(Status::Error, 'Datos incorrectos');
