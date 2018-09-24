<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();
	
	$s = service_match_param('s');
	$id = service_match_param('id');

	//	Si se esta buscando la localidad
	if( !empty($s) )
	{
		$s = str_replace(',', '%', str_replace(' ', '%', $s) );
	
		$q = "SELECT 
			*,

			asentamiento link_title,
			CONCAT(municipio, ', ', estado) link_subtitle,
			CONCAT('CP ', cp) link_body,
			'' link_imgulr
		FROM 
			info_sepomex
		WHERE 
			CONCAT_WS(' ', 
				tipo, asentamiento, cp,
				municipio, 
				estado, 
				municipio, 
				tipo, asentamiento, 
				cp) LIKE :s 
		ORDER BY 
			idEstado ASC 
		LIMIT 15";
		$p = [ ':s' => '%' . $s .'%' ];
		
		//echo get_prepared_query($q, $p); die();

		$L = service_db_select($q, $p);
		
		service_end(Status::Success, $L);
	}

	// Si no se esta buscando, checamos que nos esten pidiendo una locacion especifica
	elseif( $id > 0 )
	{
		$L = service_db_select( "SELECT * FROM info_sepomex WHERE id = :id", ['id' => $id] );
		if( !empty($L) )
			service_end(Status::Success, $L[0]);
		else
			sercice_end(Status::Warning, "No se encontró la locación solicitada (Locacion: ".$id);
	}