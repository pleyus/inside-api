<?php
  //	Terminamos para que no se abra sin login...
	if (!defined('MAKE')) die();

	$id = service_match_param('id');

	if (USER_LEVEL == UserType::Admin && $id > 0) {
		$dir = $_SERVER['DOCUMENT_ROOT'];

		//  Sacamos los datos del archivo
		$q = "SELECT * FROM inside_file WHERE id = :id";
		$p = ['id' => $id];
		$r = service_db_select($q, $p);

		//  Si hay datos
		if (!empty($r)) {
			//  Sacamos solo el primer elemento como archivo (f)  
			$f = $r[0];

			//  Cambiamos el host del archivo por una diagonal
			$url = str_replace($_INSIDE_CONFIG['hosts'], '/', $f['url']);

			//  Borramos el archivo de la base de datos
			$q = "DELETE FROM inside_file WHERE id = :id";
			if (service_db_insert($q, $p)) {

				//  Si la url tenia el host, borramos el archivo
				if ( file_exists($dir . $url ))
					@unlink($dir . $url);

				service_end(Status::Success, 'Archivo eliminado');
			}
			service_end(Status::Error, 'No se puede elimniar el archivo seleccionado');
		}
	} else
	service_end(Status::Error, 'Se requiere permisos de administrador para eliminar un archivo');
