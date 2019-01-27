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
	
	$ORDERS = [ 'u.idnumber', 'u.firstname', 'u.lastname', 'c.name', 'u.personal_phone', 'u.tutor_phone', 's.municipio', 'u.email' ];
	$order = service_match_param('order'); // Asc Desc
	$order_by = service_match_param('order_by'); // Column

	$order = $order == 'ASC' ? 'ASC' : 'DESC';
	if( !in_array($order_by, $ORDERS, true) ){
		$order = 'DESC';
		$order_by = 'idnumber';
	}

	$the_order = $order_by . ' ' . $order .
		($order_by == 'c.name' ? ', u.level ' . $order : '');

	
	//	Solo pueden obtenerlos los administradores
	if( USER_LEVEL == UserType::Admin && CanDo('user') )
	{
		$query = 
		"SELECT
			u.id,
			u.idnumber,
			u.firstname,
			u.lastname,
			u.email,
			u.birthday,
			u.pid,
			p.filename,
			u.type,
			u.sex,
			u.personal_phone,
			u.tutor_phone,
			u.uid,
			IF(u.mid > 0 OR u.lid > 0, true, false) located,
			c.name course,
			u.level,
			u.at,
			u.status,
			u.lid,
			s.asentamiento,
			s.municipio,
			s.estado,
			u.address,
			0 checked
		FROM 
			info_user u
			LEFT JOIN info_categories c ON c.id = u.cid
			LEFT JOIN info_user_pictures p ON p.id = u.pid
			LEFT JOIN info_sepomex s ON s.id = u.lid
		WHERE ";

		$params = [];
		if (!empty($ids))
		{
			$query .= ' u.id IN ' . $ids . " ORDER BY " . $the_order . " LIMIT 9999";
		} else {
			$query .= '1 ' . ( !empty($WQuery) ? $WQuery : '' ) .
					
				//	Agregamos la busqueda
				( 
					!empty( $s ) 
						? " AND CONCAT_WS(' ', 
							u.firstname, u.lastname, u.firstname,
							u.idnumber, c.name, u.level,
							u.email, u.personal_phone, u.tutor_phone, s.municipio, s.asentamiento) like :s"
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