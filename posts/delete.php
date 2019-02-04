<?php
    //	Terminamos para que no se abra sin login...
    if ( !defined('MAKE') ) die();

    $id = service_match_param('id');
    
    if( USER_LEVEL >= UserType::Admin && $id > 0)
	{
        $q = "DELETE FROM inside_posts WHERE id = :id";
        $p = ['id' => $id];

        if( service_db_insert($q, $p) )
            service_end(Status::Success, $id);
        else
            service_end(Status::Error, 'No se pudo eliminar el post seleccionado. <br>ERR: ' . service_db_error()[2]);
    }
    else
		service_end(Status::Error, 'Se requiere nivel de administrador para realizar la operación');