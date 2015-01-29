<?php

class FileModel extends ClassModel {

    /**
     *
     */
    public function listFiles()
    {
        $db = Database::getInstance();

        $query = $db->prepare("SELECT id as _id, CONCAT(server , path) as path, title, FROM_UNIXTIME(created) as added, user_name as added_by FROM files LEFT JOIN users ON files.created_by = users.user_id LIMIT 10");
        $query->execute();
        $res = $query->fetchAll();
        if(count($res) > 0) {
            return $res;
        } else {
            return [];
        }
        return false;
    }

    /**
     *
     */
    public function getFile($id)
    {
        $db = Database::getInstance();

        $query = $db->prepare("SELECT files.id as _id, CONCAT(server , path) as path, title, FROM_UNIXTIME(created) as added, user_name as added_by FROM files LEFT JOIN users ON files.created_by = users.user_id WHERE files.id = :id LIMIT 1");
        $query->execute(array(':id' => $id));
        $res = $query->fetch();
        if($res !== false) {
            $query_meta = $db->prepare("SELECT tag as `key`, value FROM files_metadata LEFT JOIN bind_files_meta ON files_metadata.id = bind_files_meta.meta_id WHERE file_id = :id");
            $query_meta->execute(array(':id' => $id));
            $meta = $query_meta->fetchAll();
            if(count($meta) < 1) {
                $meta = false;
            }
            $res->meta = $meta;
            return $res;
        }
        return false;
    }

    /**
     *
     */
    public function addFile()
    {
        $db = Database::getInstance();
        $app = \Slim\Slim::getInstance();

        $keys = '';
        $values = '';
        foreach($app->request()->post() as $key => $value) {
            if($key !== 'created' || $key !== 'created_by') {
                $keys .= ', `' . $key . '`';
                $values .= ', :' . $key;
            }
        }
        $values = trim($values, ', ');
        $keys = trim($keys, ', ');
        $sql = "INSERT INTO files($keys, `created`, `created_by`) VALUES($values, :created, :created_by)";
        $query = $db->prepare($sql);
        $query->execute(array_merge($app->request()->post(), array('created' => time(), 'created_by' => 1)));

        if($query->rowCount() > 0) {

                // TODO: add file metadata records !!!!

            return true;
        }
        return false;
    }

}