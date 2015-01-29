<?php

class UserModel {

	/**
     * Session token renewer
     */
	public function checkToken($token)
    {
        $app = \Slim\Slim::getInstance();

        if(!empty($token)) {
            $token = base64_decode($token . '=');
            $token_arr = explode('::', $token, 3); // TODO: validate username and timestamp

            $app->currentUser = $token_arr[0];
            if($token_arr[1] > time()) {
                $raw_token =  $token_arr[0] . '::' . $token_arr[1];
                $new_token = hash_hmac('SHA512', $raw_token . SESSION_TOKEN_SALT, SESSION_TOKEN_KEY);
                if($new_token === $token_arr[2])
                {
                    return true;
                }
            } else {
                return $this->renew($token_arr[0], $token_arr[2]);
            }
        }
        return false;
    }

    /**
     *
     */
    private function renew($user, $token = null)
    {
        if(!empty($token)) {
            $db = Database::getInstance();
            $query = $db->prepare('SELECT session_token FROM users WHERE user_name = :user LIMIT 1');
            $query->execute(array(':user' => $user));
            $res = $query->fetch();
            if($token === $res->session_token) {
                $raw_token =  $user . '::' . (time() + 1800); // set token for half an hour from now
                $token_hash = hash_hmac('SHA512', $raw_token . SESSION_TOKEN_SALT, SESSION_TOKEN_KEY); // hash with key
                $sql = "UPDATE users SET session_token = :token
                        WHERE user_name = :user_name";
                $tkn = $db->prepare($sql);
                $tkn->execute(array(':user_name' => $user, ':token' => $token_hash));
                if($tkn->rowCount() > 0) {
                    return substr(base64_encode($raw_token . "::" . $token_hash), 0, -1); // encode and remove last character
                }
            }
        }
        return false;
    }

    /**
     * Get current user from token
     */
    public static function getUserId($user = null)
    {
        $db = Database::getInstance();

        if(empty($user)) {
            $app = \Slim\Slim::getInstance();
            $req = $app->request();
            $user = $app->currentUser;
        }

        $sql = "SELECT user_id FROM users
                    WHERE user_name = :user_name LIMIT 1";

        $tkn = $db->prepare($sql);
        $tkn->execute(array(':user_name' => $user));
        $result = $tkn->fetch();

        if($result !== false) {
            return $result->user_id;
        }
        return false;
    }
}