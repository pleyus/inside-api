<?php

	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	//	Tablas utilizadas
	$tab_users = "info_user";

	//	Sacamos los datos
	$ids = json_decode( service_match_param('ids'), true );
	$fields = json_decode( service_match_param('fields'), true );
	

	//	Solo admins...
	if( USER_LEVEL == UserType::Admin && CanDo('user') )
	{
		//	Si se han seleccionado ids para editar:
		if( count($ids) > 0 )
		{
			//	Preparamos la paja
			$array_query = [];

			$q = "";
			$id = "";
			$params = [];
			$users = 0;

			//	Para cada campo (idnumber, sex, etc)
			foreach( $fields as $key => $val)
			{
				//	Solo agarramos los que esten marcados. Ej: MasiveEditor.idnumber.checked = true
				if($val['checked'] == 1)
				{
					//	Si tenemos los idnumbers tiene trato especial
					if( $key == 'idnumber' && strpos($val['val'], '#') > -1 )
					{
						$IDNUMS = SerializeIdNumber($val['val'], $ids);
						
						for($i = 0; $i < count($IDNUMS); $i++)
						{
							$array_query[] = 
							[
								"UPDATE info_user SET idnumber = :idnumber WHERE id = :id; ",
								$IDNUMS[$i]
							];
						}
					}
					else
					{
						$q .= $key . ' = :' . $key . ', ';
						$params[ $key ] = $val[ val ];
					}
				}
			}

			foreach($ids as $i){
				$id .= "id = :id_" . $i . ' OR ';
				$params['id_'.$i] = $i;
				$users++;
			}

			$arrlen = count($array_query);
			if( $q != '' || $arrlen > 0 )
			{
				$r = $r2 = false;
				
				if($q != '')
				{
					$q = "UPDATE $tab_users SET " . substr($q, 0, strlen($q) -2) . " WHERE " . substr($id, 0, strlen($id) - 4);
					$r = service_db_insert($q, $params);
				}

				if( $arrlen > 0 )
					$r2 = service_db_insert( $array_query );

				//	Si la consulta normal se ejecuto bien Y...
					//	Si la cadena de idnum no esta vacia entonces el checamos el resultado,
					//	Sino, VERDADERO
				if( ( $q != '' ? $r : true ) && ( $arrlen > 0 ? $r2 > 0 : true ) )
					service_end(Status::Success, 'Se han aplicado los cambios ' . ($users > 1 ? 'a los ' . $users . ' usuarios seleccionados.' : ' al usuario seleccionado.') );
				else
					service_end(Status::Warning, 'No se pudo aplicar la configuración: <br> – ' .  service_db_error()[2]);
			}
			service_end(Status::Warning, 'No hay opciones para aplicar');
		}
		service_end(Status::Error, 'No hay usuarios seleccionados');
	}

	//	Si no quiere decir que se está creando un registro nuevo
	else
		service_end(Status::Error, 'Error de permisos');
		