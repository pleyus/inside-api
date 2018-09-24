<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	$id = service_get_param('id', Param::Post);

	//	Tablas
	$tab_users = "info_user";
	$tab_applicants = "inside_applicants";
	$tab_notes = "inside_applicants_notes";

	//	Se el id no es un numero
	if( !ctype_digit($id) )
		service_end(Status::Error, 'No se puede eliminar ' . $id);

	//	Solo los admins pueden borrar los aplicantes
	if( USER_LEVEL >= UserType::Admin && CanDo('applicants') )
	{
		if (!CanDo('user')) 
		{
			InsideLog(Actions::TryDelete, Module::Applicants, $id, 'Sin permiso');
			service_end(Status::Warning, 'Se requieren permisos nivel USER_MOD para realizar la operación');
		}

		//	Consultamos el id del usuario basado en el id del aplicante
		$uid = service_db_select(
			"SELECT
				u.id uid,
				CONCAT(u.firstname, ' ', u.lastname) name
			FROM
				$tab_applicants a
				LEFT JOIN $tab_users u ON u.id = a.uid
			WHERE
				u.status = :status AND
				a.id = :id
			LIMIT 1;",
			[ 'id' => $id, 'status' => UserStatus::Applicant ]
		);

		//	Sacamos el id
		if( !empty($uid) )
		{
			$name = $uid[0]['name'];
			$uid = $uid[0]['uid'];
		
			//	Borramos
			service_db_insert("DELETE FROM $tab_users WHERE id = :uid LIMIT 1", ['uid' => $uid]);
			service_db_insert("DELETE FROM $tab_notes WHERE aid = :id", ['id' => $id]);
			service_db_insert("DELETE FROM $tab_applicants WHERE id = :id LIMIT 1", ['id' => $id]);

			//	End!
			InsideLog(Actions::Delete, Module::Applicants, 0, '('.$id.') ' .$name);
			service_end(Status::Success, 'Registro eliminado');
		}
		else
		{
			InsideLog(Actions::TryDelete, Module::Applicants, $id, 'Registro corrupto');
			service_end(Status::Success, 'No se puede borrar a este aplicante');		
		}
	}
	else
	{
		InsideLog(Actions::TryDelete, Module::Applicants, $id, 'Sin permiso');
		service_end(Status::Error, 'Se requiere nivel de administrador para realizar la operación');
	}