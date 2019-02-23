<?php
    function LoadOptions( $UserId = 0 ) {

			//  Cargamos las variables globales necesarias
			global $info_user;

			//  Reiniciamos la configuración
			$config = [];

			//  Consultamos la configuración general
			$options = service_db_select('SELECT * FROM inside_options');

			//  Por cada resultado...
			for ($i = 0; $i < count($options); $i++) {

					//  Sacamos un shortcut para los parametros
					$name = $options[$i]['name'];
					$id = $options[$i]['id'];
					$val = $options[$i]['value'];
					$type = $options[$i][ 'type' ];
					$restricted = $options[$i][ 'restricted' ] != 0;

					//  Verificamos el tipo de valor que trae
					if (OptionType::Number == $type)
							$val = $val * 1;

					elseif (OptionType::Bool == $type)
							$val = $val == 1 || $val = '1' || $val == 'true' || $val == true;

					elseif (OptionType::Json == $type)
							$val = unserialize( $val );

					//  Lo Insertamos en la configuración
					$config[ $name ] = [
							'id' => $id,
							'value' => $val,
							'type' => $type,
							'restricted' => $restricted
					];
			}

			//	Si viene -1 devolvemos la configuración global
			if($UserId == -1)
				return $config;

			//  Cargamos la configuración solicitada o la del usuario actual, si es que no tiene permisos
			$options = service_db_select(
					'SELECT * FROM inside_options_user WHERE uid = :uid', [
							'uid' => (USER_LEVEL >= UserType::Admin && CanDo('users') && $UserId > 0)
									? $UserId
									: $info_user['id']
					]
			);

			//  Volvemos a recorrer el array pero esta vez filtrando solo los !restricted
			for ($i = 0; $i < count($options); $i++) {

					$name = $options[$i]['name'];
					$conf = $config[ $name ];

					//  Checamos si la config general no es restringida
					if( !$conf['restricted'] ) { 

							$val = $options[$i]['value'];

							if (OptionType::Number == $conf['type'])
									$val = $val * 1;

							elseif (OptionType::Bool == $conf['type'])
									$val = $val == 1 || $val = '1' || $val == 'true' || $val == true;

							elseif (OptionType::Json == $conf['type'])
									$val = unserialize( $val );

							$config[ $name ]['value'] = $val;
					}
			}

			//  Devolvemos la configuración
			return $config;

    }
    // Carga la configuración principal
    
    $_INSIDE_CONF = LoadOptions();