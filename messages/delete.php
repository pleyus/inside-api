<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	$id = service_match_param('id');

	//	Solo pueden obtenerlos los administradores
	if( USER_LEVEL >= UserType::Admin && CanDo('messages') )
	{
		$p = [ 'id' => $id ];
		$q = "SELECT * FROM service_contact WHERE id = :id";
		$m = service_db_select($q, $p); $m = !empty($m) ? $m[0] : ['firstname' => '', 'message' => '', 'email' => ''];
		
		$q = "DELETE FROM services_contact WHERE id = :id";

		if( service_db_insert($q, $p) )
		{
			InsideLog( Actions::Delete, Module::Messages, $id, 'De ' . $m['firstname'] . ' «' . $m['email'] . '» : ' . substr($m['message'], 0, 40) );
			service_end(Status::Success, "Se ha eliminado correctamente el mensaje");
		}
		else
		{
			InsideLog( Actions::TryDelete, Module::Messages, $id, service_db_error()[2]);
			service_end(Status::Warning, "No se pudo eliminar el mensaje.<br><br> –" . service_db_error()[2]);
		}
	}
	else
	{
		InsideLog( Actions::TryDelete, Module::Messages, $id, 'Sin permiso' );
		service_end(Status::Error, 'Se requiere nivel de administrador para realizar la operación');
	}