<?php
	//	Terminamos para que no se abra sin login...
    if ( !defined('MAKE') ) die();

    $cid = service_match_param('cid');
    $dataset = json_decode( service_match_param('dataset'), true);
    $note = service_match_param('note');

    $lid = service_match_param('lid');  // level id o categoria del curso (levelid)
    $gid = service_match_param('gid');  // GreatId el id del maestro a quien se le va a marcar como buen profesor

    $answers = 0;
    foreach($dataset as $d) {
        if ($d['answer'] > 0)
            $answers++;
    }
    if ($answers < count($dataset))
        service_end(Status::Error, 'Completa la encuesta para guardar tus resultados');

    
    if (USER_LEVEL == UserType::Student && $cid > 0) {
        
        $uid = $USER->id;

        if(!empty($note)) {
            $query = "INSERT INTO poll_teachers_notes (cid, uid, note) VALUES (" . $cid . ", " . $USER->id . ", :note)";
            $params = ['note' => $note];
            service_db_insert($query, $params);
        }

        //  Si se marcó como buen maestro el maestro de la encuesta (GreatuserId)
        if($gid > 0) {

            //  Borramos a todo lo que tenga que ver con
            $query = "DELETE FROM poll_teachers_greatest WHERE cid = :cid AND uid = :uid";
            $params = ['uid' => $USER->id, 'cid' => $lid];
            service_db_insert($query, $params);

            $query = "INSERT INTO poll_teachers_greatest (cid, uid, tid) VALUES (:cid, :uid, :tid)";
            $params['tid'] = $gid;
            service_db_insert($query, $params);
        }

        $query = "INSERT INTO poll_teachers (cid, uid, qindex, answer) VALUES ";
        $params = [];

        for($i = 0; $i < count($dataset); $i++) {
            $query .= '(' . $cid . ', ' . $USER->id . ', :qi_' . $i . ', :ans_' . $i . '),';
            $params['qi_' . $i] = $dataset[$i]['qindex'];
            $params['ans_' . $i] = $dataset[$i]['answer'];
        }
        $query = substr($query, 0, -1);
        
        if ( service_db_insert($query, $params) ) {
            service_end(Status::Success, 'Gracias por responder esta encuesta.');
        } else {
            service_end(Status::Warning, 'No pudimos guardar tus datos que proporcionaste, intenta de nuevo mas tarde.');
        }
    }

    service_end(Status::Error, 'Solo los alumnos pueden responder encuestas');