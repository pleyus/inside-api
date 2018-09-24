<?php

	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	//	Tablas utilizadas
	$tab_notes = "inside_applicants_notes";
	$id = service_get_param('id', Param::Post);

	//	Si es que el nivel de usuario es Administrador 
	//	y el id del registro es > que 0 (se esta actualizando)
	if(  USER_LEVEL == UserType::Admin && CanDo('applicants') )
	{
	
		if(!CanDo('user'))
		{
			InsideLog(Actions::TryDelete, Module::ApplicantsNotes, $id, 'Sin permiso');
			service_end(Status::Warning, 'Se requieren permisos nivel USER_MOD para realizar la operación');
		}

		//	Preparamos el query de actualización
		$query = "DELETE FROM $tab_notes WHERE id = :id";

		//	Acomodamos los parametros para evitar una posible inyeccion
		$params = [ 'id' => $id ];
		
		
		if(service_db_insert($query, $params)){
			InsideLog(Actions::Delete, Module::ApplicantsNotes, $id);
			service_end(Status::Success, $params);
		}
		else
			service_end(Status::Warning, 'No se pudo eliminar la nota<br> –QUERY_ERROR') ;
	}

	//	Si no quiere decir que se está creando un registro nuevo
	else
	{
		InsideLog(Actions::TryDelete, Module::ApplicantsNotes, $id, 'Sin permiso');
		service_end(Status::Error, 'No se pudo eliminar la nota<br> –ADMIN_LEVEL_REQUIRED' );
	}