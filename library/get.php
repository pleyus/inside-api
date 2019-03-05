<?php

	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	//	Tablas utilizadas
	
	$tab_book = "inside_library";
	$tab_author = "inside_library_autors";

	//	Sacamos el id
	$id = service_match_param('id');

	if( CanDo('library') ){
		//	Si es un id valido
		if($id > 0)
		{
			//	Sacamos la info del programa
			$q = "SELECT
			b.id,
			a.firstname,
			a.lastname,
			e.name,
			b.title,
			b.subtitle,
			c.id AS 'cid',
			c.name AS 'category',
			b.isbn,
			b.summary,
			b.file,
			b.year,
			b.pages,
			b.status,
			b.type,
			b.at
			FROM 
			inside_library b
			LEFT JOIN inside_library_autors a ON a.id = b.aid
			LEFT JOIN inside_library_editorial e ON e.id = b.eid
			LEFT JOIN info_categories c ON c.id = b.cid  WHERE b.id = :id";
			$p = ['id' => $id];
			//echo $q;
			$r = service_db_select($q, $p);
			
			//service_end(Status::Success, $r)
			//	Si hay info
			
				$book = $r[0];
				$book['reg'] = unserialize($book['reg']);

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
				service_end(Status::Success, $book);
			
			service_end(Status::Error, 'No se encontró información para el programa solicitado.');
		}
		service_end(Status::Error, 'Para obtener la información de un programa se requiere el <b>id</b>.');
	}
	service_end(Status::Error, "No tienes autorización para cargar esta información.");