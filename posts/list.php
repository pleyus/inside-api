<?php
    //	Terminamos para que no se abra sin login...
    if ( !defined('MAKE') ) die();

    $s = service_match_param('s');
    $last = service_match_param('last');

    /**
     * Indica si se van a obtener solo los id externos de los posts
     * Esto tiene prioridad sobre calquier consulta y solo devuelve
     * una lista de ids,
     *  [
     *      id => 0,
     *      externid => '123123123123_123123123123'
     *  ]
     */
    $external = service_match_param('external');
    if($external) {
        $q = "SELECT id, externid FROM inside_posts WHERE externid != '' OR externid IS NOT NULL LIMIT 9999";
        service_end(Status::Success, service_db_select($q));
    }

    //  Si el usuario es administrador...
    if( USER_LEVEL >= UserType::Admin)
	{
        $q = "SELECT 
                p.*,
                i.url image_url,
                i.filename
            FROM inside_post p
            LEFT JOIN inside_files i ON i.id = p.fid
            WHERE 1 "

            //  Si se está buscando algo
            . ( !empty($s) ? "AND CONCAT(p.title, p.content, i.filename, i.description) LIKE :s " : '')

            . "LIMIT :last, 10";
        
        // Agregamos los parametros
        $p = ['last' => $last];
        if(!empty($s)) $p['s'] = $s;

        // Cargamos los resultados
        $r = service_db_select($q);

        service_end(Status::Success, $r);
    }
    else
		service_end(Status::Error, 'Se requiere nivel de administrador para realizar la operación');