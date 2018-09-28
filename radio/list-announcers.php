<?php
	
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	//	Sacamos los parametros
	$s = service_match_param('s'); //	Busqueda
	$last = service_match_param('last');
	$last = $last > -1 ? $last : 0;
	
	$exclude = service_match_param('exclude');
	$exclude = explode(';',$exclude);
	$ex = "";
	foreach($exclude as $h)
		if($h > 0)
			$ex .= " AND h.id != " . $h . " ";
	
	//	Tablas a utilizar
	$tab_user = "info_user";
	$tab_course = "info_categories";
	$tab_announcers = "radio_announcers";
	$tab_pics = "info_user_pictures";

	if(USER_LEVEL == UserType::Admin && CanDo('radio'))
	{
		$q = 
		"SELECT 
			h.id,
			h.alias,
			u.firstname,
			u.lastname,
			IF(c.name IS NULL, '', c.name) course,
			u.level,
			h.status,
			p.filename,
			u.id AS uid,

			h.alias link_title,
			CONCAT_WS(' ', u.firstname, u.lastname) link_subtitle,
			IF(c.name IS NULL, IF(u.type = 4,'Administrador', IF(u.type = 3, 'Docente', '(Desconocido)')) , c.name) link_body,
			p.filename link_imgurl
		FROM 
			$tab_announcers h
			LEFT JOIN $tab_user u ON u.id = h.uid
			LEFT JOIN $tab_pics p ON p.id = u.pid
			LEFT JOIN $tab_course c ON c.id = u.cid
		WHERE 1 ".
			(!empty($s) ? ' AND CONCAT_WS(" ", h.alias, u.firstname, u.lastname, c.name ) LIKE :s ' : '').
			$ex .
		"ORDER BY 
			h.alias DESC
		LIMIT 
			:last, 5";
		
		$p = [ 'last' => $last ];
		if(!empty($s))
			$p['s'] = '%'.$s.'%';

		$H = service_db_select($q, $p);

		//$H = get_prepared_query($q, $p);

		service_end(Status::Success, $H);
	}
	service_end(Status::Error, "No tienes autorización para ver la lista de locutores completa.");