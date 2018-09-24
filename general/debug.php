<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	echo '<pre>';
	
	if( USER_LEVEL == UserType::Admin )
	{
		print_r(SerializeIdNumber('#_18LCO###', [234,434,455,76,323,784]));
		// $limit = 2;

		// for($i = 1; $i< 1150;$i++)
		// 	echo $i . "\t|\t" .  str_pad($i, $limit, "0", STR_PAD_LEFT) . "\t|\t" . substr( str_pad($i, $limit, "0", STR_PAD_LEFT), -$limit) . PHP_EOL;
		
		die();	

	}