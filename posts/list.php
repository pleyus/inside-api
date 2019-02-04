<?php
    //	Terminamos para que no se abra sin login...
    if ( !defined('MAKE') ) die();

    $s = service_match_param('s');
    $last = service_match_param('last');

    if( USER_LEVEL >= UserType::Admin)
	{
        $q = "SELECT 
                p.*,
                i.url image_url,
                i.caption
            FROM inside_post p
            LEFT JOIN inside_post_images
            WHERE 1 "

            //  Si se está buscando algo
            . ( !empty($s) ? "AND CONCAT(p.title, p.content, i.caption, i.description) LIKE :s " : '')

            . "LIMIT :last, 10";
        
        // Agregamos los parametros
        $p = ['last' => $last]
        if(!empty($s)) $p['s' => $s];

        // Cargamos los resultados
        $r = service_db_select($q);

        service_end(Status::Success, $r);
    }
    else
		service_end(Status::Error, 'Se requiere nivel de administrador para realizar la operación');