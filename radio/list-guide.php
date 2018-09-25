<?php
	
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	//	Sacamos los parametros
	$s = service_match_param('s'); //	Busqueda
	$last = service_match_param('last');
	$last = $last > -1 ? $last : 0;
	
	//	Tablas a utilizar
	$tab_guide = "radio_guide";
	$tab_gh = "radio_guide_announcers";
	$tab_h = "radio_announcers";

	$q = 
	"SELECT 
		g.*,
		hs.announcers
	FROM 
		$tab_guide g
		LEFT JOIN (
			SELECT 
				gh.gid, 
				GROUP_CONCAT(h.alias ORDER BY h.alias ASC SEPARATOR ';') announcers

			FROM 
				$tab_gh gh 
				LEFT JOIN $tab_h h ON h.id = gh.hid
			GROUP BY 
				gh.gid
		) hs ON gid = g.id
	LIMIT 99999";
	
	$G = service_db_select($q);

	//$G = get_prepared_query($q, $p);
	//service_end(Status::Success, $G);

	//	Lo pesado...
	for($i = 0; $i < count($G); $i++){
		$G[$i]['announcers'] = array_filter( explode(";", $G[$i]['announcers']) );
		$G[$i]['days'] = unserialize($G[$i]['days']);
	}
	service_end(Status::Success, $G);
