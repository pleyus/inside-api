<?php
	
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

    $id = service_match_param('id');
    $set = service_match_param('set');
    $set = $set === 0 ? 0 : 1; 

	if (USER_LEVEL == UserType::Admin && CanDo('applicants') )
	{
        $q = "UPDATE inside_applicants SET excluded = :set WHERE id = :id";
        $p = ['id' => $id, 'set' => $set];
        if (service_db_insert($q, $p)) {
            service_end(Status::Success, 'Se ha ' . ($set === 1 ? 'descartado' : 'habilitado') . ' el aspirante');
        } else {
            service_end(Status::Error, 'No se pudo actualizar al aspirante');
        }
	}
	else{
		InsideLog(Actions::TryUpdate, Module::Applicants);
		service_end(Status::Error, 'No tiene privilegios suficientes para realizar la operación');
	}