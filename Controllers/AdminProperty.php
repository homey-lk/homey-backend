<?php

namespace Controllers;

use Exception;

require_once('Core/Controller.php');

use Core\Controller as Controller;


class AdminProperty extends Controller
{


    //get traffic data for chart
    public function PendingApproval($params, $param)
    {
        try {
            $userId = (string)$param['userId'];
            if (!$this->authenticateAdmin($param['token'], $userId)) throw new Exception("Authentication failed.");
            if (isset($params[0]) && $params[0] == 'summary') {
                $stmt = $this->execute($this->get('property', ['_id', 'title', 'user_id', 'created'], ("property_status = 0 ORDER BY created")));
                $this->resolve(json_encode($stmt->fetchAll()), 200);
            } else {
                $data = [
                    'price',
                    'title',
                    'rental_period as rentalPeriod',
                    'key_money as keyMoney',
                    'minimum_period as minimumPeriod',
                    'available_from as availableFrom',
                    'description',
                    'location',
                    'created',
                    'user_id as userId',
                    'facilities',
                ];
                // 'city_id',
                // 'property_type_id',

                $stmt = $this->execute($this->get('property', $data, ("_id = '{$param['id']}' AND property_status = 0")));

                $this->resolve(json_encode($stmt->fetch()), 200);
            }
        } catch (Exception $err) {
            $this->reject('{
                "status": "500",
                "error": "true",
                "message": "' . $err->getMessage() . '"
            }', 200);
        }
    }

    //Approve the property
    public function Approve($params, $param)
    {
        try {
            $userId = (string)$param['userId'];
            if (!$this->authenticateAdmin($param['token'], $userId)) throw new Exception("Authentication failed.");
            $stmt = $this->execute($this->update('property', ['property_status' => 1], ("_id = '{$param['propertyId']}'")));

            $this->resolve('{
                        "status": "204",
                        "message": "Approved"
                }', 200);
            $this->addLog($param['propertyId'] . " Approved by " . $userId, "property-approved");
        } catch (Exception $err) {
            $this->addLog(" Approving request failed", "property-approve-failed", (string)$err->getMessage());
            $this->reject('{
                "status": "500",
                "error": "true",
                "message": "' . $err->getMessage() . '"
            }', 200);
        }
    }


    //Reject the property
    public function RejectApproval($params, $param)
    {
        try {
            $userId = (string)$param['userId'];
            if (!$this->authenticateAdmin($param['token'], $userId)) throw new Exception("Authentication failed.");
            $stmt = $this->execute($this->update('property', ['property_status' => 2], ("_id = '{$param['propertyId']}'")));

            $this->resolve('{
                            "status": "204",
                            "message": "Rejected"
                    }', 200);
            $this->addLog($param['propertyId'] . " Rejected by " . $userId, "property-rejected");
        } catch (Exception $err) {
            $this->addLog(" Rejectiong request failed", "property-rejection-failed", (string)$err->getMessage());
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
    //                 case 'pending-approval':
    //                     $stmt = $this->execute($this->get('property', ['_id', 'title', 'user_id', 'created'], ("property_status = 0 ORDER BY created")));

    //                     $this->resolve(json_encode($stmt->fetchAll()), 200);
    //                     break;

    //                 case 'approve':
    //                    
    //                     break;

    //                 case 'reject':
    //                    

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
