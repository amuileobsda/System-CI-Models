<?php
class SmsMngtModel extends CI_Model {
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    /**
     * 자주쓰는문자 조회
     * */
    function selectSmsOften(){
        $param = array($this->session->userdata('userId'), $this->input->post('SEARCH_DATE'));
        $query = "
				SELECT SEQ
				     , SMS_CONT
				  FROM SMS_OFTEN
				 WHERE USER_ID = ?
				";
        return $this->db->query($query, $param)->result_array();
    }


    /**
     * 자주쓰는문자 시퀀스
     * */
    function selectSmsOftenSeq(){
        $param = array($this->session->userdata('userId'));
        $query = "
				SELECT IFNULL(MAX(SEQ), 0)+1 SEQ 
				  FROM SMS_OFTEN  
				 WHERE USER_ID = ?
				";
        $result = $this->db->query($query, $param)->result_array();
        return $result[0]['SEQ'];
    }

    /**
     * 자주쓰는문자 등록
     * */
    function insertSmsOften($seq){
        $this->db->trans_begin();

        $param = array($this->session->userdata('userId'), $seq, $this->input->post('SMS_CONT') , $this->session->userdata('userId'));
        $query = "
				INSERT INTO SMS_OFTEN
				( USER_ID
				, SEQ
				, SMS_CONT
				, REG_USER
				, REG_DATE
				) 
				VALUES 
				( ?
				, ?
				, ?
				, ?
				, NOW()
				)
				";

        $this->db->query($query, $param);
        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
        }
        else
        {
            $this->db->trans_commit();
            return true;
        }
    }

    /**
     * 자주쓰는문자 삭제
     * */
    function deleteSmsOften(){
        $this->db->trans_begin();

        $param = array($this->session->userdata('userId'), $this->input->post('SEQ'));
        $query = "
				DELETE 
				  FROM SMS_OFTEN
				 WHERE USER_ID = ?
				   AND SEQ = ?
				    
				";

        $this->db->query($query, $param);
        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
        }
        else
        {
            $this->db->trans_commit();
            return true;
        }
    }


    /**
     * sms전송히스토리 조회
     * */
    function selectSmsHistory(){
        $userId = $this->session->userdata('userId');
        $param = array($userId, $userId, $userId, $this->input->post('SEARCH_DATE'));
        $query = "
				SELECT A.USER_ID                              
				     , A.SEQ       
        		     , CASE WHEN A.SMS_TYPE = '30' THEN '직접발송'
		                    ELSE B.SUBJECT
		                END SUBJECT
				     , A.SMS_TYPE                             
				     , A.MESS                                 
				     , A.RESV_PERS                         
				     , A.SEND_PERS                         
				     , A.SEND_TIME          
             		 , (SELECT CONTENT FROM SMS_ERROR_CODE SQ WHERE SQ.CODE = left(A.SEND_RSLT_CODE,4)) SEND_RSLT_CODE
				 FROM SMS_SEND_HISTORY A                     
	         	 LEFT OUTER JOIN (SELECT CONCAT(SUBJECT, '(', LT_SUBJECT, ')' ) SUBJECT
	             			           , SEQ
	                       			FROM SMS_XXX_SEND_TMPL2
	                      		   WHERE USER_ID = ?
	                      		   UNION ALL
				                   SELECT CONCAT(SUBJECT, '(', LT_SUBJECT, ')' ) SUBJECT
				                        , SEQ
				                     FROM SMS_AUTO_SEND_TMPL3
				                    WHERE USER_ID = ?) B
	              ON A.SMS_TYPE = B.SEQ
			   WHERE A.USER_ID = ?                           
				 AND DATE_FORMAT(A.SEND_TIME, '%Y-%m-%d') = ?         
	             #AND A.SMS_TYPE IN ('1', '2', '3', '5', '6', '8', '11', '12', '13', '14', '15', '16')
			   ORDER BY A.SEND_TIME DESC
				";
        return $this->db->query($query, $param)->result_array();
    }

    function selectErrorCode($sendResult){
        $param = array($sendResult);
        $query = "
				SELECT CONTENT
				  FROM SMS_ERROR_CODE
				 WHERE CODE = ?
				";
        $result = $this->db->query($query, $param)->result_array();
        if(count($result) > 0){
            return $result[0]['CONTENT'];
        }else{
            return null;
        }
    }

    /**
     * 예약문자설정 조회
     * */
    function selectSmsManlSendTmplList(){
        $param = array($this->session->userdata('userId'));
        $query = "
				SELECT USER_ID
				     , SEQ
				     , SMS_CONT
				     , SUBJECT
				     , LT_SUBJECT
				     , USE_YN
				     , REG_USER
				     , REG_DATE 
				  FROM SMS_XXX_SEND_TMPL2
				 WHERE USER_ID = ?
				";
        return $this->db->query($query, $param)->result_array();
    }


    /**
     * 예약문자설정 조회
     * */
    function selectSmsAutoSendTmplList(){
        $param = array($this->session->userdata('userId'));
        $query = "
				SELECT USER_ID
					 , SEQ
					 , SMS_CONT
				     , SUBJECT
				     , LT_SUBJECT
					 , USE_YN
					 , SUBSTR(SEND_TIME, 1, 2) TIME
				     , SUBSTR(SEND_TIME, 3, 2) MINUTE
					 , REG_USER
					 , REG_DATE
				  FROM SMS_AUTO_SEND_TMPL3
			     WHERE USER_ID = ?
				";
        return $this->db->query($query, $param)->result_array();
    }

    /**
     * 예약문자설정 수정
     * */
    function updateSmsManlSendTmpl(){
        $this->db->trans_begin();
        $SMS_CONT = explode("||", $this->input->post('smsContArr'));
        $USE_YN = explode("||", $this->input->post('useYnArr'));
        $SEQ = explode("||", $this->input->post('seqArr'));

        $query = "
				UPDATE SMS_XXX_SEND_TMPL2
				   SET SMS_CONT = ?
				     , USE_YN   = ?
				     , REG_DATE = NOW()
				WHERE USER_ID   = ?
				  AND SEQ       = ?
				";

        for($i = 0, $cnt = COUNT($SMS_CONT); $i < $cnt; $i++){

            $param = array($SMS_CONT[$i], $USE_YN[$i], $this->session->userdata('userId'), $SEQ[$i]);
            $this->db->query($query, $param);

        }

        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
        }
        else
        {
            $this->db->trans_commit();
            return true;
        }
    }

    /**
     * 자동발송 예약문자설정 수정
     * */
    function updateSmsAutoManlSendTmpl(){
        $this->db->trans_begin();
        $SMS_CONT = explode(",", $this->input->post('smsContArr'));
        $USE_YN = explode(",", $this->input->post('useYnArr'));
        $SEQ = explode(",", $this->input->post('seqArr'));
        $SEND_TIME = explode(",", $this->input->post('sendTimeArr'));


        $query = "
				UPDATE SMS_AUTO_SEND_TMPL3
				   SET SMS_CONT = ?
				     , SEND_TIME = ?
				     , USE_YN   = ?
				     , REG_DATE = NOW()
				WHERE USER_ID   = ?
				  AND SEQ       = ?
				";

        for($i = 0, $cnt = COUNT($SMS_CONT); $i < $cnt; $i++){

            $param = array($SMS_CONT[$i], $SEND_TIME[$i], $USE_YN[$i], $this->session->userdata('userId'), $SEQ[$i]);
            $this->db->query($query, $param);

        }

        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
        }
        else
        {
            $this->db->trans_commit();
            return true;
        }
    }

    /**
     * 문자 충전신청하기
     * */
    function insertSmsCharge(){
        $this->db->trans_begin();
        $param = array($this->session->userdata('userId'), $this->session->userdata('userId'), $this->input->post('APPL_CNT'), $this->input->post('DEPO_AMT'), $this->input->post('DEPO_NM'), $this->session->userdata('userId'));
        $query = "
				INSERT INTO SMS_CHXX_LIST3
					(USER_ID
				    , SEQ
					, APPL_CNT
					, DEPO_AMT
					, DEPO_NM
					, STAT
					, REG_USER
					, APPL_DATE
					, REG_DATE) 
				VALUES 
					( ?
				    , (SELECT IFNULL(MAX(SEQ), 0)+1 FROM SMS_CHXX_LIST3 A WHERE USER_ID = ?)
					, ?
					, ?
					, ?
					, '1'
					, ?
					, DATE_FORMAT(NOW(), '%Y%m%d')
					, NOW()
				)
								
				";
        $this->db->query($query, $param);

        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
        }
        else
        {
            $this->db->trans_commit();
            return true;
        }
    }

    /**
     * 문자 충전하기 취소
     * */
    function deleteSmsCharge(){
        $this->db->trans_begin();
        $param = array($this->session->userdata('userId'), $this->input->post('SEQ'));
        $query = "
				DELETE
				  FROM SMS_CHXX_LIST3
				 WHERE USER_ID = ?
				   AND SEQ = ?
				";

        $this->db->query($query, $param);

        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
        }
        else
        {
            $this->db->trans_commit();
            return true;
        }
    }

    /**
     * 문자 충전 히스토리 조회
     * */
    function selectSmsChargeList(){
        $param = array($this->session->userdata('userId'));
        $query = "
				SELECT USER_ID
				     , SEQ
				     , DATE_FORMAT(APPL_DATE, '%Y-%m-%d') APPL_DATE
				     , CONCAT(FORMAT(APPL_CNT, 0), '개') APPL_CNT
				     , CONCAT(FORMAT(DEPO_AMT, 0), '원') DEPO_AMT
				     , DEPO_NM
				     , DEPO_BANK_NO
				     , STAT
				     , DEPO_BANK_NM
				     , REG_USER
				     , DATE_FORMAT(REG_DATE, '%Y-%m-%d') REG_DATE
				     , (SELECT BIZ_NUM FROM SUPER_ADMIN_XXXX WHERE USER_ID = 'superAdmin' ) as BIZ_NUM
				  FROM SMS_CHXX_LIST3
				 WHERE USER_ID = ?
				 ORDER BY SEQ DESC
				";
        return $this->db->query($query, $param)->result_array();
    }




    /**
     * 보유문자 조회
     * */
    function selectHaveSmsCnt($userId){
        $param = array($userId);
        $query = "
				SELECT IFNULL(HOLD_SMS_SEND_CNT, 0) HOLD_SMS_SEND_CNT
				  FROM ADMIN_XXXX
				 WHERE USER_ID = ?
				";

        $result = $this->db->query($query, $param)->result_array();
        return $result[0]['HOLD_SMS_SEND_CNT'];
    }

    /**
     * 보유문자 한개 차감
     * */
    function updateHaveSmsCnt($userId){
        $param = array($userId);
        $query = "
				UPDATE ADMIN_XXXX
				   SET HOLD_SMS_SEND_CNT = HOLD_SMS_SEND_CNT - 1
				 WHERE USER_ID = ?
				";
        $result = $this->db->query($query, $param);
    }

    /**
     * 보유문자 다수
     * */
    function updateHaveManySmsCnt($userId, $cnt){
        $param = array($userId, $cnt);
        $query = "
				UPDATE ADMIN_XXXX
				   SET HOLD_SMS_SEND_CNT = HOLD_SMS_SEND_CNT - ?
				 WHERE USER_ID = ?
				";

        $result = $this->db->query($query, $param);
    }

    /**
     * 문자템플릿 조회-수동문자
     * */
    function selectSmsTemplate($userId, $seq){
        $param = array($userId, $seq);
        $query = "
				SELECT SMS_CONT
				  FROM SMS_XXX_SEND_TMPL2
				 WHERE USER_ID = ? 
				   AND SEQ = ?
				   AND USE_YN = 'Y'
				";

        $result = $this->db->query($query, $param)->result_array();
        if(count($result) > 0){
            return $result[0]['SMS_CONT'];
        }else{
            return false;
        }
    }

    /**
     * 문자템플릿 조회-자동문자
     * */
    function selectAutoSmsTemplate($userId, $seq){
        $param = array($userId, $seq);
        $query = "
				SELECT SMS_CONT
				  FROM SMS_AUTO_SEND_TMPL3
				 WHERE USER_ID = ?
				   AND SEQ = ?
				";

        $result = $this->db->query($query, $param)->result_array();
        if(count($result) > 0){
            return $result[0]['SMS_CONT'];
        }else{
            return false;
        }
    }

    function updateAutoCancelList_en(){
        $this->db->trans_begin();

        $query = "
				SELECT B.RESV_NO
				     , A.USER_ID
				     , A.BUSI_NM
                  FROM ADMIN_XXXX A
                 INNER JOIN RESV_XXXX B
                    ON A.USER_ID = B.USER_ID
                 WHERE 
                    ( ( A.AUTO_CACL_USE_YN = 'Y' AND B.RESV_STAT = '00' ) OR B.RESV_STAT = '09' ) 
                   AND NOW() > B.RESV_CNCL_TIME
				   AND B.RESV_SELLER != 'butlerlounge'
				   AND B.RESV_NM LIKE '(en)%'
				";
        $result = $this->db->query($query)->result_array();

        foreach($result as $item){
            $data = array($item['USER_ID'], $item['RESV_NO']);
            $query = "
					UPDATE RESV_XXXX
					   SET RESV_STAT = '02'
						 , MODI_DATE = NOW()
						 , MODI_USER = 'batch'
					 WHERE USER_ID = ?
					   AND RESV_NO = ?
					";

            $this->db->query($query, $data);
            log_message('debug', $item['USER_ID'].' - '.$item['BUSI_NM'].' - '.$item['RESV_NO'].' 예약이 취소되었습니다.');
        }

        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
        }
        else
        {
            $this->db->trans_commit();
            return true;
        }
    }

    function selectAdminHp($userId){
        $query = "
				SELECT RESV_INFO_RECI_HP
				  FROM ADMIN_XXXX 
				 WHERE USER_ID = ?
				";
        $result = $this->db->query($query)->result_array();
        if(count($result) > 0){
            return $result[0]['RESV_INFO_RECI_HP'];
        }else{
            return '';
        }
    }

    function insertSmsCheckHistory( $data ){
        return $this->db->insert("SMS_SEND_CHECK_HISTORY", $data);
    }

    // 자동취소시간
    function autoCanTime($userId){
        $param = array($userId);
        $query = "
				SELECT 
					USER_ID,
					AUTO_CACL_USE_YN,
					DEPO_WAIT_TIME,
					DEPO_WAIT_TODA_YSDY,
					AUTO_CACL_USE_YN,
					AUTO_CACL_USE_TM_YN,
					AUTO_CACL_USE_TM_S,
					AUTO_CACL_USE_TM_E 
				FROM ADMIN_XXXX
					WHERE USER_ID = ?
				";
        return $this->db->query($query, $param)->result_array()[0];

    }

	function getAllUserList($USER_ID)
	{
		$query = "SELECT USER_ID FROM ADMIN_XXXX 
				WHERE STAT_CODE = '01'";
		return $this->db->query($query)->result_array();
	}

	function insert_smsModiCont($VAL)
	{
		$query = "select count(*) as cnt from SMS_XXX_SEND_TMPL2 WHERE SEQ = 7 and USER_ID = '".$VAL['USER_ID']."'";
		$cnt = $this->db->query($query)->result_array()[0]['cnt'];

		if($cnt < 1){
		$query = "INSERT INTO SMS_XXX_SEND_TMPL2
					(USER_ID, SEQ, SUBJECT,LT_SUBJECT,SMS_CONT,USE_YN,REG_USER,REG_DATE)
					VALUES
					('".$VAL['USER_ID']."',".$VAL['SEQ'].", '".$VAL['SUBJECT']."', '".$VAL['LT_SUBJECT']."','".$VAL['SMS_CONT']."','".$VAL['USE_YN']."','".$VAL['REG_USER']."',NOW())";
		$this->db->query($query);
		}
	}
}
