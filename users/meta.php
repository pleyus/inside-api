<?php

	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	//	Tablas utilizadas
	$tab_users = "info_user";

	$set = service_get_param('set', Param::Post);
	$set = !empty($set) ? json_decode($set, true) : false;

	$id = service_get_param('id', Param::Post);
	$id = $id > 0 ? (USER_LEVEL == UserType::Admin ? $id : $info_user) : $info_user['id'];

	if($set)
	{
		//	Si el usuario es administrador, puede modificar el nivel de acceso del usuario
		$set['access'] = USER_LEVEL == UserType::Admin ? $set['access'] : $info_user['meta']['access'];
		$query = "UPDATE $tab_users SET meta = :meta WHERE id = :id";
		$params = [ 'meta' => serialize( $set ), 'id' => $id ];
		service_end(Status::Success, service_db_insert($query, $params));
	}
	else
		service_end(Status::Error, 'Es necesario especificar la accion con las metas');
