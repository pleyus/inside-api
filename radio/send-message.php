<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	$tab_messages = "radio_messages";

	//	Captcha
	require_once "core/vendor/simple-php-captcha-master/simple-php-captcha.php";
	$captcha_params = [
		'min_length' => 4,
		'max_length' => 4,
		'characters' => '0123456789',
		'max_font_size' => 45,
		'min_font_size' => 40
	];
			
	//	Respuesta VS Code
	$response = service_match_param('response');
	$code = service_get_param('captcha', Param::Session)['code'];

	//	Creamos nueva captcha para devolver
	$_SESSION['captcha'] = simple_php_captcha( $captcha_params );

	//	Sacamos los datos que se estan intentando enviar
	$name = service_match_param('name');
	$message = service_match_param('message');
	$ip = service_get_ip(true);
	$at = time();
	$listid = service_match_param('token');

	//	Preparamos la respuesta erronea
	$data = [];
	$data['message'] = "El codigo del <i>captcha</i> es incorrecto|" . $ip;
	$data["code"] = $_SESSION['captcha']['image_src'];
	$status = Status::Error;

	if( strlen($listid) != 32 ){
		$data['message'] = "No se pudo enviar tu mensaje, actualiza la página e intentalo nuevamente.";
		$status = Status::Warning;
	}
	
	//	Revisamos si el captcha es correcto
	elseif($response == $code)
	{
		//	Primero, revisemos que la IP no este baneada
		$q = "SELECT id FROM $tab_messages WHERE status = :status AND listid = :listid";
		$p = ['status' => RegStatus::Banned, 'listid' => $listid];
		$r = service_db_select($q, $p);
		
		//	Si no se encuentra nada, quiere decir que esta disponible
		if(empty($r))
		{
			$q = "INSERT INTO $tab_messages (at, listid, name, message, ip, status) VALUES (:at, :listid, :name, :message, :ip, :status)";
			$p = [
				'at' => $at,
				'listid' => $listid,
				'name' => $name,
				'message' => $message,
				'ip' => $ip,
				'status' => RegStatus::Active
			];
			$r = service_db_insert($q, $p);
			if($r)
			{
				$status = Status::Success;
				$data['message'] = "¡Gracias! Tu mensaje se envió correctamente.";
			}
			else
				$data['message'] = "No se pudo enviar tu mensaje, intenta de nuevo en unos momentos.";
		}
		else
			$data['message'] = "Lamentablemente fuiste baneado, tu mensaje no fue enviado.";
	}

	//	Si no mostramos el mensaje y devolvemos nueva captcha
	service_end($status, $data);
	