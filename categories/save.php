<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	//	Sacamos los datos enviados
	$data = service_get_param('data', Param::Post);
	$D = json_decode($data, true);

	//	Sacamos el tipo de categoria		
	$type = service_get_param('type', Param::Post);

	if( !in_array( $type, $_CATEGORIES) )
	{
		InsideLog( ($D['id'] > 0 ? Actions::TryUpdate : Actions::TryCreate), Module::Categories, $D['id'], $type);
		service_end(Status::Error, 'La categoria solicitada «' . $type . '» no esta soportada');
	}

	if( USER_LEVEL == UserType::Admin )
	{
		if( $type == 'institution' && CanDo('institutions'))
		{
			if( empty($D['name']) )
			{
				InsideLog(( $D['id'] > 0 ? Actions::TryUpdate : Actions::TryCreate), Module::Categories, $D['id'], $type);
				service_end(Status::Warning, "Se necesita un nombre para poder guardar la categoria");
			}

			if($D['id'] > 0)
			{
				$query = 
					"UPDATE 
						info_institutions 
					SET 
						name = :name, 
						lid = :lid, 
						address = :address, 
						phone1 = :phone1, 
						director = :director, 
						phone2 = :phone2 
					WHERE id = :id";
				$params = [
					'name' => $D['name'],
					'lid' => $D['lid'] > 0 ? $D['lid'] : 0,
					'address' => $D['address'],
					'phone1' => $D['phone1'],
					'director' => $D['director'],
					'phone2' => $D['phone2'],
					'id' => $D['id']
				];
				if( service_db_insert($query, $params) )
				{
					InsideLog(Actions::Update, Module::Categories, $D['id'], $type);
					service_end(Status::Success, "Se actualizó correctamente '" . $D['name'] . "'");
				}
				else
				{
					InsideLog(Actions::TryUpdate, Module::Categories, $D['id'], $type);
					service_end(Status::Error, "No se pudo actualizar el registro.");
				}
			}
			else
			{
				$query = 
					"INSERT INTO 
						info_institutions 
						(name, lid, address, phone1, director, phone2)
					VALUES 
						(:name, :lid, :address, :phone1, :director, :phone2)";

				$params = [
					'name' => $D['name'],
					'lid' => $D['lid'] > 0 ? $D['lid'] : 0,
					'address' => $D['address'] ?: '',
					'phone1' => $D['phone1'] ?: '',
					'director' => $D['director'] ?: '',
					'phone2' => $D['phone2'] ?: ''
				];

				if( service_db_insert($query, $params) )
				{
					$q = "SELECT id FROM info_institutions ORDER BY id DESC LIMIT 1";
					$r = service_db_select($q);
					$r = !empty($r) ? $r[0]['id'] : 0;

					InsideLog(Actions::Create, Module::Categories, $r, $type);
					service_end(Status::Success, "Se creó correctamente la escuela <b>" . $D['name'] . "</b>");
				}
				else
				{
					InsideLog(Actions::TryCreate, Module::Categories, 0, $type);
					service_end(Status::Error, "No se pudo crear el registro.");
				}
			}
		}
		elseif( CanDo('courses') || CanDo('vias') || CanDo('campaigns'))
		{
			//	Revisamos los requisitos
			if( !( $D['id'] > -1 ) )
			{
				InsideLog( Actions::TryCreate, Module::Categories, 0, $type);
				service_end(Status::Error, "Hay un problema con el identificador del elemento " . $D["id"]);
			}

			if( empty( $D["name"] ) )
			{
				InsideLog( ($D['id'] > 0 ? Actions::TryUpdate : Actions::TryCreate), Module::Categories, $D['id'], $type);
				service_end(Status::Error, "El nombre es requisito para poder guardar el elemento");
			}

			if( empty( $D["slug"] ) && $type != CategoryType::Via )
			{
				InsideLog( ($D['id'] > 0 ? Actions::TryUpdate : Actions::TryCreate), Module::Categories, $D['id'], $type);
				service_end(Status::Error, "Escriba un nombre corto para el elemento");
			}

			//	Preparamos el query y los parametros para insertar
			$query = "INSERT INTO info_categories (name, slug, param1, type) VALUES (:name, :slug, :param1, :type)";
			$params = 
			[
				"slug" => $D["slug"], 
				"name" => $D["name"],
				"param1" => $D["param1"]
			];

			//	Pero si se esta actualizando la wea
			if( $D["id"] > 0 )
			{
				//	Cambiamos el query y agregamos id a $params
				$query = "UPDATE info_categories SET name = :name, slug = :slug, param1 = :param1 WHERE id = :id";
				$params["id"] = $D["id"];
			}
			else $params['type'] = $type;

			//	Terminamos
			if( service_db_insert( $query, $params ) )
			{
				InsideLog( ($D['id'] > 0 ? Actions::Update : Actions::Create), Module::Categories, $D['id'], $type);
				service_end(Status::Success, "Se guardo correctamente el registro" );
			}
			else
			{
				InsideLog( ($D['id'] > 0 ? Actions::TryUpdate : Actions::TryCreate), Module::Categories, $D['id'], $type);
				service_end(Status::Error, "Algo salió mal al intentar guardar");
			}
		}
	}

	InsideLog( ($D['id'] > 0 ? Actions::TryUpdate : Actions::TryCreate), Module::Categories, $D['id'], $type);
	service_end(Status::Error, "No tienes autorización para crear esta categoria.");
	
