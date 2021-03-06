<?php
	
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	$id = service_match_param('id', 0);
	
	$T_users = "info_user";
	$T_guide = "radio_guide";
	$T_announcers = "radio_announcers";
	$T_gh = "radio_guide_announcers";
	$T_pictures = "inside_files";
	$T_categories = "info_categories";
	$T_institutions = 'info_institutions';

	if( $USER->id < 2 ) // Si es que esta loggeado
		service_end(Status::Warning, 'Modulo protegido');

	//	Si no viene el id o si no es un administrador, 
	//	se retorna la información del usuario actual
	if( $id < 1 || USER_LEVEL != UserType::Admin  )
	{
		if($USER->id > 0)
			service_end(Status::Success, $info_user);
		else
			service_end(Status::Error, 'No puedes entrar al modulo, inicia sesión primero');
	}

	if( USER_LEVEL == UserType::Admin && ( CanDo('user') || CanDo('applicants') ) )
	{
		$U = GetUserInfo($id);
		//	End.
		service_end(Status::Success, $U);
	}
