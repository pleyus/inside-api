<?php
	#-----------------------------------------------
	#	Cargamos el nucleo de moodle para
	#	para usarlo como barrera
	#-----------------------------------------------

		//	Cambiamos la dirección del root de moodle
		$__NEW_ROOT = '/api';
	
		//	Llamamos a config.php de moodle
		require_once $_SERVER['DOCUMENT_ROOT'] . "/plataforma/config.php";
	
		//	Cargamos la conexión a la base de datos con los datos de moodle
		try 
		{ 
			$service_db = new PDO(
				"mysql:announcername=" . $CFG->dbannouncer . ";dbname=" . $CFG->dbname, 
				
				$CFG->dbuser,

				$CFG->dbpass, 
				
				[
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					PDO::ATTR_EMULATE_PREPARES => false,
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"
				]); 
		}
		catch (Exception $x) 
		{ 
			service_end(Status::Error, "No se ha podido conectar a la base de datos."); 
		}
	
		//	Define el tipo de usuario que esta actualmente accesando
		$info_user = GetUserInfo();
		define(
			'USER_LEVEL', 
			$info_user['id'] > 0	// Si el id es mayor a 0 (quiere decir que se consiguo la info, si no ya pelo)
				? (
					($info_user['status'] == UserStatus::Active || $info_user['status'] == UserStatus::Graduated)
					? $info_user['type'] 
					: UserType::Quest
				)
				: UserType::Quest
		);