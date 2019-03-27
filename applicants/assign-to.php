<?php
	
	//	Terminamos para que no se abra sin login...
	if ( !defined('MAKE') ) die();

	$aid = service_match_param('aid'); $aid = $aid > 0 ? $aid : 0;
	$uid = service_match_param('uid'); $uid = $uid > 0 ? $uid : null;

	$report = service_match_param('only-report');
	$clear = service_match_param('clear');

	if (USER_LEVEL == UserType::Admin && CanDo('applicants') )
	{
		//	Si solo se está reportando el estatus de seguimiento...
		if($report) {
			$q = "DELETE FROM inside_applicants_tracking WHERE uid = :uid";
			$p = [ 'uid' => $info_user['id'] ];
			service_db_insert($q, $p);
			if(!$clear) {

				$q = "INSERT INTO inside_applicants_tracking (aid, uid, at) (:aid, :uid, " . time() . ");";
				$p['aid'] = $aid;

				if (service_db_insert($q, $p) ) 
					service_end(Status::Success, '');

				service_end(Status::Warning, '');
			}
		}
		else {
			$q = "UPDATE inside_applicants SET aid = :uid WHERE id = :aid";
			$p = ['uid' => $uid, 'aid' => $aid];
			if (service_db_insert($q, $p)) {
				service_end(Status::Success, 'Se ha asignado correctamente');
			}

			service_end(Status::Error, 'No se pudo asignar el usuario al aspirante');
		}
	}
	else{
		InsideLog(Actions::TryCreate, Module::Applicants);
		service_end(Status::Error, 'No tiene privilegios suficientes para realizar la operación');
	}