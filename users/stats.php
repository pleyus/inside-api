<?php
/*

SELECT
	u.id uid,
	CONCAT(u.firstname, ' ', u.lastname) nombre,
	A_FECHA(l.timecreated) as dia,
	count( A_FECHA(l.timecreated) ) 'events'
FROM
	mdl_user u 
	LEFT JOIN mdl_logstore_standard_log l ON u.id = l.userid

WHERE 
	u.id = 2 AND
    l.timecreated > UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY)

GROUP BY dia

ORDER BY l.timecreated ASC

-------------------------------------------------------

SELECT
	tmp.id _date,
	tmp.dates as day,
	count( A_FECHA(l.timecreated) ) 'events'
FROM
	(
		SELECT 
			CONCAT( YEAR( FROM_UNIXTIME(timecreated) ), LPAD( MONTH( FROM_UNIXTIME(timecreated) ), 2, '0' ), LPAD(DAY( FROM_UNIXTIME(timecreated) ), 2, '0' )) id,
			A_FECHA(timecreated) dates
		FROM 
				mdl_logstore_standard_log 
		WHERE 
				timecreated > UNIX_TIMESTAMP(CURDATE() - INTERVAL 6 DAY)
		GROUP BY dates ASC
    ) tmp
	LEFT JOIN mdl_logstore_standard_log l ON 
    	l.timecreated > UNIX_TIMESTAMP(CURDATE() - INTERVAL 6 DAY) AND 
        A_FECHA(l.timecreated) = tmp.dates AND
        l.userid = 2
        
	
GROUP BY day

ORDER BY l.timecreated ASC

*/

	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) 
		die();

	//	Sacamos el id del usuario para mayor comodidad
	$_id = $USER->id;

	// Verificamos que el id pertenesca a una persona real (no al guest o a -1)
	if( $_id < 2 )
		service_end(Status::Error, 'Se necesita una cuenta para acceder aqui');

	//	Si somos administradores podremos manejar cualquier id
	if( USER_LEVEL >= UserType::Admin )
	{	
		//	El id va a ser igual a lo que nos envien en parametro get o post, dependiendo
		$id = service_get_param('id', Param::Get);
		$id = $id ?: service_get_param('id', Param::Post);
	}
	
	//	Si no viene nada de parametro, pues usamos el propio
	$id = $id ?: $_id;

	//	Verificamos que no nos traten de pinchar via id
	if( !ctype_digit($id) )
		service_end(Status::Error, 'No se pueden obtener las estadisticas de ' . $id);

	//	Consultamos...
	$S = service_db_select
	(
		"CALL USER_STATS( :id )",
		['id' => $id]
	);
	
	if( empty($S) )
		service_end(Status::Warning, 'No se han encontrado datos para este id');

	service_end( Status::Success, $S);
