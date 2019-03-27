<?php
	
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	$action = service_match_param('action');
	
	$ids = GetIdsFromString( service_match_param('ids') );
	
	if( empty($ids) )
		service_end( Status::Error, 'No hay usuarios seleccionados para descargar.' );

	$ids = '(' . implode(',', $ids) . ')';

	if( USER_LEVEL == UserType::Admin && CanDo('user') )
	{
		$q = 
		"SELECT 
			u.id,
			u.firstname,
			u.lastname,
			IF(c.name IS NULL, IF(u.type = 4,'Administrativo', IF(u.type = 3, 'Docente', 'General')) , 'Alumno') as type, 
			IF(c.name IS NULL, 'Interno', c.name) course,
			IF(c.param1 = 'e', 'true', 'false') e,
			IF(c.param1 = 's', 'true', 'false') s,
			u.level,
			u.idnumber,
			f.url
		FROM
			info_user u
			LEFT JOIN info_categories c ON c.id = u.cid
			LEFT JOIN inside_files f ON f.id = u.fid
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


			$csv = "idnumber	code	firstname	lastname	type	course	pics	e	s" . PHP_EOL;
			$errors = "";
			foreach( $r as $val )
			{
				if(is_file($root . '/uploads/users/' . $val['filename'])) {
					$course = $val['course'];
					$course = strpos($course, '(') > -1
						? substr($course,0, strpos($course, '('))
						: $course;
					$csv .= 
						$val['idnumber'] . '	' . 
						'*' . $val['idnumber'] . '*	' . 
						$val['firstname'] . '	' . 
						$val['lastname'] . '	' . 
						$val['type'] . '	' . 
						$course . '	' . 
						'pics/' . $val['filename'] . '	' . 
						($val['e'] == $val['s'] ? 'true' : $val['e']) . '	' . 
						$val['s'] .
						PHP_EOL;
					$zip->addFile($root . '/uploads/users/' . $val['filename'], 'pics/' . $val['filename']);
				}
				else
					$errors .= " – No hay imagen para " . $val['firstname'] . ' ' . $val['lastname'] . ' (id:' . $val['id'] . ')' . PHP_EOL;
			}

			$zip->addFromString("info.csv", $csv);

			if($errors != '')
				$zip->addFromString("errors.txt", 'Hubieron algunos problemas al crear los archivos:' . PHP_EOL . $errors);
			
				$zip->close();
			service_end(Status::Success, '/tmp/' . $filename);
		}
		service_end(Status::Warning, 'No se encontraron usuarios disponibles para descargar.');
	}

	service_end(Status::Error, 'No tienes permisos para usar este modulo');
