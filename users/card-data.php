<?php
	
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	$action = service_match_param('action');
	
	$ids = GetIdsFromString( service_match_param('ids') );
	$ids = '(' . implode(',', $ids) . ')';

	if( USER_LEVEL == UserType::Admin && CanDo('user') )
	{
		$q = 
		"SELECT 
			u.firstname,
			u.lastname,
			IF(c.name IS NULL, IF(u.type = 4,'Administrativo', IF(u.type = 3, 'Docente', 'Usuario Web')) , 'Alumno') as type, 
			IF(c.name IS NULL, '', c.name) course,
			u.level,
			u.idnumber,
			p.filename
		FROM
			info_user u
			LEFT JOIN info_categories c ON c.id = u.cid
			LEFT JOIN info_user_pictures p ON p.id = u.pid
		WHERE
			u.id IN $ids
		LIMIT 9999";

		$r = service_db_select($q);

		//	Si nos devuelve usuarios
		if(!empty($r))
		{
			$root = $_SERVER['DOCUMENT_ROOT'];
			$tmpdir = $root . '/tmp/';
			//	Checamos si existe el directorio temporal
			if( !is_dir( $tmpdir ) )
			{
				mkdir($tmpdir);
				file_put_contents( $tmpdir . 'index.php', '' );
			}


			$zip = new ZipArchive();
			$filename = time() . '.zip';
			if ( $zip->open( $tmpdir . $filename, ZipArchive::CREATE ) !== true )
				service_end(Status::Error, "No se puede crear el archivo comprimido");


			$csv = "idnumber;code;firstname;lastname;type;course;pics;e;s" . PHP_EOL;

			foreach( $r as $val )
			{
				$course = $val['course'];
				$course = strpos($course, '(') > -1
					? substr($course,0, strpos($course, '('))
					: $course;
				$csv .= 
					$val['idnumber'] . ';' . 
					'*' . $val['idnumber'] . '*;' . 
					$val['firstname'] . ';' . 
					$val['lastname'] . ';' . 
					$val['type'] . ';' . 
					$course . ';' . 
					'pics/' . $val['filename'] . ';' . 
					(strpos($val['course'], '(') > -1 ? 'false' : 'true' ) . ';' . 
					(strpos($val['course'], '(') > -1 ? 'true' : 'false' ) . ';' . 
					PHP_EOL;
				$zip->addFile($root . '/uploads/users/' . $val['filename'], 'pics/' . $val['filename']);
			}

			$zip->addFromString("info.csv", $csv);
			$zip->close();
			service_end(Status::Success, '/tmp/' . $filename);
		}
		service_end(Status::Warning, 'No se encontraron usuarios disponibles para operar.');
	}

	service_end(Status::Error, 'No tienes permisos para usar este modulo');
