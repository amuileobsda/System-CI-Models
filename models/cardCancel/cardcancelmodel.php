<?
class CardCancelModel extends CI_Model {

    public function cardCancelSugi($resvNo, $user_id, $room_code,$paymethod) {
        $param1 = array($user_id);
        $query = "
            SELECT
                CARD_KEY,
                ROOM_KEY_YN
            FROM
                ADMIN_XXXX 
            WHERE
                USER_ID = ?
        ";
        
        $adminData = $this->db->query($query, $param1)->result_array()[0];
        
        $CPID = $adminData['CARD_KEY'];
        
      
        if($adminData['ROOM_KEY_YN'] === "Y") {
            $param2 = array($user_id, $room_code);
            $query2 = "
                SELECT
                    CARD_KEY
                FROM
                    ROOM_XXX 
                WHERE
                    USER_ID = ?
                AND ROOM_CODE = ?
            ";

            $roomData = $this->db->query($query2, $param2)->result_array()[0];

            if(!empty($roomData['CARD_KEY'])) {
                $CPID = $roomData['CARD_KEY'];
            }

        }
        
 
        $param3 = array($user_id, $CPID);
        $query3 = "
                SELECT
                    PKEY
                FROM
                    ADMIN_CARD_INFO 
                WHERE
                    USER_ID = ?
                AND CPID = ?
            ";
            
        $PKEY = $this->db->query($query3, $param3)->result_array()[0]['PKEY']; 
	    
        $response = $this->paymentCancelReady($CPID, $PKEY,$paymethod);
		
        $payjoa = $this->getDaoutrxNo($CPID, $resvNo, $user_id);
       
        $data = array(
            "RETURNURL" => $response['RETURNURL'],
            "TOKEN" => $response['TOKEN'],
            "TRXID" => $payjoa['DAOUTRX'],
            "AMOUNT" => (string) $payjoa['AMOUNT'],
            "CPID" => $CPID,
            "PKEY" => $PKEY,
        );
        
        $res = $this->paymentCancel($data);
        
       //return $res['RESULTCODE'] === "0000" ? 'Pay01Cancel' : 'CANCELFAIL';


       if($res['RESULTCODE'] != "0000"){
            return $res;
        
       }
       return $res['RESULTCODE'] === "0000" ? 'Pay01Cancel' : 'CANCELFAIL';
    }

    public function paymentCancelReady($CPID, $PKEY,$paymethod) {
	$url = 'https://apitest.kiwoompay.co.kr/pay/ready'; //개발

        $body_data = array(
            "CPID" => $CPID,
            "PAYMETHOD" => $paymethod,
            "CANCELREQ" => "Y"
        );

       

        $body = json_encode($body_data);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=EUC-KR', 'Authorization:' . $PKEY));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


	    $response = curl_exec($ch);

		return $response;

        curl_close($ch);
    }

    public function getDaoutrxNo($CPID, $resvNo, $user_id) {
        $param = array($CPID, $resvNo, $user_id);
        $query = "
                SELECT DAOUTRX, AMOUNT FROM RESV_CARD_XXXX
                WHERE CPID = ?
                AND RESV_NO = ?
                AND USER_ID = ?
            ";

        return $this->db->query($query, $param)->result_array()[0];
    }

    public function paymentCancel($data) {
        $url = $data['RETURNURL'];

        $body_data = array(
            "CPID" => $data['CPID'],
            "TRXID" => $data['TRXID'],
            "AMOUNT" => $data['AMOUNT'],
            "CANCELREASON" => "중복예약"
        );

        $body = json_encode($body_data);
        
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=EUC-KR', 'Authorization:' . $data['PKEY'], 'TOKEN:' . $data['TOKEN']));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        $body_json = json_decode($body, true);

        curl_close($ch);
        

        return $body_json;
    }
}
