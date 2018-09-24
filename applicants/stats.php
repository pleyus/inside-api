<?php

	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	$since = service_match_param('since');
	$until = service_match_param('until');

	if( USER_LEVEL >= UserType::Admin && CanDo('applicants') )
	{
		$p = [ 'since' => $since . ' 00:00:00', 'until' => $until . ' 23:59:59' ];

		$interested = service_db_select(
			"SELECT
				IF(u.cid > 0, c.name, 'Sin especificar') course,
				COUNT(a.id) total,
				SUM(IF(u.status = 0 AND a.excluded = 0, 1, 0)) enrolled,
				SUM(IF(u.sex = 2, 1, 0)) sex_male,
				SUM(IF(u.sex = 1, 1, 0)) sex_female,
				SUM(IF(u.sex = 0, 1, 0)) sex_undefined
			FROM
				inside_applicants a
				LEFT JOIN info_user u ON u.id = a.uid
				LEFT JOIN info_categories c ON c.id = u.cid
				LEFT JOIN info_categories v ON v.id = a.via
			WHERE
				u.at >= UNIX_TIMESTAMP(:since) AND 
				u.at <= UNIX_TIMESTAMP(:until)
			GROUP BY
				c.id",
			$p
		);


		$towns = service_db_select(
			"SELECT
				IF( u.lid, s.municipio, 'Desconocido' ) municipios,
				COUNT(a.id) total,
				SUM(IF(u.status = 0 AND a.excluded = 0, 1, 0)) enrolled
			FROM
				inside_applicants a
				LEFT JOIN info_user u ON u.id = a.uid
				LEFT JOIN info_sepomex s ON s.id = u.lid
			WHERE
				u.at >= UNIX_TIMESTAMP(:since) AND 
				u.at <= UNIX_TIMESTAMP(:until)
			GROUP BY
				s.idMunicipio
			ORDER BY
				s.municipio
			LIMIT 40",
			$p
		);


		$vias = service_db_select(
			"SELECT
				IF(a.via > 0, c.name, 'Indefinida' ) via,
				COUNT(a.id) total,
				SUM(IF(u.status = 0 AND a.excluded = 0, 1, 0)) enrolled,
				SUM(IF(a.excluded = 1, 1, 0)) excluded,
				SUM(IF(u.status = 2, 1, 0)) retired,
				SUM(IF(u.status = 4 AND a.excluded = 0, 1, 0)) applicants
			FROM
				inside_applicants a
				LEFT JOIN info_user u ON u.id = a.uid
				LEFT JOIN info_categories c ON c.id = a.via
			WHERE
				u.at >= UNIX_TIMESTAMP(:since) AND 
					u.at <= UNIX_TIMESTAMP(:until)
				GROUP BY
					c.id
				ORDER BY
					c.name", $p
		);
		$register = service_db_select(
			"SELECT
				YEAR( FROM_UNIXTIME(u.at)) as 'year',
				MONTHNAME(FROM_UNIXTIME(u.at)) month,
				COUNT(a.id) total,
				#status
				SUM(IF(u.status = 0 AND a.excluded = 0, 1, 0)) enrolled,
				SUM(IF(a.excluded = 1, 1, 0)) excluded,
				SUM(IF(u.status = 2, 1, 0)) retired,
				SUM(IF(u.status = 4 AND a.excluded = 0, 1, 0)) applicants
			FROM
				inside_applicants a
				LEFT JOIN info_user u ON u.id = a.uid
			WHERE
				u.at >= UNIX_TIMESTAMP(:since) AND
				u.at <= UNIX_TIMESTAMP(:until)
			GROUP BY
				YEAR(FROM_UNIXTIME(u.at)), 
				MONTH(FROM_UNIXTIME(u.at))
			ORDER BY
				'year' ASC,
				MONTH(FROM_UNIXTIME(u.at)) ASC
			LIMIT 40", $p
		);

		InsideLog(Actions::View, Module::ApplicantsStats);
		
		service_end(Status::Success, [
			'interested' => $interested,
			'towns' => $towns,
			'register' => $register,
			'vias' => $vias
		]);
	}
	else{
		InsideLog(Actions::TryView, Module::ApplicantsStats);
		service_end(Status::Error, 'Se requiere nivel de administrador para realizar la operación');
	}