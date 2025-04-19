<?php
class XMONEYModel extends CI_Model {
	function __construct()
	{
		parent::__construct();
	}
	
	function selectEmoneyList(){
		
		$dSql1 = "";
		if($this->input->post('RE_VISIT1') && $this->input->post('RE_VISIT2')){
			$dSql1 .= " AND RE_VISIT BETWEEN '".$this->input->post('RE_VISIT1')."' AND '".$this->input->post('RE_VISIT2')."'";
		}
		
		if($this->input->post('EVENT_SMS_RECI_YN') != '' && $this->input->post('EVENT_SMS_RECI_YN') != '0'){
			$dSql1 .= " AND EVENT_SMS_RECI_YN = '".$this->input->post('EVENT_SMS_RECI_YN')."'";
		}
		
		if($this->input->post('CUMULATIVE_PRCE1') && $this->input->post('CUMULATIVE_PRCE2')){
			$dSql1 .= " AND CUMULATIVE_PRCE BETWEEN '".stripComma($this->input->post('CUMULATIVE_PRCE1'))."' AND '".stripComma($this->input->post('CUMULATIVE_PRCE2'))."'";
		}
		
		if($this->input->post('EMONEY1') && $this->input->post('EMONEY2')){
			$dSql1 .= " AND XMONEY BETWEEN '".stripComma($this->input->post('EMONEY1'))."' AND '".stripComma($this->input->post('EMONEY2'))."'";
		}
		
		if($this->input->post('SEARCH_TEXT')){
			if($this->input->post('SEARCH_TYPE') == '01'){
				$dSql1 .= "AND RESV_NM LIKE '%".$this->input->post('SEARCH_TEXT')."%'";
			}elseif($this->input->post('SEARCH_TYPE') == '02'){
				$dSql1 .= "AND RESV_TEL = '".$this->input->post('SEARCH_TEXT')."'";
			}
		}
		
		
		$dSql2 = "";
		if($this->input->post('BEGIN_DATE') && $this->input->post('END_DATE')){
			$dSql2 = " AND B.CHCK_IN_DATE BETWEEN '".stripDateFormat($this->input->post('BEGIN_DATE'))."' AND '".stripDateFormat($this->input->post('END_DATE'))."'";
		}
		
		$param = array($this->session->userdata('userId'));
		$query = "
				SELECT RESV_TEL
				     , RESV_NM
				     , RE_VISIT
				     , FORMAT(CUMULATIVE_PRCE, 0) CUMULATIVE_PRCE
				     , EVENT_SMS_RECI_YN
				     , FORMAT(XMONEY, 0) XMONEY
				     , LAST_CHECKIN_DATE
				  FROM (SELECT A.RESV_TEL
				             , A.RESV_NM
				             , IFNULL((SELECT COUNT(*) CNT 
				        				            FROM RESV_XXXX SQ 
				        				           WHERE SQ.USER_ID = A.USER_ID
				        				             AND SQ.RESV_TEL = A.RESV_TEL
				                                     AND SQ.RESV_STAT = '01'
				        				           GROUP BY SQ.RESV_TEL 
				        				          HAVING CNT > 1), 0) RE_VISIT
				             , SUM(A.TOT_PRCE) CUMULATIVE_PRCE
				             , IFNULL(A.EVENT_SMS_RECI_YN, 'N') EVENT_SMS_RECI_YN
				             , IFNULL((SELECT XMONEY
				                         FROM XMONEY SQ
				                        WHERE SQ.USER_ID = A.USER_ID
				                          AND SQ.TEL_NO = A.RESV_TEL), 0) XMONEY
				             , DATE_FORMAT(MAX(CHCK_IN_DATE), '%Y-%m-%d') LAST_CHECKIN_DATE
				          FROM RESV_XXXX A
				             , RESV_INFO_ROOM B
				         WHERE A.USER_ID = ?
				           AND A.USER_ID = B.USER_ID
				           AND A.RESV_NO = B.RESV_NO
				           AND RESV_STAT = '01'
				           ".$dSql2."
				         GROUP BY A.RESV_TEL) A
				   WHERE 1 = 1
				".$dSql1;
		return $this->db->query($query, $param)->result_array();
	}
	
	function insertEmoneyBatch(){
	    $this->db->trans_begin();
		
		$userId = $this->session->userdata('userId');
		$TEL_NO_ARR = $this->input->post('TEL_NO_ARR');
		$emoney = $this->input->post('XMONEY');
		$memo = $this->input->post('MEMO');

		foreach($TEL_NO_ARR as $item){
			 $param = array($userId 
						  	, $item
					        , $userId
			 				, $item
							, $emoney
					        , $memo
					   		, $userId
			);
			$query = "
					INSERT INTO XMONEY_HISTORY(
					    USER_ID
					  , TEL_NO
					  , SEQ
					  , XMONEY
					  , MEMO
					  , REG_USER
					  , REG_DATE
					) VALUES (
					    ?
					  , ?
					  , (SELECT IFNULL(MAX(SEQ), 1) + 1 FROM XMONEY_HISTORY A WHERE A.USER_ID = ? AND A.TEL_NO = ?)
					  , ?
					  , ?
					  , ?
					  , NOW()
					)
					";
			$this->db->query($query, $param); 
			
			
			
			$param = array($userId, $item);
			$query = "
				SELECT COUNT(TEL_NO) CNT
				  FROM XMONEY
				 WHERE USER_ID = ?
			       AND TEL_NO = ?
				";
			$result = $this->db->query($query, $param)->result_array();

			if($result[0]['CNT'] <= 0){
				$param = array($userId
						 		, $item
								, stripComma($emoney)
								, $userId
						  		, $userId
				 );
				$query = "
					INSERT INTO XMONEY(
					   USER_ID
					  ,TEL_NO
					  ,XMONEY
					  ,REG_USER
					  ,REG_DATE
					  ,MODI_USER
					  ,MODI_DATE
					) VALUES (
					   ?
					  ,?
					  ,?
					  ,?
					  ,NOW()
					  ,?
					  ,NOW()
					)
					";
			}else{
				$param = array($emoney
						 		, $userId
						  		, $userId
								, $item
				 );
				$query = "
					UPDATE XMONEY
					   SET XMONEY = XMONEY + ?
					     , MODI_USER = ?
					     , MODI_DATE = NOW()
					  WHERE USER_ID = ?
					    AND TEL_NO = ?
					";
			}
			
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
	
	function updateEmoneySmsReciveChange(){
		$this->db->trans_begin();
		
		$userId = $this->session->userdata('userId');
		$TEL_NO_ARR = $this->input->post('telList');
		
		$results = array();
		
		foreach($TEL_NO_ARR as $item){
			$param = array($item);
			$query = "
				UPDATE RESV_XXXX
			       SET EVENT_SMS_RECI_YN = 'Y'
			     WHERE RESV_TEL = ?
				";
			$result = $this->db->query($query, $param);
			array_push($results, $result);
		}
		
		if ($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();
		}
		else
		{
			$this->db->trans_commit();
			return $results;
		}
	}
	
	/**
	 * 적립금설정조회
	 * */
	function selectEmoneyConf(){
		$userId = $this->session->userdata('userId');
		$param = array($userId);
		$query = "
				SELECT ACCU_TYPE
				     , ACCU_AMT
				     , ACCU_AMT_DIV
				     , RCMM_ACCU_AMT
				     , RCMM_ACCU_AMT_DIV
				     , USE_MAX_EMONEY
				     , USE_MAX_EMONEY_DIV
				     , WDAYS
			      FROM ACCU_XXXX
				 WHERE USER_ID = ?
				";
		$result = $this->db->query($query, $param)->result_array();
		$rtn = array();
		if(count($result) > 0){
			$rtn = $result[0];
		}
		return $rtn;
	}
	
	/**
	 * 적립금설정저장
	 * */
	function insertEmoneyConf(){
		$this->db->trans_begin();
		
		$userId = $this->session->userdata('userId');
		$param = array($userId);
		$query = "
				DELETE 
				  FROM ACCU_XXXX 
				 WHERE USER_ID = ? 
				";
		$this->db->query($query, $param);
		
		$param = array(
				$userId
				, $this->input->post('ACCU_TYPE')
				, $this->input->post('ACCU_AMT')
				, $this->input->post('ACCU_AMT_DIV')
				, stripComma($this->input->post('RCMM_ACCU_AMT'))
				, $this->input->post('RCMM_ACCU_AMT_DIV')
				, $this->input->post('USE_MAX_EMONEY')
				, $this->input->post('USE_MAX_EMONEY_DIV')
				, $this->input->post('WDAYS')
		);
		$query = "
				INSERT INTO ACCU_XXXX(
				   USER_ID
				  ,ACCU_TYPE
				  ,ACCU_AMT
				  ,ACCU_AMT_DIV
				  ,RCMM_ACCU_AMT
				  ,RCMM_ACCU_AMT_DIV
				  ,USE_MAX_EMONEY
				  ,USE_MAX_EMONEY_DIV
				  ,WDAYS
				) VALUES (
				   ?
				  , ?
				  , ?
				  , ?
				  , ? 
				  , ?
				  , ?
				  , ?
				  , ?
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
	
	//적립금히스토리
	function selectTelByEmoneyHistory($telno){
		$userId = $this->session->userdata('userId');
		$param = array($userId, $telno);
		$query = "
				SELECT FORMAT(XMONEY, 0) XMONEY
				     , MEMO
				     , DATE_FORMAT(REG_DATE, '%Y-%m-%d') REG_DATE 
				  FROM XMONEY_HISTORY
				 WHERE USER_ID = ?
				   AND TEL_NO = ?
				";
		return $this->db->query($query, $param)->result_array();
	}
	
	function selectTelByCustormerInfo($telno){
		$userId = $this->session->userdata('userId');
		
		$param = array($userId, $telno);
		$query = "
				SELECT RESV_NM
				     , RESV_TEL
				  FROM RESV_XXXX
				 WHERE USER_ID = ?
				   AND RESV_TEL = ?
				 GROUP BY RESV_TEL 
				";

		$result = $this->db->query($query, $param)->result_array();
		return $result[0];  
	}
	//적립금
	function selectTelByEmoney($telno){
		$userId = $this->session->userdata('userId');
		
		$param = array($userId, $telno);
		$query = "
				SELECT FORMAT(XMONEY, 0) XMONEY
				  FROM XMONEY
				 WHERE USER_ID = ?
				   AND TEL_NO = ?
				";
		$result = $this->db->query($query, $param)->result_array();
		$emoney = 0;
		if(count($result) > 0){
			$emoney = $result[0]['XMONEY'];
		}
		
		return $emoney;
	}
	
	//개인적립금적립
	function insertTelByEmoney(){
		$userId = $this->session->userdata('userId');
		
		$param = array();
		$query = "
		
				";
	}
	
	function selectTelByRecommandInfo($telno){
		$userId = $this->session->userdata('userId');
		
		$param = array($userId, $telno);
		$query = "
				SELECT RESV_NM
				     , RESV_TEL
				     , (SELECT DATE_FORMAT(MIN(CHCK_IN_DATE), '%Y-%m-%d') 
				          FROM RESV_INFO_ROOM SQ 
				         WHERE SQ.USER_ID = A.USER_ID 
				           AND SQ.RESV_NO = A.RESV_NO) CHECKIN_DATE
				  FROM RESV_XXXX A
				 WHERE USER_ID = ?
				   AND RCMM_TEL = ?
				   AND RESV_STAT = '01'
				";
		return $this->db->query($query, $param)->result_array();
	}
}
	
