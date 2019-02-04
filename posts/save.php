<?php
    //	Terminamos para que no se abra sin login...
    if ( !defined('MAKE') ) die();

    // Obtenemos los datos
    $post = service_match_param('post');
    $post = json_decode($post, true);

    if( USER_LEVEL >= UserType::Admin && is_array($post))
	{
        //  Valores necesarios
        if( empty($post['title']) )
            service_end(Status::Error, 'Se requiere el titulo de la publicación');

        $q = "";
        $p = [];

        //  Si se está actualizando el post
        if($post['id'] > 0) {
            $q = "UPDATE inside_posts SET 
                title = :title,
                content = :content,
                type = :type,
                status = :status,
                uid = :uid,
                iid = :iid
                WHERE id = :id";
            $p = [
                'title' => $post['title'],
                'content' => $post['content'],
                'type' => $post['type'] > 0 ? $post['type'] ? 0,
                'status' => $post['status'],
                'uid' => $info_user['id'],
                'iid' => $post['iid']
            ];

            if(service_db_insert( $q, $p )) {
                //  Quitamos las imagenes que teniamos en el post, para meter las nuevas.
                service_db_insert("DELETE FROM inside_pi_list WHERE pid = :pid", ['pid' => $post['id']]);

                //  Preparamos el string de insert
                $inserts = "";
                foreach($post['images'] as $image) {
                    if($image['id'] > 0)
                        $inserts = "(" . $post['id'] . ", " . $image['id'] . "),";
                    }
                $inserts = substr($inserts, 0, -1);
                                
                service_db_insert("INSERT INTO inside_pi_list (pid, iid) VALUES " . $inserts);
                service_end(Status::Success, $post['id'])
            }
            else
                serice_end(Status::Error, 'No se pudo guardar la publicación.<br> ERR: ' . service_db_error()[2]);
        }

        //  Se está creando un post
        else {            
            $at = time();

            $q = "INSERT INTO inside_posts (title, content, type, at, status, uid, iid) 
                VALUES(:title, :content, :type, :at, :status, :uid, :iid)";
            $p = [
                'title' => $post['title'],
                'content' => $post['content'],
                'type' => $post['type'] > 0 ? $post['type'] ? 0,
                'at' => $at,
                'status' => $post['status'],
                'uid' => $info_user['id'],
                'iid' => $post['iid']
            ];

            if(service_db_insert( $q, $p )) {
                $r = service_db_select("SELECT id FROM inside_posts WHERE at = " . $at);
                $post['id'] = !empty($r) ? $r[0]['id'] : '';
            }
            else
                serice_end(Status::Error, 'No se pudo guardar la publicación.<br> ERR: ' . service_db_error()[2]);
        }
            
	}
	else
		service_end(Status::Error, 'Se requiere nivel de administrador para realizar la operación');