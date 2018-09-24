<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	//	Sacamos el type
	$type = service_match_param('type');
	$last = service_match_param('last', 0);
	$s = service_match_param('s');

	# Orders
		$ORDERS = [ 'i.name', 's.asentamiento', 'i.phone1' ];
		$order = service_match_param('order'); // Asc Desc
		$order_by = service_match_param('order_by'); // Column

		$order = $order == 'ASC' ? 'ASC' : 'DESC';
		if( !in_array($order_by, $ORDERS, true) ){
			$order_by = 'i.id';
			$order = 'ASC';
		}

		$the_order = $order_by . ' ' . $order;

	//	Whitelist de las categorias disponibles en esta versión
	$type = 
		$type == 'vias'
		? CategoryType::Via
		: (
			$type == 'courses'
			? CategoryType::Course
			: (
				$type == 'campaigns'
				? CategoryType::Campaign
				: (
					$type == 'institution'
					? CategoryType::Institution
					: -1
				)
			)
		);

	if( $type == -1 )
		service_end( Status::Error, "Se esta intentando obtener una categoría no soportada por esta versión." );
	else
	{
		if($type == CategoryType::Institution)
		{
			$query = "SELECT
				i.*,
				s.asentamiento loc,
				s.municipio mun,
				s.estado est,

				i.name link_title,
				CONCAT(s.asentamiento, ', ', s.municipio) link_subtitle,
				i.director link_body,
				'' link_imgurl

			FROM 
				info_institutions i
				LEFT JOIN info_sepomex s on s.id = i.lid ";
			
			$params = [];
			if( !empty($s) )
			{
				$query .= " WHERE 
					CONCAT_WS(' ', i.name, i.phone1, i.director, s.asentamiento, i.name, i.phone1, i.director, s.asentamiento ) like :s ";
				$params = [ 's' => '%'. str_replace(' ', '%', $s) .'%'];
			}

			$query .= " ORDER BY " . $the_order . " LIMIT :last, 10 ";
			$params[ 'last' ] = $last;

			//service_end(1,get_prepared_query($query, $params));
			service_end(Status::Success, service_db_select($query, $params));
		}
		else
			service_end(Status::Success, service_db_select(
				"SELECT *
				FROM info_categories
				WHERE type = :type AND status = 0
				ORDER BY name ASC",
				['type' => $type ]
			));
	}