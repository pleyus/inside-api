<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	if( USER_LEVEL == UserType::Admin )
	{

		//	Agrega aqui el codigo a testear y luego ve a
		//	https://unitam.edu.mx/api/?using=general&make=debug
		//
		//	Solo los admins...


		service_end(Status::Success, $_FILES);
	}
	service_end(Status::Error, $CFG->session_file_save_path);