<?php
	//	Terminamos para que no se abra sin login...
    if ( !defined('MAKE') ) die();

    $cid = service_match_param('cid');
    $can = false;

    //  Sacamos primero los datos generales de la encuesta
        $query = "SELECT
                c.id,
                c.fullname name,

                cr.name area,
                cg.id levelid,
                cg.name level,

                t.uid tid,
                t.firstname tfirstname,
                t.lastname tlastname,
                f.url turl,

                g.tid gid,
                gu.firstname gfirstname,
                gu.lastname glastname,
                gf.url gurl


            FROM
                mdl_course c
                LEFT JOIN mdl_course_categories cg ON cg.id = c.category
                LEFT JOIN mdl_course_categories cr ON cr.path = SUBSTRING_INDEX(cg.path, '/', 2) AND cr.depth = 1
                
                LEFT JOIN mdl_enrol et ON et.courseid = c.id AND et.enrol = 'manual' AND et.status = 0
                LEFT JOIN mdl_user_enrolments uet ON uet.enrolid = et.id
                LEFT JOIN info_user t ON t.uid = uet.userid
                LEFT JOIN inside_files f ON f.id = t.fid

                LEFT JOIN poll_teachers_greatest g ON g.cid = cg.id
                LEFT JOIN info_user gu ON gu.uid = g.tid
                LEFT JOIN inside_files gf ON gf.id = gu.fid
            WHERE 
                c.id = :cid;";
        $params = ['cid' => $cid];
        $poll = service_db_select($query, $params);
    
    //  Si existe la encuesta seleccionada
    if (!empty($poll)) {
        $poll = $poll[0];

        if (USER_LEVEL == UserType::Student) {
            $poll['dataset'] = [];
            service_end(Status::Success, $poll);
        }

    
        if (USER_LEVEL == UserType::Teacher) {
            $uid = $USER->id;

            if( $poll['tid'] != $uid ) {
                service_end(Status::Error, 'No tienes autorización para ver esta encuesta');
            }

            $query = "SELECT 
                g.*,
                u.firstname,
                u.lastname,
                u.url
            FROM  
                poll_teachers_greatest g 
                LEFT JOIN info_user u ON u.uid = g.uid
            WHERE g.cid = :cid AND u.tid = :uid";

            $params = [
                'cid' => $poll['levelid'],
                'uid' => $USER->id
            ];

            $poll['greats'] = service_db_select($query, $params);

            $can = true;
        }

        if(USER_LEVEL == UserType::Admin || $can) {
            
            $query = "SELECT * FROM  poll_teachers WHERE  cid = :cid";
            $params = ['cid' => $cid];

            $poll['dataset'] = service_db_select($query, $params);

            //  Si $can es falsa, quiere decir que no era un maestro y soy administrador
            if($can === false) {

                //  Saco quien lo marcó como buen docente
                $query = "SELECT 
                    g.*,
                    u.firstname,
                    u.lastname,
                    f.url
                    FROM  
                        poll_teachers_greatest g 
                        LEFT JOIN info_user u ON u.uid = g.uid
                        LEFT JOIN inside_files f ON f.id = u.fid
                    WHERE g.cid = :cid";
                $params = [
                    'cid' => $poll['levelid']
                ];

                $poll['greats'] = service_db_select($query, $params);
            }

            $query = "SELECT
                u.id,
                u.firstname,
                u.lastname,
                n.note
            FROM
                poll_teachers_notes n 
                LEFT JOIN info_user u ON u.uid = n.uid
            WHERE 
                n.cid = :cid";
            $params = ['cid' => $cid];
            
            $poll['notes'] = service_db_select($query, $params);

            service_end(Status::Success, $poll);
        }
        service_end(Status::Success, $poll);
    }
    else {
        service_end(Status::Error, 'No se encuentran registros para esta encuesta.');    
    }

    service_end(Status::Error, 'Al parecer no tienes acceso a las encuestas');