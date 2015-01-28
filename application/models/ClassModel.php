<?php

class ClassModel {

    /**
     *
     */
    public function listClasses()
    {
        $db = Database::getInstance();
        $query = $db->prepare('SELECT id as _id, class_name as class, type FROM classes LEFT JOIN class_types ON classes.class_type = class_types.id');
        $query->execute();
        $res = $query->fetchAll();
        if(count($res) > 0) {
            return $res;
        }
        return false;
    }

    /**
     *
     */
    public function listItems($name)
    {
        $db = Database::getInstance();

        $classExists = false;
        foreach($this->listClasses() as $row) {
            if($row->class === $name) {
                $classExists = true;
                break;
            }
        }
        if($classExists) {
            $query = $db->prepare("SELECT id as _id, CONCAT(server , path) as path, title, FROM_UNIXTIME(created) as added, user_name as added_by FROM $name LEFT JOIN users ON {$name}.created_by = users.user_id");
            $query->execute();
            $res = $query->fetchAll();
            if(count($res) > 0) {
                return $res;
            }
        }
        return false;
    }

    /**
     *
     */
    public function showItem($name, $id)
    {
        $db = Database::getInstance();

        $classExists = false;
        foreach($this->listClasses() as $row) {
            if($row->class === $name) {
                $classExists = true;
                break;
            }
        }
        if($classExists) {
            $query = $db->prepare("SELECT $name.id as _id, CONCAT(server , path) as path, title, FROM_UNIXTIME(created) as added, user_name as added_by FROM $name LEFT JOIN users ON {$name}.created_by = users.user_id WHERE id = :id LIMIT 1");
            $query->execute(array(':id' => $id));
            $res = $query->fetch();
            if(count($res) > 0) {
                $query_meta = $db->prepare("SELECT tag as `key`, value FROM {$name}_metadata LEFT JOIN bind_{$name}_meta ON {$name}_metadata.id = bind_{$name}_meta.meta_id WHERE file_id = :id");
                $query_meta->execute(array(':id' => $id));
                $meta = $query_meta->fetchAll();
                if(count($meta) < 1) {
                    $meta = null;
                }
                $res->meta = $meta;
                return $res;
            }
        }
        return false;
    }

    /**
     *
     */
    public function addItem($name)
    {
        $db = Database::getInstance();
        $app = \Slim\Slim::getInstance();

        $classExists = false;
        foreach($this->listClasses() as $row) {
            if($row->class === $name) {
                $classExists = true;
                break;
            }
        }
        if($classExists) {
            $keys = '';
            $values = '';
            foreach($app->request()->post() as $key => $value) {
                if($key !== 'created' || $key !== 'created_by')
                $keys .= ', `' . $key . '`';
                $values .= ', :' . $key;
            }
            $values = trim($values, ', ');
            $keys = trim($keys, ', ');
            $sql = "INSERT INTO $name($keys, `created`, `created_by`) VALUES($values, :created, :created_by)";
            $query = $db->prepare($sql);
            $query->execute(array_merge($app->request()->post(), array('created' => time(), 'created_by' => 1)));

            if($query->rowCount() > 0) {
                return true;
            }
        }
        return false;
    }

}