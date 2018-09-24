<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	$id = service_get_param('id', Param::Post);

	if( !ctype_digit($id) )
		service_end(Status::Error, 'No se puede obtener información de ' . $id);

	if( USER_LEVEL >= UserType::Admin && CanDo('payment') )
	{
		service_db_select
		(
			"UPDATE services_payments SET status = :status WHERE id = :id",
			['id' => $id, 'status' => PayStatus::Deleted]
		);
		
		service_end(Status::Success, 'Deleted!');
	}
	else
		service_end(Status::Error, 'Se requiere nivel de administrador para realizar la operación');