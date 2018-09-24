<?php
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	//	El id al cual vamos a consultar
	$id = service_match_param('id');

	$tab_applicants = "inside_applicants";

	//	Solo con nivel requerido
	if( USER_LEVEL == UserType::Admin && CanDo('applicants') )
	{
		//	Si el id es mayor a 0
		if($id > 0)
		{
			//	Sacamos la info de applicant
			$q = "SELECT * FROM inside_applicants WHERE id = :id";
			$p = [ id => $id ];
			$A = service_db_select($q, $p);

			//	Si hay algo...
			if( !empty($A) )
			{
				//	Cargamos el primer applicant
				$A = $A[0];

				//	User
				$q = "SELECT * FROM info_user WHERE id = :id";
				$p = [ id => $A[uid] ];
				$U = service_db_select($q, $p);

				//	Si hay algo
				if( !empty($U) )
				{
					$U = $U[0];

					$A['user'] = $U;

					//	Registado por
						$q = "SELECT id, firstname, lastname FROM info_user WHERE id = :id";
						$p = [ 'id' => $A['user']['rid'] ];
						$UR = service_db_select($q, $p);
						$A['user']['regby'] = $UR = !empty($UR) ? $UR[0] : [];;

					//	Campaña
						$q = "SELECT * FROM info_categories WHERE id = :cid";
						$p = [ 'cid' => $A[campaign] ];
						$C = service_db_select($q, $p);
						//	Si la campaña no esta vacia
						$C = !empty($C) 
							#La asignamos tal cual
							? $C[0] 
							#Si esta vacia, checamos el id de campaña y...
							: [ 
								'id' => $A[campaign], 
								#Si el id es -1 entonces es standard, si no, viene de internet
								'name' => $A[campaign] == -1 
									? 'Estandard' # -1
									: 'Internet'  # 0
							];
						$A['campaign'] = $C;

					//	Notes
						$q = "SELECT n.*, u.firstname, u.lastname FROM inside_applicants_notes n LEFT JOIN info_user u ON u.id = n.uid WHERE aid = :aid ORDER BY n.at DESC";
						$p = [ aid => $A[id] ];
						$N = service_db_select($q, $p);
						$N = !empty($N) ? $N : [];
						$A['notes'] = $N;

					//	Origen
						$q = 
							"SELECT i.*, 
								i.name link_title,
								CONCAT(s.asentamiento, ', ', s.municipio) link_subtitle,
								i.director link_body,
								'' link_imgurl 
							FROM 
								info_institutions i
								LEFT JOIN info_sepomex s ON s.id = i.lid
							WHERE i.id = :iid";
						$p = [ iid => $U[ iid ] ];
						$I = service_db_select($q, $p);
						$I = !empty($I) ? $I[0] : null;
						$A['user']['institution'] = $I;
					
					//	Location
						$q = 
							"SELECT 
								*,

								asentamiento link_title,
								CONCAT(municipio, ', ', estado) link_subtitle,
								CONCAT('CP ', cp) link_body,
								'' link_imgulr
							FROM 
								info_sepomex 
							WHERE 
								id = :lid";
						$p = [ lid => $U[ lid ] ];

						$L = service_db_select($q, $p);
						$L = !empty($L) ? $L[0] : null;
						$A['user']['location'] = $L;

					InsideLog(Actions::View, Module::Applicants, $id);
					service_end(Status::Success, $A);
				}
			}
		}
		InsideLog(Actions::TryView, Module::Applicants, $id);
		service_end(Status::Error, 'No se puede cargar el aspirante');
	}
	else
	{
		InsideLog(Actions::TryView, Module::Applicants, $id, 'Sin permiso');
		service_end(Status::Error, 'No tienes privilegios suficientes para entrar aqui');
	}