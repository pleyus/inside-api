<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	//	Sacamos el type
	$type = service_match_param('type');
	$id = service_match_param('id');
	
	//	Solo las instituciones pueden sacar el get, porque tienen vinculo a otra base
	if( $type == 'institution' && $id > 0 )
	{
		$query = "SELECT * FROM info_institutions WHERE id = :id";
		$params = ['id' => $id];
		$I = service_db_select($query, $params);
		if(!empty($I))
		{
			$I = $I[0];
			$S = null;
			if($I['lid'])
			{
				$query = "SELECT *,
					asentamiento link_title,
					CONCAT(municipio, ', ', estado) link_subtitle,
					CONCAT('CP ', cp) link_body,
					'' link_imgurl
				 FROM info_sepomex WHERE id = :lid";
				$params = ['lid' => $I['lid']];
				$S = service_db_select($query, $params);
				$S = empty($S) ? null : $S[0];
			}
			$I['location'] = $S;
		}
		service_end(Status::Success, $I);
	}
	service_end(Status::Error, "No se pudo cargar la institucion solicitada");