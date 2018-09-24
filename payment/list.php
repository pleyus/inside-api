<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	$last = service_get_param('last', Param::Post);
	$filter_status = @$_POST['filter_status'];
	
	$search = service_get_param('search', Param::Post);
	$search = str_replace(' ', '%', $search);


	$ORDERS = [ 'p.id', 'p.at', 'u.firstname', 'u.lastname', 'p.concept', 'p.ref', 'amount' ];
	$order = service_match_param('order'); // Asc Desc
	$order_by = service_match_param('order_by'); // Column

	$order = $order == 'ASC' ? 'ASC' : 'DESC';
	if( !in_array($order_by, $ORDERS, true) ){
		$order = 'DESC';
		$order_by = 'at';
	}

	$the_order = $order_by . ' ' . $order;


	if( ( USER_LEVEL == UserType::Admin && CanDo('payment') ) || USER_LEVEL == UserType::Student )
	{
		//	Preparamos los parametros
		$params = ['last' => $last];
		if( !empty($search) )
			$params['like'] = '%' . $search . '%';
		
		if( !( $filter_status < 0 ) )
			$params['filter_status'] = $filter_status;

		//	Preparamos la consulta
		$query = "SELECT 
				p.*,
				p.amount import,
				-- p.id id,
				-- p.uid uid,
				-- p.at at,
				CONCAT(u.firstname, ' ', u.lastname) name,
				u.firstname,
				u.lastname,
				-- p.concept concept,
				-- p.ref ref,
				(p.amount + p.charge) amount,
				-- p.status,
				FALSE AS checked,
				IF(c.name IS NULL, IF(u.type = 4,'Administrador', IF(u.type = 3, 'Docente', '(Desconocido)')) , c.name) hcname
			FROM 
				services_payments p
				LEFT JOIN info_user u ON u.id = p.uid
				LEFT JOIN info_categories c ON c.id = p.hcid

			WHERE 1 AND " . 
				(USER_LEVEL == UserType::Student ? ' p.uid = ' . $USER->uid . ' AND ' : '') . 
				
				( 
					!empty($search) 
						? " CONCAT_WS(' ', " . (
							USER_LEVEL != UserType::Student 
								? ' p.id, p.amount, u.idnumber, u.firstname, u.lastname, p.concept, p.ref, p.id, p.amount, u.idnumber, u.firstname, u.lastname, '
								: '') . 
							"p.concept, p.ref) LIKE :like AND " 
						: '' 
				) . 
				
				" p.status != " . PayStatus::Deleted .

				(
					USER_LEVEL == UserType::Student
					? " AND p.status = " . PayStatus::Paid
					: ( !( $filter_status < 0 ) ? " AND p.status = :filter_status" : '')
				) .
			" ORDER BY " . $the_order . " LIMIT :last, 10"; 

		// Buscamos el pago
		
		$P = service_db_select
		(
			$query,
			$params
		);
		
		
		//	Fin :D
		service_end(Status::Success, $P !== false ? $P : []);
	}
	else
		service_end(Status::Error, 'Lo sentimos, pero no tienes acceso a este modulo, por favor contacta con algun administrador para resolver tu situación');