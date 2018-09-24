<?php

	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	//	Tablas utilizadas
	$tab_pictures = "info_user_pictures";
	$tab_user = "info_user";

	//	Id de la foto
	$id = service_match_param('id');		//	Id de imagen
	$action = service_match_param('action');//	Accion a realizar
	$uid = service_match_param('uid');		//	Id del usuario al que le corresponde esta imagen

	//	Solo admins...
	if(  USER_LEVEL == UserType::Admin && CanDo('user') )
	{
		if($action == 'set')
		{
			//	Query para la tabla usuarios, asignando el id de la imagen a pid, donde el id de usuario sea uid
			$query = "UPDATE $tab_user SET pid = :id WHERE id = :uid;";
			$params = ['id' => $id, 'uid' => $uid];
			
			if(service_db_insert($query, $params))
				service_end(Status::Success, "Se ha reasignado la imagen al usuario.");

			service_end(Status::Error, "Hubo un problema al asignar la imagen, intenta de nuevo en un momento");
		}
		elseif($action == 'delete')
		{
			//	Cargamos la imagen, para ver si existe
			$pic = service_db_select("SELECT * FROM $tab_pictures WHERE id = :id", ['id' => $id]);

			//	Si no viene vacia
			if(!empty($pic))
			{
				//	Sacamos el nombre del archivo solamente
				$pic = $pic[0]['filename'];

				//	Borramos el registro de la base de datos
				if( service_db_insert("DELETE FROM $tab_pictures WHERE id = :id", ['id' => $id]) )
				{
					//	Ahora buscamos mas imagenes dentro de la tabla que apunten al mismo archivo
					$pics = service_db_select("SELECT uid FROM $tab_pictures WHERE filename = :name", ['name' => $pic]);
					
					//	Si no hay, borramos el archivo
					if(empty($pics))
						@unlink( $_SERVER['DOCUMENT_ROOT'] . '/uploads/users/' . $pic );

					//	Ahora buscamos otras imagenes del usuario...
					$pics = service_db_select("SELECT id FROM $tab_pictures WHERE uid = :uid ORDER by at DESC", ['uid'=>$uid]);
					//	Si hay imagenes aun, sacamos el ID de la mas reciente
					$pics = !empty($pics) ? $pics[0]['id'] : 0;

					//	Sacamos el id de la info del usuario
					$user_pic = service_db_select("SELECT pid FROM $tab_user WHERE id = :uid", ['uid' => $uid]);
					//	Sacamos solo el id de la imagen actual del usuario
					$user_pic = !empty($user_pic) ? $user_pic['pid'] : 0;

					//	En caso de que el id de la imagen sea el mismo $id que borramos
					if($user_pic == $id)
						//	Le ponemos la mas reciente o directamente 0 al pid anterior
						service_db_insert("UPDATE $tab_user SET pid = :pid WHERE id = :uid", ['pid' => $pics, 'uid' => $uid]);
					
					service_end(Status::Success, "Se ha eliminado la imagen correctamente");
				}
				service_end(Status::Error, "No se pudo quitar el vinculo de la imagen con el usuario");
			}
			service_end(Status::Error, "No se ha encontrado la imagen para borrar");
		}
		else
			service_end(Status::Error, 'No sabemos que hacer con la imagen. Lo sentimos');
	}

	//	Si no, quiere decir que no hay permiso...
	else
		service_end(Status::Error, 'Error de permisos');
		