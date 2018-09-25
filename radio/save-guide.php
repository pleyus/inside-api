<?php

	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	//	Tablas utilizadas
	$tab_guide = "radio_guide";
	$tab_gh = "radio_guide_announcers";

	//	Sacamos la info
	$G = json_decode( service_match_param('data'), true );
	
	
	if( USER_LEVEL == UserType::Admin && CanDo('radio') )
	{
		//	Si se esta actualizando...
		if($G[id] > 0)
		{
			//	Actualizamos los datos del programa
			$q = "UPDATE $tab_guide SET name = :name, days = :days, status = :status WHERE id = :id;";
			$p = [
				name => $G[name],
				days => serialize($G[days]),
				status => $G[status] == 1 ? 1 : 0,
				id => $G[id]
			];
			$r = service_db_insert($q, $p);
			
			if($r){
				//	Quitamos todos los locutores antes de ponerlos nuevamente
				$q = "DELETE FROM $tab_gh WHERE gid = :id";
				$p = [id => $G[id]];
				$r = service_db_insert($q, $p);

				//	Insertamos los locutores, si es que trae...
				if( !empty($G[ announcers ]) ){
					$q = "";
					$p = [ ];
					$id = $G[id];
					foreach( $G[ announcers ] as $h )
					{
						$q .= " ($id, :hid_" . $h[ id ] . "),";
						$p[ 'hid_'.$h[ id ] ] = $h[ id ];
					}
					$q = "INSERT INTO $tab_gh (gid, hid) VALUES " . substr($q, 0, -1);
					
					$r = service_db_insert($q, $p);
					
				}

				service_end(Status::Success, 'Se ha guardado la información...');
			}
			service_end(Status::Error, 'No se pudo actualizar la información del programa');
		}
		else{
			//	Creamos un nuevo programa
			$q = "INSERT INTO $tab_guide (name, img, days, status) VALUES (:name, '', :days, :status)";
			$p = [
				name => $G[name],
				days => serialize($G[days]),
				status => $G[status] == 1 ? 1 : 0
			];
			
			service_db_insert($q, $p);

			$q = "SELECT MAX(id) AS id FROM $tab_guide";
			//die(get_prepared_query($q,$p));
			$r = service_db_select($q);

			if( !empty($r) ){
				$id = $r[0][id];

				//	Insertamos los locutores
				if( !empty( $G[ announcers ]) ){
					$hq = ""; // Announcer Queries
					$p = [ ID => $id ];

					foreach( $G[ announcers ] as $h )
					{
						$hq .= "( :ID, :hid_" . $h[ id ] . "),";
						$p[ 'hid_'.$h[ id ] ] = $h[ id ];
					}
					$q = "INSERT INTO $tab_gh (gid, hid) VALUES " . substr($hq, 0, -1);
				}

				$r = service_db_insert($q, $p);

				service_end(Status::Success, 'Se ha guardado la información...');
			}
		}
	}
	service_end(Status::Error, 'No tienes autorización para realizar la acción');