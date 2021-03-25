<?php

namespace Controllers;

use Exception;

require_once('Core/Controller.php');

use Core\Controller as Controller;



class AdminUsers extends Controller
{


    //get user
    public function GetUser($params, $param)
    {
        try {
            $userId = (string)$param['userId'];
            if (!$this->authenticateAdmin($param['token'], $userId)) throw new Exception("Authentication failed.");
            $stmt = $this->execute($this->join(
                'login',
                [
                    'login.email',
                    'login.mobile',
                    'login.user_status as status',
                    'user.first_name as firstName',
                    'user.last_name as lastName'
                ],
                ", user WHERE login.user_id = user.user_id AND user.user_id = " . (int)$param['profile'] . ""
            ));

            $result['userData'] = $stmt->fetch();

            $stmt = $this->execute($this->get('property', ['_id', 'title', 'created'], "user_id = " . (int)$param['profile'] . " AND privated = 0"));

            $result['ownPropertyData'] = $stmt->fetchAll();

            $this->resolve(json_encode($result), 200);
        } catch (Exception $err) {
            $this->reject('{
                "status": "500",
                "error": "true",
                "message": "' . $err->getMessage() . '"
            }', 200);
        }
    }

    // public function post()
    // {
    //     try {
    //         if (isset($this->params[0])) {
    //             if (!$this->authenticate()) throw new Exception("Unautherized request.");
    //             switch ($this->params[0]) {
    //                 case 'all-users':

    //                     $stmt = $this->execute(Login::join(
    //                         [
    //                             'user.user_id as userId',
    //                             'login.email',
    //                             'login.mobile',
    //                             'login.user_status as status',
    //                             'user.first_name as firstName',
    //                             'user.last_name as lastName'
    //                         ],
    //                         (", user WHERE login.user_id = user.user_id AND login.user_type = 0")
    //                     ));

    //                     $this->resolve(json_encode($stmt->fetchAll()), 200);
    //                     break;

    //                 case 'all-admins':

    //                     $stmt = $this->execute(Login::join(
    //                         [
    //                             'user.user_id as userId',
    //                             'login.email',
    //                             'login.mobile',
    //                             'login.user_status as status',
    //                             'user.first_name as firstName',
    //                             'user.last_name as lastName'
    //                         ],
    //                         (", user WHERE login.user_id = user.user_id AND login.user_type = 1")
    //                     ));


    //                     $this->resolve(json_encode($stmt->fetchAll()), 200);
    //                     break;

    //                 case 'get':



    //                     break;


    //                 default:
    //                     throw new Exception("Invalid Request");
    //             }
    //         } else throw new Exception("Invalid Parmeters");
    //     } catch (Exception $err) {

    //         $this->reject('{
    //             "status": "500",
    //             "error": "true",
    //             "message": "' . $err->getMessage() . '"
    //     }', 200);
    //     } //End of try catch

    // } //End of GET

    // // Authenticate Admin 
    // private function authenticate()
    // {
    //     if (isset($param['userId'], $param['token'])) {
    //         if ($this->authenticateAdmin($param['userId'], $param['token'])) return true;
    //         else return false;
    //     } else return false;
    // }
}//End of Class
