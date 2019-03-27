<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	if( ( USER_LEVEL == UserType::Admin && CanDo('payment') ) || USER_LEVEL == UserType::Student )
	{
		$D = get_pays();

		//	Fin :D
		service_end(Status::Success, $D);
	}
	else
		service_end(Status::Error, 'Se requiere nivel de administrador para realizar la operación');


function get_pays()
{
	global $USER;
	$id = USER_LEVEL == UserType::Student 
		? $USER->uid 
		: service_get_param('id', Param::Post);

	if( !ctype_digit($id) )
		service_end(Status::Error, 'No se puede obtener ' . (USER_LEVEL == UserType::Student ? 'tu' : 'la') . ' información');


	// Buscamos el pago
	$P = service_db_select
	(
		"SELECT * FROM services_payments WHERE id = :id AND status != :status",
		['id' => $id, 'status' => PayStatus::Deleted]
	);

	//	Si NO lo encontramos terminamos
	if( empty($P) )
		service_end(Status::Error, 'No se han encontrado datos para este id');

	//	En caso de que este el pago en la lista, solo sacamos el primero
	$P = $P[0];
	
	//	Obtenemos los datos de la persona a quien se le asignó el pago
	$U = service_db_select
	(
		"SELECT * FROM info_user WHERE id = :id",
		[ 'id' => $P['uid'] ]
	);

	//	Obtenemos los datos de la persona quien GENERÓ el pago
	$G = service_db_select
	(
		"SELECT * FROM info_user WHERE id = :id",
		['id' => $P['gid']]
	);

	//	Si es que ya recogieron el pago, sacamos los datos del COLECTOR
	$C = $P['cid'] > 1 
		? service_db_select
			(
			"SELECT * FROM info_user WHERE id = :id",
			['id' => $P['cid']]
			)
		: false;


	//	Verificar que no esten vacios los usuarios que nos muestran.
	if(empty($U) || empty($G))
		service_end(Status::Error, 'Hay un problema con el pago '.$id. '. No se puede leer correctamente la información de usuario');
	
	//	Asignar datos al array de retorno
	$P['user'] = $U[0];
	$PIC = service_db_select("SELECT * FROM inside_files WHERE id = :fid", ['fid' => $P['user']['fid']]);
	$P['user']['picture'] = empty($PIC) ? [] : $PIC[0];
	
	$COURSE = service_db_select("SELECT * FROM info_categories WHERE id = :id", ['id' => $P['user']['cid']]);
	$P['user']['course'] = !empty($COURSE) ? $COURSE[0] : [];

	$P['generator'] = $G[0];
	$P['collector'] = !empty($C) ? $C[0] : false;

	return $P;
}