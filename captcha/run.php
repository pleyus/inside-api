<?php
	//	Teminamos para que funcione todo correctamente
	if ( !defined('MAKE') ) die();

	require_once "core/vendor/simple-php-captcha-master/simple-php-captcha.php";

	$captcha_params = [
		'min_length' => 4,
		'max_length' => 4,
		'characters' => '0123456789',
		'max_font_size' => 45,
		'min_font_size' => 40
	];

	$response = service_match_param('response'); 
	$reload = service_match_param('reload');

	$status = Status::Error;
	$data["message"] = "Error en captcha";

	///////////////////////////////////////////
	//  Si se verifica el captcha y es valido
		if( $response == service_get_param('captcha', Param::Session)['code'] )
		{
			$status = Status::Success;
			$data["message"] = "Captcha verificado";
			goto fin;
		}
	///////////////////////////////////////////
	//  Si se va a recargar el captcha
		elseif( $reload )
		{
			$status = Status::Success;
			$data["message"] = "Captcha actualizado";
			goto fin;
		}
	///////////////////////////////////////////
	//  Si el captcha es incorrecto
		elseif( !is_valid_captcha($response) )
		{
			$status = Status::Error;
			$data["message"] = "Captcha incorrecto";
			goto fin;
		}
	
		

	/********************************
	*
	*         Al terminar...
	*
	********************************/
	fin:
		//  Cargamos un nuevo captcha
		$_SESSION['captcha'] = simple_php_captcha( $captcha_params );

		//  Devolvemos el mensaje y el nuevo captcha
		$data["code"] = $_SESSION['captcha']['image_src'];
		service_end($status, $data);