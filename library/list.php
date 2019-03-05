<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	$last = service_match_param('last');
	$last = $last > 0 ? $last : 0;
	
	$filter = service_match_param('filter');

	//	Sacamos los Ids solicitados (en caso de que vengan)
	$ids = GetIdsFromString( service_match_param('ids') );
	if(!empty($ids))
		$ids = '(' . implode(',', $ids) . ')';

	//	Sacamos los comodines
	$Wild = Wildcards( 
		service_match_param('s'), 
		'level, !level, control, !control, nivel, !nivel, ' .

		'[!pic], [!foto], [!confoto], ' .
		'[pic], [foto], [confoto], ' .

		'[felicidades], [hbd], [cumple], '.
		
		'[activos], [active],'.
		'[!activos], [!active],'.
		
		'[!plataforma], [!plat]'
	);
	$WQuery = "";
	$WParams = [];
	
	if( !empty($Wild) )
	{
		if($Wild[1]['!level'] !== null || $Wild[1]['!nivel'] !== null)
		{
			$WQuery .= " AND u.level != :level ";
			$WParams['level'] = $Wild[1]['!level'] | $Wild[1]['!nivel'];
		}
		elseif($Wild[1]['level'] !== null || $Wild[1]['nivel'] !== null)
		{
			$WQuery .= " AND u.level = :level ";
			$WParams['level'] = $Wild[1]['level'] | $Wild[1]['nivel'];
		}
		

		if($Wild[1]['!control'] !== null)
		{
			$WQuery .= " AND u.idnumber NOT LIKE :control ";
			$WParams['control'] = $Wild[1]['!control'] . '%';
		}
		elseif($Wild[1]['control'] !== null)
		{
			$WQuery .= " AND u.idnumber LIKE :control ";
			$WParams['control'] = $Wild[1]['control'] . '%';
		}
		
		if($Wild[1]['!pic'] || $Wild[1]['!foto'] || $Wild[1]['!confoto'])
			$WQuery .= " AND (u.pid IS NULL OR u.pid < 1) ";
		elseif($Wild[1]['pic'] || $Wild[1]['confoto'] || $Wild[1]['foto'])
			$WQuery .= " AND u.pid > 0 ";


		if($Wild[1]['!activos'] || $Wild[1]['!active'] || $Wild[1]['inactive'])
			$WQuery .= " AND u.status != 0 ";
		elseif($Wild[1]['activos'] || $Wild[1]['active'])
			$WQuery .= " AND u.status = 0 ";
		
		

		if($Wild[1]['!plat'] || $Wild[1]['!plataforma'])
			$WQuery .= " AND NOT (u.uid > 0) ";

		if($Wild[1]['felicidades'] !== null || $Wild[1]['hbd'] !== null || $Wild[1]['cumple'] !== null )
			$WQuery .= " AND DATE_FORMAT(FROM_UNIXTIME( birthday ), '%m%d') = DATE_FORMAT(NOW(), '%m%d') ";
		
		
	}
	
	//	Search
	$s = $Wild[0];
	
	$ORDERS = [ 'b.id', 'a.firstname', 'a.lastname', 'b.title', 'b.isbn', 'c.name' ];
	$order = service_match_param('order'); // Asc Desc
	$order_by = service_match_param('order_by'); // Column

	$order = $order == 'ASC' ? 'ASC' : 'DESC';
	if( !in_array($order_by, $ORDERS, true) ){
		$order = 'DESC';
		$order_by = 'b.isbn';
	}

	$the_order = $order_by . ' ' . $order .
		($order_by == 'b.title' ? ', b.isbn ' . $order : '');

	
	//	Solo pueden obtenerlos los administradores
	if( USER_LEVEL == UserType::Admin && CanDo('user') )
	{
		$query = 
		"SELECT
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
		LEFT JOIN info_categories c ON c.id = b.cid
		WHERE 1 ";
		
		$params = [];
		if(!empty($ids))
			$query .= ' AND b.id IN ' . $ids . " ORDER BY " . $the_order . " LIMIT 9999";
		else {
			$query .=
				//	Ponemos los Wildcards
				( !empty($WQuery) ? $WQuery : '' ) .
						
				//	Agregamos la busqueda
				( 
					!empty( $s ) 
						? " AND CONCAT_WS(' ', 
							b.isbn, a.firstname, a.lastname,
							b.title, b.subtitle, c.name) like :s"
						: '' 
				) .
				" ORDER BY " . $the_order .
				" LIMIT :last, 10";

			$params = array_merge(['last' => $last ], $WParams);
			
			if( !empty($s) )
				$params['s'] = '%' . $s . '%';

			//	Se agregan los filtros, solo si es mayor a -1 y si estan vacios los comodines
			if( $filter > -1 && empty($WParams))
				$params['filter'] = $filter;
		}

		$data = service_db_select( $query, $params );

		#service_end(Status::Success, [$data, get_prepared_query($query, $params), $query, $params, $_service_db_select_error]);
		service_end(Status::Success, $data);
	}
	else
		service_end(Status::Error, 'Se requiere autorización para realizar la operación');