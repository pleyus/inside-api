<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	$id = service_match_param('id');
	$type = service_match_param('type');

	if( USER_LEVEL == UserType::Admin ){

		if( !( $id > -1 ) ){
			InsideLog(Actions::TryDelete, Module::Categories, $id, $type);
			service_end( Status::Error, "¿Que intentas hacer? " . $id . " no es un numero id valido" );
		}
		else
			if($type == 'institution' && CanDo('institutions'))
			{
				$ok = service_db_insert("DELETE FROM info_institutions WHERE id = :id", ['id' => $id]);
				if($ok){
					InsideLog(Actions::Delete, Module::Categories, 0, ('('. $id .') ' . $type));
					service_end(Status::Success, "Se ha eliminado correctamente el registro");
				}
				else
				{
					InsideLog(Actions::TryDelete, Module::Categories, $id, 'Error');
					service_end(Status::Error, "No se pudo eliminar el registro");
				}
			}
			else if( CanDo('courses') || CanDo('vias') || CanDo('campaigns') )
			{
				$ok = service_db_insert(
					"UPDATE info_categories SET status = :s WHERE id = :id",
					['id' => $id, 's' => RegStatus::Deleted]
				);

				if($ok){
					InsideLog(Actions::Delete, Module::Categories, $id, $type);
					service_end( Status::Success, "Registro eliminado" );
				}
				else{
					InsideLog(Actions::TryDelete, Module::Categories, $id, $type);
					service_end( Status::Success, 'Algo salió mal al borrar el registro' );
				}
			}
	}
	InsideLog(Actions::TryDelete, Module::Categories, $id, $type);
	service_end(Status::Error, "No tienes autorización para eliminar los elementos.");
