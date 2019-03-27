<?php
	//	Terminamos para que no se abra sin login...
    if ( !defined('MAKE') ) die();

    $last = service_match_param('last');
    $last = $last > 0 ? $last : 0;
    
    $s = service_match_param('s');


    $ORDERS = [ 'c.id', 'c.fullname', 't.firstname', 't.lastname', 'cr.name', 'cg.name' ];
	$order = service_match_param('order'); // Asc Desc
	$order_by = service_match_param('order_by'); // Column

	$order = $order == 'ASC' ? 'ASC' : 'DESC';
	if( !in_array($order_by, $ORDERS, true) ){
		$order = 'DESC';
		$order_by = 'c.id';
	}

	$the_order = $order_by . ' ' . $order .
        ($order_by == 'cr.name' ? ', cg.name ' . $order : '');
        
    
    if (USER_LEVEL > UserType::User) {

        $params = ['uid' => $USER->id];

        if (USER_LEVEL == UserType::Student) {
            $query = "SELECT
                c.id,
                c.fullname name,
                t.firstname,
                t.lastname,
                IF(p.id IS NULL, 0, 1) status
            FROM 
                info_user u
                LEFT JOIN mdl_user_enrolments ue ON ue.userid = u.uid
                LEFT JOIN mdl_enrol e ON e.id = ue.enrolid
                LEFT JOIN mdl_course c ON c.id = e.courseid

                LEFT JOIN mdl_course_categories cg ON cg.id = c.category
                LEFT JOIN mdl_course_categories cr ON cr.path = SUBSTRING_INDEX(cg.path, '/', 2) AND cr.depth = 1
                
                LEFT JOIN (SELECT * FROM poll_teachers WHERE answer > 0 GROUP BY uid) p ON p.cid = c.id AND p.uid = u.uid
                
                LEFT JOIN mdl_enrol et ON et.courseid = c.id AND et.enrol = 'manual' AND et.status = 0
                LEFT JOIN mdl_user_enrolments uet ON uet.enrolid = et.id
                LEFT JOIN info_user t ON t.uid = uet.userid
                
            WHERE 
                c.visible = 1 AND
                u.uid = :uid " .
                //	Agregamos la busqueda
				( 
					!empty( $s ) 
						? " AND CONCAT_WS(' ', 
							t.firstname, t.lastname, c.fullname,
							cg.name, cr.name) like :s "
						: '' 
				) .
            "ORDER BY " . $the_order;
            
            
        } elseif(USER_LEVEL == UserType::Teacher) {
            $query = "SELECT
                c.id,
                c.fullname name,    
                cr.name area,
                cg.name level
                
            FROM
                mdl_user_enrolments ue
                LEFT JOIN mdl_enrol e ON e.id = ue.enrolid
                LEFT JOIN mdl_course c ON c.id = e.courseid
                LEFT JOIN info_user t ON t.uid = ue.userid
                LEFT JOIN mdl_course_categories cg ON cg.id = c.category
                LEFT JOIN mdl_course_categories cr ON cr.path = SUBSTRING_INDEX(cg.path, '/', 2) AND cr.depth = 1
                
            WHERE 
                c.enddate > 1546322399 AND
                ue.userid = :uid " .
                //	Agregamos la busqueda
				( 
					!empty( $s ) 
						? " AND CONCAT_WS(' ', 
							t.firstname, t.lastname, c.fullname,
							cg.name, cr.name) like :s "
						: '' 
				) .
            "ORDER BY " . $the_order;

        }
        elseif(USER_LEVEL == UserType::Admin) {
            $query = "SELECT
                c.id,
                c.fullname name,
                cr.name area,
                cg.name level,
                t.firstname,
                t.lastname
            FROM
                mdl_course c
                LEFT JOIN mdl_course_categories cg ON cg.id = c.category
                LEFT JOIN mdl_course_categories cr ON cr.path = SUBSTRING_INDEX(cg.path, '/', 2) AND cr.depth = 1
                
                LEFT JOIN mdl_enrol et ON et.courseid = c.id AND et.enrol = 'manual' AND et.status = 0
                LEFT JOIN mdl_user_enrolments uet ON uet.enrolid = et.id
                LEFT JOIN info_user t ON t.uid = uet.userid
            WHERE 
                c.enddate > 1546322399
                AND cr.id > 1 " .
                //	Agregamos la busqueda
				( 
					!empty( $s ) 
						? " AND CONCAT_WS(' ', 
							t.firstname, t.lastname, c.fullname,
							cg.name, cr.name) like :s "
						: '' 
				) .
            "ORDER BY " . $the_order;

            $params = [];
        }
        if( !empty($s) )
            $params['s'] = '%' . $s . '%';

        $polls = service_db_select($query, $params);
        service_end(Status::Success, $polls);
    }
    service_end(Status::Error, 'Al parecer no tienes acceso a las encuestas');