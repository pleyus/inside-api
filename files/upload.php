<?php
    //	Terminamos para que no se abra sin login...
    if ( !defined('MAKE') ) die();

    if (USER_LEVEL == UserType::Admin) {
        
        //	Checamos el directorio
		$dir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/";
		if(!is_dir($dir))
            mkdir($dir);
        
        //  Si no existe el archivo htaccess para proteger la ejecución en upload
        if( !file_exists($dir.'.htaccess') )
            //  Lo creamos
            file_put_contents($dir.'.htaccess', '<Files *.php> deny from all </Files>');

        //	Preparamos el archivo
        $tmp_name = $_FILES['fily']['tmp_name'];
        $org_name = $_FILES["fily"]["name"];
		$ext = '.' . array_pop( explode('.', $org_name) );	//	Sacamos la extensión
		$name = md5_file($tmp_name) . $ext;

        //  Movemos el archivo
        if(move_uploaded_file($tmp_name, $dir . $name)) {

            //  Preparamos la info
            $at = time();
            $filename = service_match_param('filename') | basename($org_name) | $at;
            $mime = finfo::file($dir . $name, FILEINFO_MIME_TYPE);
            $description = service_match_param('description') | '';
            
            //  Preparamos la query
            $query = "INSERT INTO inside_files (filename, description, at, url, mimetype, uid) VALUES (:filename, :description, :at, :url, :mimetype, :uid);";
            $params = [
                'filename' => $filename,
                'description' => $description,
                'at' => $at,
                'url' => $_INSIDE_CONF['host'] . '/uploads/' . $name,
                'mimetype' => $mimetype,
                'uid' => $info_user['id']
            ];
            if( service_db_insert($query, $params) )
                service_end(Status::Success, 'Subida exitosa.');
        }

        @unlink($dir . $name);
        service_end(Status::Error, 'No se pudo guardar el archivo.');
    }
    else
        service_end(Status::Error, 'Se requiere permiso de administrador para subir archivos');