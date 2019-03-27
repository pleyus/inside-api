<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	$counter = [
		Applicants => [],
		RadioMessages => [],
		Messages => [],
		Birthdays => [],
		Feedbacks => [],
		ApplicantsTracking => [],
		LoggedIn => $USER->id > 0
	];

	//	LoggedIn
	if(!$counter[LoggedIn])
		service_end( Status::Success, $counter );

	//	Applicants
		if( CanDo('applicants') )
		{
			$q = "SELECT a.id FROM inside_applicants a
				LEFT JOIN info_user u ON u.id = a.uid
				LEFT JOIN info_categories c ON c.id = u.cid
				LEFT JOIN inside_applicants_notes n ON n.aid = a.id
			WHERE n.id IS NULL AND u.status = 4";

			$counter[ Applicants ] = count( service_db_select($q) );

			//	Obtiene los aspirantes que llevan seguimiento actualmente
			$tai = service_match_param('tai');

			//	Borramos los trackings antiguos
			$q = "DELETE FROM inside_applicants_tracking WHERE at < (" . time() . " - 30)";
			service_db_insert($q);

			//	Borramos los del usuario
			$q = "DELETE FROM inside_applicants_tracking WHERE uid = :uid";
			$p = [ 'uid' => $info_user['id'] ];
			service_db_insert($q, $p);


			if($tai > 0) {
				
				//	Si se esta haciendo tracking...
				$q = "INSERT INTO inside_applicants_tracking (aid, uid, at) VALUES (:aid, :uid, " . time() . ");";
				$p['aid'] = $tai;

				service_db_insert($q, $p);
			}

			//	Devolvemos los trackings actuales
			$q = "SELECT * FROM inside_applicants_tracking";
			$counter[ ApplicantsTracking ] = service_db_select($q);
			
		}

	//	Feedbacks
		$lfs = service_match_param('lfs'); # Last Feedback Seen
		$q = "SELECT id FROM info_feedback WHERE id > :lfs AND status = 0";
		$p = [lfs => $lfs];

		if( CanDo('feedback') )
		{
			$q .= " AND (response = '' OR response IS NULL) ";
			$counter[Feedbacks] = count(service_db_select($q, $p));
		}
		else
		{
			$q .= " AND (response != '' OR response IS NOT NULL) AND uid = :uid ";
			$p[uid] = $info_user[ id ];
			$counter[Feedbacks] = count(service_db_select($q, $p));
		}

	//	Paymentses
		if( !CanDo('payment') )
		{
			$lpt = service_match_param('lpt'); #	Last pay time
			$q = "SELECT id FROM services_payments WHERE uid = :uid AND status = 1 AND at > :at";
			$p = [uid => $info_user[id], at => $lpt];
			$counter[ Payments ] = count(service_db_select($q, $p));
		}

	//	Mensajes de radio
		if( CanDo('radio') || ImAnnouncer() )
		{
			$lmt = service_match_param('lmt') | service_match_param('radio-lmt'); # Last Message Time
			$q = "SELECT id FROM radio_messages WHERE at > :lmt";
			$p = [ lmt => $lmt ];
			$counter[ RadioMessages ] = count(service_db_select($q, $p));
		}

	//	Mensajes
		if( CanDo('messages') )
		{
			$q = "SELECT id FROM services_contact WHERE seen_by IS NULL OR seen_by < 1";
			$counter[ Messages ] = count(service_db_select($q));
		}

	//	Cumpleaños... 1
		#	if( ERES_HUMANO ) 
			$q = "SELECT
				u.id, u.firstname, u.lastname, u.birthday, f.url, u.status,
				IF(c.name IS NULL, IF(u.type = 4,'Administrativo', IF(u.type = 3, 'Docente', '(Desconocido)')) , c.name) course, 
				u.level,
				YEAR(CURDATE()) - YEAR( FROM_UNIXTIME( u.birthday) ) AS age,
				UNIX_TIMESTAMP(DATE_ADD( FROM_UNIXTIME( u.birthday ), 
					INTERVAL YEAR( CURDATE() ) - YEAR( FROM_UNIXTIME( u.birthday ) )  + 
					IF(DAYOFYEAR(CURDATE()) > DAYOFYEAR(FROM_UNIXTIME( u.birthday )),1,0) YEAR )) at
			FROM 
				info_user u
				LEFT JOIN inside_files f ON f.id = u.fid
				LEFT JOIN info_categories c ON c.id = u.cid
			WHERE 
				u.birthday != 0 AND
				DATE_ADD(
					FROM_UNIXTIME(u.birthday), 
					INTERVAL YEAR( CURDATE() ) - YEAR( FROM_UNIXTIME(u.birthday) )  + 
						IF(DAYOFYEAR(CURDATE()) > DAYOFYEAR(FROM_UNIXTIME(u.birthday)),1,0) 
				YEAR ) 
					BETWEEN 
						DATE_FORMAT(CURDATE(), '%Y-%m-%d 00:00:00') 
					AND 
						DATE_ADD( DATE_FORMAT(CURDATE(), '%Y-%m-%d 23:59:59') , INTERVAL 7 DAY) ".
					(USER_LEVEL != UserType::Admin 
						? ' AND u.level = ' . $info_user[level] . ' AND u.cid = ' . $info_user[ cid ] . ' ' 
						: '').
					" ORDER BY at ASC";
			$counter[ Birthdays ] = service_db_select($q);
			//$counter[ DEBUG ] = service_db_error()[2];
		# endif;
	
	service_end( Status::Success, $counter );

