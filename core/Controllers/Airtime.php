<?php

    class Airtime extends ApiAccess{

        //Purchase Airtime
		public function purchaseMyAirtime($body,$networkDetails){

            $details=$this->model->getApiDetails();
 
            //Check Airtime Type
            if($body->airtime_type == "VTU"){$name="Vtu"; $thenetworkId=$networkDetails["vtuId"];} 
            else {$name ="Sharesell"; $thenetworkId=$networkDetails["sharesellId"]; }

            //Get Api Key Details
            $networkname = strtolower($networkDetails["network"]);
            $host = self::getConfigValue($details,$networkname.$name."Provider");
            $apiKey = self::getConfigValue($details,$networkname.$name."Key");

            
            //Check If API Is Is Using N3TData Or Bilalsubs
            if(strpos($host, 'n3tdata') !== false){
                $hostuserurl="https://n3tdata.com/api/user/";
                return $this->purchaseAirtimeWithBasicAuthentication($body,$host,$hostuserurl,$apiKey,$thenetworkId);
            }

            if(strpos($host, 'bilalsadasub') !== false){
                $hostuserurl="https://bilalsadasub.com/api/user/";
                return $this->purchaseAirtimeWithBasicAuthentication($body,$host,$hostuserurl,$apiKey,$thenetworkId);
            }

            $hostuserurl="https://api.savebills.com.ng/api/auth/airtime";
            // ------------------------------------------
            //  Purchase Airtime
            // ------------------------------------------
            
            if($body->ported_number == "false"){$ported_number="false";} else{$ported_number="true";}

            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => $host,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "network": "'.$thenetworkId.'",
                "amount": "'.$body->amount.'",
                "mobile_number": "'.$body->phone.'",
                "Ported_number":'.$ported_number.',
                "request-id" : "'.$body->ref.'",
                "airtime_type": "'.$body->airtime_type.'"
            }',
             
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: Token $apiKey"
            ),
            ));

            $exereq = curl_exec($curl);

            $err = curl_error($curl);
            
            if($err){
                $response["status"] = "fail";
                $response["msg"] = "Server Connection Error";
                file_put_contents("airtime_error_log2.txt",json_encode($response).$err);
                curl_close($curl);
                return $response;
            }

            $result=json_decode($exereq);
            curl_close($curl);

            if($result->Status=='successful' || $result->Status=='processing'){
                $response["status"] = "success";
            }
            else{
                $response["status"] = "fail";
                $response["msg"] = "Server/Network Error";
                file_put_contents("airtime_error_log.txt",json_encode($result));
            }

            return $response;
		}

        //Purchase Airtime
		public function purchaseAirtimeWithBasicAuthentication($body,$host,$hostuserurl,$apiKey,$thenetworkId){

			
            $response = array();
            
            // ------------------------------------------
            //  Get User Access Token
            // ------------------------------------------
            
            if($body->ported_number == "false"){$ported_number=false;} else{$ported_number=true;}


            // ------------------------------------------
            //  Purchase Airtime
            // ------------------------------------------
        
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL =>"https://api.savebills.com.ng/api/auth/airtime",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "network": "'.$thenetworkId.'",
                "amount": "'.$body->amount.'",
                "number": "'.$body->phone.'",
                "bypass":"'.$ported_number.'",
                "refid" : "'.$body->ref.'",
                "plan_type": "'.$body->airtime_type.'"
            }',
            
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "x-api-key: SB.KEY3.5532838074845146e+43"
            ),
            ));

            $exereq = curl_exec($curl);

            $err = curl_error($curl);
            
            if($err){
                $response["status"] = "fail";
                $response["msg"] = "Server Connection Error"; //.$err;
                file_put_contents("basic_airtime_error_log2.txt",json_encode($response));
                curl_close($curl);
                return $response;
            }

            $result=json_decode($exereq);
            curl_close($curl);

            if($result->status==1 || $result->status=='success'){
                $response["status"] = "success";
            }
            else{
                $response["status"] = "fail";
                $response["msg"] = "Server/Network Error";
                file_put_contents("basic_airtime_error_log.txt",json_encode($result));
            }

            return $response;
		}


    }

?>