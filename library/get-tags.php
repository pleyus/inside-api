<?php

	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	//	Tablas utilizadas
	
	$tab_union = "inside_library_books_tags";
	$tab_tags = "inside_library_tags";

	//	Sacamos el id
	$id = service_match_param('id');

	if( CanDo('library') ){
		//	Si es un id valido
		if($id > 0)
		{
			//	Sacamos la info del programa
			$q = "SELECT u.id,t.name 
			FROM $tab_union u 
			LEFT JOIN $tab_tags t ON u.id = t.id 
			WHERE bid = :id";
			$p = ['id' => $id];
			//echo $q;
			$r = service_db_select($q, $p);
			
			//service_end(Status::Success, $r)
			//	Si hay info
			
				$tag = $r;
				$tag['reg'] = unserialize($tag['reg']);

				//	Ahora sacamos los announcers
				/*$q = 
					"SELECT
						* 
					FROM 
						$tab_author 
					WHERE 
						id = :gid";
				$p = ['gid' => $id];
				$r = service_db_select($q, $p);
				$book['author'] = !empty($r) ? $r : [];*/
				
				//$prog = get_prepared_query($q, $p);
				service_end(Status::Success, $tag);
			
			service_end(Status::Error, 'No se encontró información para el programa solicitado.');
		}
		service_end(Status::Error, 'Para obtener la información de un programa se requiere el <b>id</b>.');
	}
	service_end(Status::Error, "No tienes autorización para cargar esta información.");