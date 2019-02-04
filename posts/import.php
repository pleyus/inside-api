<?php
    //	Terminamos para que no se abra sin login...
    if ( !defined('MAKE') ) die();

    //  Capturamos los post que se quieren importar
    $posts = service_match_param('posts');
    $posts = json_decode($posts);
    
    //  Si es admin y los posts no vienen vacios
    if( USER_LEVEL >= UserType::Admin && !empty($posts))
	{
        //  Sacamos los posts
    }
    else
		service_end(Status::Error, 'Se requiere nivel de administrador para realizar la operación');