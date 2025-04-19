<?php
class PensioninfoModel extends CI_Model {
	function __construct()
	{
		// Call the Model constructor
		parent::__construct();
	}
	
	function selectRoomInfo($userId){
		$param = array($userId);
		$query = "
				SELECT TYPE_NM
				     , ROOM_EXTN
				     , ADLT_BASE_PERS
				     , ADLT_MAX_PERS
				     , INTERIOR
				     , ETC_DETL
				  FROM ROOM_XXX
				 WHERE USER_ID = ?
				";
		return $this->db->query($query, $param)->result_array();
	}
	
	function selectPriceInfo($userId){
		$param = array($userId);
		$query = "
				SELECT B.TYPE_NM
				     , A.WEEK_PRCE
				     , A.FRD_PRCE
				     , A.SAT_PRCE
				     , A.SUN_PRCE
				     , B.ROOM_EXTN
				     , B.ADLT_BASE_PERS
				     , B.ADLT_MAX_PERS
				     , CASE WHEN A.PROD_CODE = 'P01' THEN '기본'
				            WHEN A.PROD_CODE = 'P02' THEN '동절기'
				            WHEN A.PROD_CODE = 'P03' THEN '비수기'
				            WHEN A.PROD_CODE = 'P04' THEN '준성수기'
				            WHEN A.PROD_CODE = 'P05' THEN '성수기'
				            WHEN A.PROD_CODE = 'P06' THEN '극성수기'
				        END PROD_NM
				     , A.PROD_CODE
				     , B.ROOM_CODE
				     , A.USE_YN
				  FROM XROOM_PRICE_INFO A
				 INNER JOIN ROOM_XXX B
				    ON A.USER_ID = B.USER_ID
				   AND A.ROOM_CODE = B.ROOM_CODE
				 WHERE A.USER_ID = ?
				 ORDER BY B.SORT_NO, A.ROOM_CODE, A.PROD_CODE
				";
		return $this->db->query($query, $param)->result_array();
	}
	
	function selectPensionInfo($userId){
		$param = array($userId);
		$query = "
				SELECT A.USER_ADDR ADDR
				     , A.BUSI_NM
				     , A.COMM_SALE_NO
				     , A.USER_NM
				  FROM ADMIN_XXXX A
				 WHERE USER_ID = ?
				";
		$result = $this->db->query($query, $param)->result_array();
		return $result[0];
	}
	
}
