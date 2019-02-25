<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();
	
	$db_table = "services_contact";

	#
	# 	Codigo de la API
	#
	if( service_get_param('send', Param::Post) )
	{
		# 	Revisamos si ya han enviado un mensaje
		$contact_sent = service_get_param( 'contact_sent', Param::Cookie );
		if( !empty( $contact_sent ) )
		{
			setcookie('contact_sent', time(), time() + (60*5) );
			service_end( Status::Warning, "Ya ha enviado un mensaje, espere 5 minutos" );
		}


		# 	Obtenemos los datos
		$firstname 	= service_get_param('firstname', Param::Post); #
		$lastname 	= service_get_param('lastname', Param::Post);  
		$phone 		= service_get_param('phone', Param::Post, ''); 
		$email 		= service_get_param('email', Param::Post, ''); 
		$message 	= service_get_param('message', Param::Post);   #
		$ip 		= service_get_ip();

		# 	Revisamos que vengan los importantes
		if( !empty( $firstname ) && !empty( $message ) )
		{
			# 	Insertamos el registro
			$resp = service_db_insert(

				# Preparamos la query
				"INSERT INTO $db_table (at, firstname, lastname, phone, email, message, ip) VALUES (UNIX_TIMESTAMP(), :firstname, :lastname, :phone, :email, :message, :ip);",
				
				# Y pasamos los datos
				[
					'firstname' => $firstname,
					'lastname'  => $lastname,
					'phone' 	=> $phone,
					'email' 	=> $email,
					'message' 	=> $message,
					'ip'		=> $ip
				]);
			
			# 	En caso de que se registre el mensaje
			if( $resp )
			{
				# Guardamos una cookie que dura 5 minutos, con el fin de reducir el SPAM
				setcookie('contact_sent', time(), time() + _5MINS, '/' );

				# Guardamos la informacion del usuario por 3 dias, para facilitar su registro
				setcookie('user_firstname', $firstname, time() + _3DAYS, '/' );
				setcookie('user_lastname', $lastname, time() + _3DAYS, '/' );
				setcookie('user_phone', $phone, time() + _3DAYS, '/' );
				setcookie('user_email', $email, time() + _3DAYS, '/' );

				# Terminamos
				service_end ( Status::Success, "Mensaje enviado, gracias por sus comentarios" );
			}
			else
				# Si no devuelve nada la consulta, quiere decir que algo salió mal
				service_end ( Status::Error, "Ups! Algo salió mal al enviar su mensaje. Intente reenviarlo en unos momentos, si el problema persiste utilice alguno de nuestros otros medios de contacto" );
		}
		else
		{
			service_end( Status::Warning, "Los campos marcados con asterisco son requeridos" );
		}
	}
	service_end(Status::Error, "Utilice el formulario para enviar su mensaje");