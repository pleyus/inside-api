<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	$tab_users = "info_user";
	$tab_applicants = "inside_applicants";
	$tab_categories = "info_categories";
	$tab_notes = "inside_applicants_notes";
	
	$last = service_get_param('last', Param::Post);
	$last = $last > 0 ? $last : 0;

	$search = service_get_param('search', Param::Post);
	$search = str_replace(' ', '%', $search);
	
	$filter_type = service_get_param('filter_type', Param::Post);

	$period = service_match_param('period', 0);

	# Orders
		$ORDERS = [ 'u.at', 'u.firstname', 'u.lastname', 'n.at', 'n.note' ];
		$order = service_match_param('order'); // Asc Desc
		$order_by = service_match_param('order_by'); // Column

		$order = $order == 'ASC' ? 'ASC' : 'DESC';
		if( !in_array($order_by, $ORDERS, true) ){
			$order = '';
			$order_by = '';
		}

		$the_order = $order_by . ' ' . $order;
		

	//	Solo pueden obtenerlos los administradores
	if( USER_LEVEL >= UserType::Admin && CanDo('applicants') )
	{
		//	Ejecutamos la consulta
		$query =
			"SELECT
				a.campaign,
				a.id id, 
				a.uid,
				a.excluded,

				n.note,
				n.at note_at,
				
				c.name course,

				u.email email,
				u.firstname firstname,
				u.lastname lastname,
				u.at 'at',
				u.personal_phone personal_phone,
				u.tutor_phone tutor_phone,
				u.mid mid,
				u.status,
				u.type,

				'' as new_note,
				'0' as note_saved
			FROM 
				$tab_applicants a
				LEFT JOIN 
				(
					SELECT 
						x.id, 
						x.aid, 
						x.at, 
						x.note 
					FROM
						(SELECT n1.aid, MAX(n1.at) 'the_last' FROM $tab_notes n1 group by n1.aid) n
						LEFT JOIN $tab_notes x ON x.at = n.the_last
				) n ON n.aid = a.id
				LEFT JOIN $tab_users u ON u.id = a.uid
				LEFT JOIN $tab_categories c ON c.id = u.cid
			WHERE 1 ".
				($period > 0 ? ' AND YEAR( FROM_UNIXTIME(u.at) ) = :period ' : '').

				(!empty($search) 
					? " AND CONCAT_WS(' ', u.firstname, u.lastname, u.personal_phone, u.tutor_phone, n.note, c.name, u.idnumber, u.firstname, u.lastname, u.personal_phone, u.tutor_phone, n.note, c.name, u.idnumber) LIKE :search "
					: "").
				
					($filter_type == 'active' ? " AND u.status != " . UserStatus::Applicant : "").
				($filter_type == 'excluded' ? " AND a.excluded > 0 " : "").
				
				($filter_type == 'recent' ? " AND n.at >= " . ( time()-(_DAY*7) ) . " " : "").
				($filter_type == 'week' ? " AND ( n.at >= " . ( time()-(_DAY*15) ) .  " AND n.at < " . ( time()-(_DAY*7) ) . ") " : "").
				($filter_type == 'old' ? " AND n.at < " . ( time()-(_DAY*15)) . " " : "").

			" ORDER BY ".
				( 
					!empty($order_by) 
					? $order_by." ".$order
					: "a.excluded ASC, u.status DESC, n.at ASC, a.campaign DESC " 
				).
			" LIMIT 
				$last, 10";// . ($last > 0 ? '10' : '15');
		
		$params = [];

		if($period > 0)
			$params['period'] = $period;

		if( !empty($search) )
			$params['search'] = '%' . $search . '%';
		
		$result = service_db_select( $query, $params );
		InsideLog(Actions::View, Module::ApplicantsList);
		service_end(Status::Success, $result );
	}
	else{
		InsideLog(Actions::TryView, Module::ApplicantsList);
		service_end(Status::Error, 'Se requiere nivel de administrador para realizar la operación');
	}