<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();
	//error_reporting(0);

	//	Tablas a utilizar
	$Tusers = "info_user";
	$Tcourse = "info_categories";
	$Tupics = "info_user_pictures";

	$s = service_match_param('s');
	$s = str_replace(' ', '%', $s);

	//	Para buscar usuarios en distintas tablas (relaciones)
	$in = service_match_param('in');

	//	Solo pueden obtenerlos los administradores
	if( USER_LEVEL == UserType::Admin )
	{
		//	Busqueda normal
		$query = "SELECT
			u.id, u.firstname, u.lastname, u.personal_phone, u.tutor_phone, u.email, u.idnumber, u.status, u.level, u.cid, u.type,
			
			CONCAT_WS(' ', u.firstname, u.lastname) link_title,
			IF(c.name IS NULL, IF(u.type = 4,'Administrador', IF(u.type = 3, 'Docente', '(Desconocido)')) , c.name) link_subtitle,
			'' link_body,
			p.filename link_imgurl


		FROM 
			info_user u
			LEFT JOIN info_user_pictures p ON p.id = u.pid
			LEFT JOIN info_categories c ON c.id = u.cid
			
		WHERE
			CONCAT_WS(' ', u.firstname, u.lastname, u.idnumber, u.email) like :s

		ORDER BY idnumber DESC
		LIMIT 5";

		if($in == 'radio.host')
			//	Busqueda de radio
			$query = "SELECT
				u.id, 
				u.firstname, 
				u.lastname,
				u.status,
				p.filename,
				c.name course,
				u.level,
				
				CONCAT_WS(' ', u.firstname, u.lastname) link_title,
				IF(c.name IS NULL, IF(u.type = 4,'Administrador', IF(u.type = 3, 'Docente', '(Desconocido)')) , c.name) link_subtitle,
				u.email link_body,
				p.filename link_imgulr
				
			FROM
				$Tusers u
				LEFT JOIN $Tupics p ON p.uid = u.id
				LEFT JOIN $Tcourse c ON c.id = u.cid
			WHERE
				CONCAT_WS(' ', u.firstname, u.lastname, u.idnumber, u.email) like :s
			ORDER BY 
				u.firstname DESC
			LIMIT 5";
		$params = ['s' => '%' . $s . '%'];

		$data = service_db_select($query,  $params);
		/*$data['debug'] = [
			'query' => $query,
			'params' => $params,
			'prepared' => get_prepared_query($query, $params)
		];*/
		service_end(Status::Success, $data);
	}
	else
		service_end(Status::Error, 'Se requiere nivel de administrador para realizar la operación');