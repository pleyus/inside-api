<?php
#
#	General
#

	function service_end($status, $data)
	{
		if( !is_int( $status ) )
			throw new Exception("El estado debe ser un numero entero", 1);
		
		die ( json_encode( [ 'status' => $status, 'data' => $data ] ) );
	}
	function service_match_param($name, $default = false)
	{
		$val = service_get_param($name, Param::Get, false);
		$val = $val ?: service_get_param($name, Param::Post, false);
		$val = $val ?: service_get_param($name, Param::Cookie, false);
		$val = $val ?: service_get_param($name, Param::Session, false);

		return $val ?: $default;
	}
	function service_get_param($name, $type = Param::Get, $default = false)
	{
		$val = $default;
		
		switch( $type )
		{
			case Param::Get:
				if( !empty( @$_GET[ $name ] ) )
					$val = $_GET[ $name ];
			
			case Param::Post:
				if( !empty( @$_POST[ $name ] ) )
					$val = $_POST[ $name ];
			
			case Param::Cookie:
				if( !empty( @$_COOKIE[ $name ] ) )
					$val = $_COOKIE[ $name ];
			
			case Param::Session:
				if( !empty( @$_SESSION[ $name ] ) )
					$val = $_SESSION[ $name ];
					
		}

		return $val;
	}
	function get_category_by_slug($slug) 
	{
		$query = "SELECT * FROM info_categories WHERE slug = :slug LIMIT 1";
		$params = [ 'slug' => $slug ];

		$C = service_db_select($query, $params);
		$C = !empty($C) ? $C[0] : false;
		
		return $C;
	}

	/**
	*	Realiza una consulta a la base de datos
	*
	* 	@global $service_db
	*
	* 	@param $query Es la consulta que se ha de realizar.
	*	@param $params
	*/
	$_service_db_error = false;
	function service_db_select( $query, $params = [] )
	{
		global $service_db, $_service_db_error;

		try
		{
			$q = $service_db->prepare($query);
			$q->execute($params);
		}
		catch(Exception $e) {
			$_service_db_error = $e;
			return false;
		}
		return 	$q->fetchAll( PDO::FETCH_ASSOC );
	}
	/**
	 * Ejecuta una consulta modificadora a en la base de datos actual. Si se ejecuta la consulta con $query = array en lugar de true o false, devuelve el numero de registros modificados correctamente
	 * @param mixed $query En formato string se considera como consulta, en array ya no se toma en cuenta $params y debera ser [ $querystring, $params ] cada elemento
	 * @param array $params Opcional: son los parametros que se curarán con el prepare
	 */
	function service_db_insert( $query, $params = [] )
	{
		global $service_db, $_service_db_error;

		if( is_array( $query ) )
		{
			$total = 0;

			foreach($query as $val)
			{
				try
				{
					$q = $service_db -> prepare( $val[0] );
					if( $q -> execute( $val[1] ?: [] ))
						$total++;
				}
				catch( Exception $e )
				{
					$_service_db_error = $e;
				}
			}

			return $total;
		}
		else
		{
			try
			{
				$q = $service_db -> prepare( $query );
				return $q -> execute( $params );
			}
			catch( Exception $e ){
				$_service_db_error = $e;
				return false;
			}
		}
	}
	function service_db_error()
	{
		global $_service_db_error;
		return $_service_db_error === false ? [0,0,'(desconocido)'] : $_service_db_error->errorInfo;
	}
	function get_prepared_query($sql,$params) 
	{
		$sql = str_replace( "
", "", $sql);
		$sql = str_replace( "	", " ", $sql);
		//$sql = str_replace( "  ", "", $sql);
		foreach ($params as $key => $value) {
			$sql = str_replace( ":".$key, is_string($value) ? "'" . $value . "'" : $value, $sql);
		}
		return $sql;
	  }
	function service_get_ip($full = false) {
		
		$ipaddress = '';
		if($full)
		{
			$ipaddress = @$_SERVER['HTTP_CLIENT_IP'] . ';' .
            $ipaddress .= @$_SERVER['HTTP_X_FORWARDED_FOR'] . ';' .
			$ipaddress .= @$_SERVER['REMOTE_ADDR'] . ';';
			$ipaddress .= @$_SERVER['HTTP_X_FORWARDED'] . ';';
			$ipaddress .= @$_SERVER['HTTP_FORWARDED_FOR'] . ';';
			$ipaddress .= @$_SERVER['HTTP_FORWARDED'];
		}
		else 
		{
			if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
				$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
			else if (isset($_SERVER['HTTP_CLIENT_IP']))
				$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
			else if(isset($_SERVER['HTTP_X_FORWARDED']))
				$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
			else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
				$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
			else if(isset($_SERVER['HTTP_FORWARDED']))
				$ipaddress = $_SERVER['HTTP_FORWARDED'];
			else if(isset($_SERVER['REMOTE_ADDR']))
				$ipaddress = $_SERVER['REMOTE_ADDR'];
		}
		return $ipaddress;
	}

	function decode_file($data, $types = [ 'jpg', 'jpeg', 'gif', 'png' ] )
	{
		if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) 
		{
			$data = substr($data, strpos($data, ',') + 1);
			$data = str_replace(' ', '+', $data);
			$type = strtolower($type[1]); // jpg, png, gif
		
			if (!in_array($type, $types ))
				return false;
		
			return base64_decode($data);
		} 
		else
			return false;
	}
	/**
	 * Obtiene los programas en los que participa un usuario, si no se pasa el id, se toma el del usuario actual
	 */
	function RadioPrograms($user_id, $onlyactives = true)
	{
		if($user_id > 0)
		{
			$q = "SELECT 
					g.id, g.name, g.img, g.days
				FROM 
					radio_guide g
					LEFT JOIN radio_guide_announcers gh ON gh.gid = g.id
					LEFT JOIN radio_announcers h ON gh.hid = h.id
				WHERE 
					h.uid = :uid 
					AND g.status = " . ($onlyactives ? 1 : 0);

			$p = [ 'uid' => $user_id ];
			//$r = get_prepared_query($q, $p);
			$r = service_db_select($q, $p);

			return $r;
		}
		else 
			return [];
	}
	function ImAnnouncer()
	{
		global $info_user;
		$id = $info_user[ id ];

		return $id > 0 ? !empty( RadioPrograms($id) ) : false;
	}
	function GetUserInfo($id = 0)
	{
		global $OUTPUT, $USER;

		/***************************************************
		*	Información base de usuario
		****************************************************/
			$U = service_db_select
			(
				"SELECT * FROM info_user WHERE ".($id == 0 ? 'u' : '')."id = :id LIMIT 1",
				['id' => ($id == 0 ? $USER->id : $id)]
			);

			//	Si viene vacio le asignamos solo lo basico y terminamos
			if( empty($U) )
			{
				$U = [
					'id' => 0,
					'firstname' => $USER->firstname,
					'lastname' => $USER->lastname
				];
				return $U;
			}

			//	Si no, continuamos y regresamos solo al primero
			$U = $U[0];
			$id = $id > 0 ? $id : $U[ id ];
			
			$USER->uid = $U['id'];

		/***************************************************
		*	Sacamos la información del curso en el que esta
		****************************************************/
			$COURSE = service_db_select(
				"SELECT 
					* 
				FROM 
					info_categories 
				WHERE 
					id = :id", 
				['id' => $U['cid'] ]
			);

			//	Si viene algun curso lo ponemos, si no vaciamos el array
			$U['course'] = !empty($COURSE) ? $COURSE[0] : [];


		/***************************************************
		*	Sacamos las imagenes del usuario
		****************************************************/
			$PICTURE = service_db_select(
				"SELECT 
					* 
				FROM 
					info_user_pictures
				WHERE 
					id = :id", 
				['id' => $U['pid'] ]
			);

			//	Si viene algun curso lo ponemos, si no vaciamos el array
			$U['picture'] = !empty($PICTURE) ? $PICTURE[0] : [];
		
		/***************************************************
		*	Sacamos los telefonos
		****************************************************/
			$PHONES = service_db_select(
				"SELECT 
					* 
				FROM 
					info_user_phones
				WHERE 
					uid = :id",
				['id' => $U['id'] ]
			);

			//	Si viene algun curso lo ponemos, si no vaciamos el array
			$U['phones'] = !empty($PHONES) ? $PHONES : [];

		/***************************************************
		*	Sacamos la información de la instucion de proc.
		****************************************************/
			$INSTITUTION = service_db_select(
				"SELECT 
					i.*,
					s.asentamiento loc,
					s.municipio mun,
					s.estado est,

					i.name link_title,
					CONCAT(s.asentamiento, ', ', s.municipio) link_subtitle,
					i.director link_body,
					'' link_imgurl
				FROM 
					info_institutions i
					LEFT JOIN info_sepomex s ON s.id = i.lid
				WHERE 
					i.id = :iid", 
				['iid' => $U['iid'] ]
			);

			//	Si viene algun curso lo ponemos, si no vaciamos el array
			$U['institution'] = !empty($INSTITUTION) 
				? $INSTITUTION[0] 
				//	Patch para el linker
				: null;

		/***************************************************
		*	Sacamos su ubicación
		****************************************************/
			$LOCATION = service_db_select(
				"SELECT
					*,
					asentamiento link_title,
					CONCAT(municipio, ', ', estado) link_subtitle,
					CONCAT('CP ', cp) link_body,
					'' link_imgulr
				FROM info_sepomex WHERE id = :lid",
				['lid' => $U[lid]]
			);
			$U['location'] = !empty($LOCATION) ? $LOCATION[0] : null;		

		/***************************************************
		*	Sacamos la información de quien lo registró
		****************************************************/
			$U['regby'] = service_db_select(
				"SELECT
					id, 
					firstname, 
					lastname
				FROM info_user 
				WHERE id = :id LIMIT 1",
				[id => $U['rid']]
			);
			$U['regby'] = empty($U['regby']) ? null : $U['regby'][0];

		/***************************************************
		*	Sacamos sus datos de plataforma
		****************************************************/
			$U['platform'] = service_db_select(
				"SELECT
				id, 
				firstname, 
				lastname, 
				email,

				CONCAT(firstname, ' ', lastname) link_title,
				email link_subtitle,
				idnumber link_body,
				'' link_imgurl
				
				FROM mdl_user WHERE id = :id LIMIT 1",
				[id => $U['uid']]
			);
			$U['platform'] = empty( $U['platform'] ) ? null : $U['platform'][0];
		/***************************************************
		*	Sacamos la Información de sus programas de radio
		****************************************************/
			$U['radio'] = RadioPrograms( $id );

		/***************************************************
		*	Sacamos sus capacidades como superuser
		****************************************************/
			$capabilities = service_db_select("SELECT * FROM info_user_capabilities WHERE uid = :id LIMIT 1", [ id => $U[id] ]);

			if(empty( $capabilities ))
				$U[capabilities] = [
					uid => $U[id],
					payment => 0,
					user => 0,
					applicants => 0,
					courses => 0,
					vias => 0,
					campaigns => 0,
					institutions => 0,
					radio => 1,
					docs => 1,
					messages => 0
				];
			else
				$U[ capabilities ] = $capabilities[0];
		
		return $U;
	}

	function SaveLog($log_name){
		global $USER;
		try
		{
			$log_name = "./" . $log_name;
			$username = $USER ? (
				($USER->firstname ?: 'noname') . ' ' . ($USER->lastname ?: '')
			) : '(not logged)';

			if( !file_exists($log_name) )
			{
				$f = fopen($log_name, "w");
				$cont = 'date,user,using,make' . PHP_EOL. date("h:i:s d/m/Y") . ',' . $username . ',' . USING . ',' . MAKE . PHP_EOL;
				fwrite($f, $cont);
				fclose($f);
			}
			else
			{
				$f = fopen($log_name, "a");
				$username = $USER ? (
					($USER->firstname ?: 'noname') . ' ' . ($USER->lastname ?: '')
				) : '(not logged)';
				$cont = date("h:i:s d/m/Y") . ',' . $username . ',' . USING . ',' . MAKE . PHP_EOL;
				fwrite($f, $cont);
				fclose($f);
			}
		}catch(Exception $x){}
	}

	function CanDo( $module ){
		global $info_user;

		//	Mientras no se actualiza el modulo
		//return true;

		$q = "SELECT * FROM info_user_capabilities WHERE uid = :id LIMIT 1";
		$p = [ id => $info_user[ id ]];
		$r = service_db_select($q, $p);

		if( empty($r) )
			return false;
		else
		{
			$r = $r[0];
			return $r[ $module ] == 1;
		}
	}

	function InsideLog($action, $module, $affectedid = 0, $with = null){
		global $info_user;
		
		$q = "INSERT INTO info_user_log (at, action, module, aid, comment, uid) VALUES (:at, :action, :module, :aid, :comment, :uid)";
		$p = [
			'at' => time(),
			'action' => $action > 0 ? $action : 0,
			'module' => $module > 0 ? $module : 0,
			'aid' => $affectedid > 0 ? $affectedid : 0,
			'comment' => $with,
			'uid' => $info_user['id'] > 0 ? $info_user['id'] : 0,
		];
		return service_db_insert($q, $p);
	}

	/**
	 * Extrae los comodines de una cadena de texto. Devuelve un array de dos elementos, el primero la cadena de texto limpia de comodines y el segundo con los comodines asociativos a su valor.
	 * @param	mixed	$text	El texto que se quiere analizar
	 * @param	mixed	$wildcards	Array de comodines ["val1", "val2" ... "valn"] o un string separado por comas "val1, val2, val3"
	 * @return	mixed	Un array en caso de exito o false en caso de error
	 */
	function Wildcards($text, $wildcards)
	{
		//	Checamos si viene un array de comodines
		if(!is_array($wildcards))
		{
			//	Si no, checamos si es un string de comodines (val1, val2, ... valn)
			$wildcards = explode(',', str_replace(' ', '', $wildcards) );

			//	Si no, terminamos
			if(empty($wildcards))
				return [$text, []];
		}

		//	Pasamos a minusculas los Wildcards
		for($i = 0; $i < count($wildcards); $i++)
			$wildcards[$i] = strtolower( $wildcards[$i] );

		//	Ahora explotamos el texto en palabras
		$wilds = explode(' ', $text);
		$values = [];
		$founded = [];

		//	Por cada palabra como $w
		foreach($wilds as $w):
			//	Explotamos el valor por : (dos puntos)
			$ws = explode( ':', strtolower($w) );

			//	Y checamos si el valor 0 se encuentra en el array de comodines
			if( in_array( $ws[0], $wildcards) )
			{
				//	Si es asi, lo guardamos con su valor en nuestro comodeitor xD 
				$values[ $ws[0] ] = $ws[1] ? $ws[1] : null;

				//	Y agregamos el wildcard en bruto a encontrados
				$founded[] = $w;
			}
			//	Para encontrar comodines bandera
			elseif( in_array('['.$ws[0].']', $wildcards) )
			{
				$values[$ws[0]] = true;
				$founded[] = $w;
			}
		endforeach;

		//	Ahora quitamos los wildcards de la matris $wilds
		$wilds = array_diff($wilds, $founded);

		//	Y lo que quede lo juntamos en texto para mandarlo como search
		$text = implode(' ', $wilds);
		
		//	Al terminar devolvemos:
		return [ $text, $values ];
	}

	function SerializeIdNumber(string $idnumber, array $ids)
	{
		$idnums = [];
		$max = strlen(count($ids).'');	// Maximo del largo de la cadena 10 = 2, 1000 = 4
		$group = "";	//..Son las # que se encuentran en el id number Ej: 18LTS### = ###, IDNO## = ##

		//	Formamos el grupo de #, empezando por uni
		//	Ej: primer recorrido buscamos "#" en el num control,
		//		si se encuentra continuamos
		//		sino, terminamos y nos quedamos con el ultimo grupo encontrado, si
		//		venia el id 18LTS### en el recorrido 3 el grupo será ### y en el siguiente ya no se podra
		$g = "";
		for($i = 0; $i < strlen($idnumber); $i++)
		{
			//	Preparamos el group temporal para buscar...
			$g = $g . "#";

			//	Si su posicion es mayor o igual a 0
			if( strpos($idnumber, $g) > -1 )
				//	Lo asignamos al grupo mas grande encontrado
				$group = $g;
			
		}
		//	Ya tenemos el grupo mas grande de numeros id seriales. Ej: ###
		//	Ahora lo vamos a serializar con numeros de 1 a N (999 maximo en este caso...)

		
		//	Para cada ID en ids
		for($i = 0; $i < count($ids); $i++)
		{
			//	Creamos su serial. Ej: ### = 001, ### = 002... etc
			$serie = str_pad(
				$i+1, 			//	El indice de ids + 1 (para que empieze en 1)
				strlen($group), //	Lo maximo de caracteres que se requieren (dado por ###)
				'0', 			//	El caracter con que se llenará	Ej: 000
				STR_PAD_LEFT	//	Donde se llena (en este caso a la Izquierda) Ej: 1 = 001
			);

			//	Lo iniciamos para manipularlo
			$idseriado = 
			[ 
				id => $ids[$i], 	//	El id del user al que se le asigna el idnumber
				idnumber => $idnumber	//	El id en bruto. Ej: 18LTS###
			];

			//	Vamos a reemplazar #, ##, ###, #### con el group serializado: Ej: ### > 001, ## > 01, # > 1 
			for($j = strlen($group); $j >= 0; $j--)

				//	Al idnumber en bruto se le reemplazará 
				$idseriado[ idnumber ] = str_replace(
					substr($group, 0, $j+1), //	El substr desde 0 hasta $j+1 del grupo. Ej: para grupo = ###, y $j = 0 --> #, $j = 2 --> ###
					substr($serie, -($j+1)), //	Se reemplazará con la serie (001) desde -($j+1)   si $j = 0, $j + 1 = 1, el substring de 001 de -1 = 1
					$idseriado[ idnumber ]
				);
			$idnums[] = $idseriado;
		}

		return $idnums;

		//	COMO ES POSIBLE QUE ESTE TAN PRRO ALV!!!!

	}
	/**
	 * Obtiene un array con los ids numericos que vengan dentro de una cadena de texto
	 *
	 * @param string $string	Es la cadena que, se supone, trae los ids
	 * @param string $separator	Si no se asigna, asumiremos que viene separado por comas
	 * @return array
	 */
	function GetIdsFromString( $string, $separator = ',' ) {
		$tmpids = explode( $separator, $string );
		$ids = [];

		foreach( $tmpids as $id ) {
			$i = trim( $id );

			if($i > 0)
				$ids[] = $i * 1;
		}
		return $ids;
	}