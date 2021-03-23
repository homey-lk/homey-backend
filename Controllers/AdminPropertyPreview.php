<?php

namespace Controllers;

use Exception;

require_once('Core/Controller.php');

use Core\Controller as Controller;

class AdminPropertyPreview extends Controller
{

    public function post()
    {
        try {
            if (isset($this->params[0])) {
                if (!$this->authenticate()) throw new Exception("Unautherized request.");
                switch ($this->params[0]) {
                    case 'pending-approval':
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
                            'user_id userId',
                            'facilities',
                        ];
                        // 'city_id',
                        // 'property_type_id',

                        $stmt = $this->execute($this->get('property',$data, ("_id = '{$param['id']}' AND property_status = 0")));

                        $this->resolve(json_encode($stmt->fetch()),200);

                        break;

                    default:
                        throw new Exception("Invalid Request");
                }
            } else throw new Exception("Invalid Parmeters");
        } catch (Exception $err) {
            $this->reject('{
                "status": "500",
                "error": "true",
                "message": "' . $err->getMessage() . '"
        }',200);

        } //End of try catch

    } //End of GET

    // Authenticate Admin 
    private function authenticate()
    {
        if (isset($param['userId'], $param['token'])) {
            if ($this->authenticateAdmin($param['userId'], $param['token'])) return true;
            else return false;
        } else return false;
    }
}//End of Class
