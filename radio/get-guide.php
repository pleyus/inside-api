<?php

	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	//	Tablas utilizadas
	$tab_guide = "radio_guide";
	$tab_gannouncers = "radio_guide_announcers";
	$tab_announcers = "radio_announcers";
	$tab_users = "info_user";
	$tab_course = "info_categories";


	//	Sacamos el id
	$id = service_match_param('id');

	if( CanDo('radio') ){
		//	Si es un id valido
		if($id > 0)
		{
			//	Sacamos la info del programa
			$q = "SELECT * FROM $tab_guide WHERE id = :id";
			$p = ['id' => $id];
			$r = service_db_select($q, $p);
			
			//	Si hay info
			if(!empty($r))
			{
				$prog = $r[0];
				$prog['days'] = unserialize($prog['days']);

				//	Ahora sacamos los announcers
				$q = 
					"SELECT
						h.*,
						u.firstname,
						u.lastname,
						c.name course,
						u.level
					FROM 
						$tab_gannouncers gh
						LEFT JOIN $tab_announcers h ON h.id = gh.hid
						LEFT JOIN $tab_users u ON u.id = h.uid
						LEFT JOIN $tab_course c ON c.id = u.cid
					WHERE 
						gid = :gid";
				$p = ['gid' => $id];
				$r = service_db_select($q, $p);
				$prog['announcers'] = !empty($r) ? $r : [];
				
				//$prog = get_prepared_query($q, $p);
				service_end(Status::Success, $prog);
			}
			service_end(Status::Error, 'No se encontró información para el programa solicitado.');
		}
		service_end(Status::Error, 'Para obtener la información de un programa se requiere el <b>id</b>.');
	}
	service_end(Status::Error, "No tienes autorización para cargar esta información.");