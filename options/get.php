<?php
    //	Terminamos para que no se abra sin login...
    if ( !defined('MAKE') ) die();
    
    //  Si somos administradores, obtenemos cualquier config.
    if (USER_LEVEL >= UserType::Admin && CanDo('users')) {

        //  Cargamos el id de quien queremos obtener la configuración
        $id = service_match_param('uid', 0);
        service_end(Status::Success, LoadOptions($id));

    } else {
        service_end(Status::Success, $_INSIDE_CONF)
        
    }