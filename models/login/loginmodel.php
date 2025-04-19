<?php
class LoginModel extends CI_Model {
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    /**
     * 펜션관리자 로그인-슈퍼관리자용
     * */
    function selectAdminInfoAsSuperAdmin($userId){
        $param = array($userId);
        $query = "
				SELECT A.USER_ID
				     , A.USER_NM
				     , A.USER_HP
				     , A.STAT_CODE
					 , CASE WHEN NOW() BETWEEN A.SVC_BEGIN_DATE AND A.SVC_END_DATE THEN 'Y'
     						ELSE 'N'
 					 	END IS_SERVICE_PERIOD
				     , IFNULL((SELECT USE_YN FROM ADMIN_SERVICE SQ WHERE SQ.USER_ID = A.USER_ID), 'N') ADD_SERVICE_USE_YN
		 	      FROM ADMIN_XXXX A
				 WHERE A.USER_ID = ?
				";
        return $this->db->query($query, $param)->result_array();
    }

    /**
     * 펜션관리자 로그인
     * */
    function selectAdminInfo($userId, $passwd){
        $param = array($userId, $passwd);
        $query = "
				SELECT A.USER_ID
				     , A.USER_NM
				     , A.USER_HP
				     , A.STAT_CODE
					 , CASE WHEN NOW() BETWEEN A.SVC_BEGIN_DATE AND A.SVC_END_DATE THEN 'Y'
     						ELSE 'N'
 					 	END IS_SERVICE_PERIOD
				     , IFNULL((SELECT USE_YN FROM ADMIN_SERVICE SQ WHERE SQ.USER_ID = A.USER_ID), 'N') ADD_SERVICE_USE_YN
		 	      FROM ADMIN_XXXX A
				 WHERE A.USER_ID = ?
				   AND A.USER_PW = ?
				";
        return $this->db->query($query, $param)->result_array();
    }

    /**
     * 부산통합로그인
     */
    function selectAdminInfoBusan($userId){
        $param = array($userId);
        $query = "
				SELECT A.USER_ID
				     , A.USER_NM
				     , A.USER_HP
					 , CASE WHEN NOW() BETWEEN A.SVC_BEGIN_DATE AND A.SVC_END_DATE THEN 'Y'
     						ELSE 'N'
 					 	END IS_SERVICE_PERIOD
				     , IFNULL((SELECT USE_YN FROM ADMIN_SERVICE SQ WHERE SQ.USER_ID = A.USER_ID), 'N') ADD_SERVICE_USE_YN
		 	      FROM ADMIN_XXXX A
				 WHERE A.USER_ID = ?
				";
        return $this->db->query($query, $param)->result_array();
    }

    /**
     * 슈퍼관리자 로그인
     * */
    function selectSuperAdminInfo($userId, $passwd){
        $param = array($userId, $passwd);
        $query = "
				SELECT USER_ID
				     , USER_NM
		 	      FROM SUPER_ADMIN_XXXX
				 WHERE USER_ID = ?
				   AND USER_PW = ?
				";
        return $this->db->query($query, $param)->result_array();
    }
    
    
    function test($result){
	    $param = array(
				$result
				
		);
		$query = "
			INSERT INTO TEST
			   SET 	test = ?      
				 

				";
		$this->db->query($query, $param);
		
	    
    }
}
