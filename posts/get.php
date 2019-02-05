<?php
    //	Terminamos para que no se abra sin login...
    if ( !defined('MAKE') ) die();

    $id = service_match_param('id');

    if( $id > 0 ) {
        //  Sacamos la info base del post
        $post = service_db_select("SELECT * FROM inside_posts WHERE id = :id", ['id' => $id]);

        //  Si es que hay un al menos un post
        if( !empty($post) ) {

            //  Lo ponemos como post principal
            $post = $post[0];

            //  Cargamos la imagen como null
            $post['image'] = null;
            
            //  Si tenemos un fid en el post
            if($post['fid'] > 0)
            {

                //  Seleccionamos la imagen de acuerdo al fid
                $q = "SELECT * FROM inside_files WHERE id = :fid";
                $p = [ 'fid' => $post['fid'] ];
                $r = service_db_select($q, $p);

                // Y lo asignamos a image
                $post['image'] = !empty($r) ? $r[0] : null;
            }

            //  Ahora sacamos todas las imagenes de la galeria (images)
            $q = "SELECT i.* 
                FROM 
                    inside_pi_list l
                    LEFT JOIN inside_files i ON i.id = l.fid
                WHERE l.pid = :pid";
            $p = ['pid' => $post['id']];
            $r = service_db_select($q, $p);

            //  Y las asignamos
            $post['images'] = !empty($r) ? $r : [];

            service_end(Status::Success, $post);
        }
    }
    else 
        service_end(Status::Error, 'No se encuentra el post que has solicitado');