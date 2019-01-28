<?php
	//	Headers
	@header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN'] );
	header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Access-Control-Allow-Methods: POST, GET, OPTIONS');

	//	Nucleo
	require_once "core/enum.php";
	require_once "core/functions.php";
	require_once "core/slib.php";
	require_once "core/options.php";
	
	define( 'USING', str_replace( '/', '', service_get_param('using') ) );
	define( 'MAKE', str_replace( '/', '', service_get_param('make') ) );
	
	SaveLog('log.tmp.csv');
	
	if( file_exists( './' . USING . '/' . MAKE . '.php' ) )
		require_once './' . USING . '/' . MAKE . '.php';

	if(!defined('INC')) // en caso de que se llame desde consulta
		service_end(Status::Warning, '♪ And -someone- dared disturb the sound of silence. ♪'); 