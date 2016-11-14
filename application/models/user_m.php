<?php
class User_m extends Core_db
{

    function __construct()
    {
        parent::__construct();
        $this->table = 'users';
    }

    public function addLoginAttempt($userid)
    {
        $query = "UPDATE users
                  SET usr_pw_req_num = CASE 
                        WHEN usr_pw_req_date is null
                        OR usr_pw_req_date < NOW() - INTERVAL '10 minutes' THEN 1 
                        ELSE usr_pw_req_num+1 END,
                      usr_pw_req_date = NOW()
                  WHERE usr_id = ?";
        return (false !== $this->db->query($query, $userid));
    }

    public function createResetToken($userid)
    {
        $newtoken = hash('sha256', random_bytes(64));
        $query = "UPDATE users
                  SET usr_pw_reset_token = ?,
                      usr_pw_reset_date = NOW()
                  WHERE (usr_id = ?)";
        $result = $this->db->query($query, array($newtoken, $userid));
        return (false != $result ? $newtoken : false);
    }

    public function changePassword($userid, $newpass)
    {
        $pass_hash = password_hash($newpass, PASSWORD_DEFAULT);
        $query = "UPDATE users SET usr_pass = ? WHERE (usr_id = ?)";
        return ($this->db->query($query, array($pass_hash, $userid)) !== false);
    }

    public function generateRandomString($length = 10) {
        $characters = '!%@#-=0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function resetPassword($token)
    {
        $query = "SELECT * FROM users WHERE usr_pw_reset_token = ?";
        $r_user = $this->db->query($query, $token);
        $user = (false != $r_user ? $r_user->fetchRow() : false);

        $randomPass = $this->generateRandomString(20);
        if ($user)
            $r = $this->changePassword($user->userid, $randomPass);

        return ($user && $r_user ? $randomPass : false);
    }

    public function resetLoginAttempts($userid)
    {
        $query = "UPDATE users SET usr_pw_req_num = 0
                  WHERE (usr_id = ?)";
        return (false !== $this->db->query($query, $userid));
    }

    public function addUser($username, $password, $mail, $plan_id)
    {
        $query = "INSERT INTO users (usr_login, usr_mail, usr_pass, usr_disabled, usr_role, usr_plan, usr_joined)
                  VALUES (?, ?, ?, true, (select role_id from role where role_name = 'user'), ?, NOW());";
        $result = $this->db->query($query, array(
                strtolower($username),
                strtolower($mail),
                password_hash($password, PASSWORD_DEFAULT),
                $plan_id
            ));
        return (false !== $result);
   }

    public function getRoles()
    {
        $roles = $this->db->query("SELECT * FROM roles;");

        return $roles ? $roles->getResult() : false;
    }

    public function getRoleById($id)
    {
        $role = $this->db->query("SELECT * FROM roles WHERE (role_id = ?)", $id);

        return $role ? $role->getRow() : false;
    }

    public function getUserById($id)
    {
        $query = "SELECT *, plan_name as plan,
                  (SELECT conn_date FROM connections ORDER BY conn_date DESC LIMIT 1) as last_seen
                  FROM users 
                  INNER JOIN plan ON (usr_plan = plan_id)
                  LEFT OUTER JOIN connections ON (conn_user = usr_id)
                  WHERE (usr_id = ?);";
        $user = $this->db->query($query, $id);

        return $user ? $user->getRow() : false;
    }

    public function getUserByLogin($login)
    {
        $query = "SELECT * FROM users WHERE (usr_login = ?);";
        $user = $this->db->query($query, strtolower($login));

        return $user ? $user->getRow() : false;
    }

    public function delUser($id)
    {
        $query = "DELETE FROM users WHERE (usr_id = ?);";
        $this->db->query($query, $id);
        $user = $this->db->query("SELECT * FROM users WHERE (usr_id = ?);", $id);

        return $user == false;
    }

    public function getUsers(){
        $query = "SELECT * FROM users;";
        $users = $this->db->query($query);

        return $users ? $users->getResult() : false;
    }
}
