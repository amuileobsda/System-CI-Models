<?php
class OptionMngtModel extends CI_Model {
	
	/**
	 * 옵션목록
	 * */
	function selectOptionList(){
		$param = array($this->session->userdata('userId'));
		$query = "
				SELECT OPTN_NM
				     , SORT_NO
				     , OPTN_CODE
				     , UNIT_NM
				     , FORMAT(WEEK_PRCE, 0) WEEK_PRCE
				     , USE_YN
				     , USE_DAILY_PRICE_YN
				     , VIEW_YN
                     , OPTN_IMG
				  FROM XROOM_XOP
				 WHERE USER_ID = ?
				 ORDER BY SORT_NO
				";
		return $this->db->query($query, $param)->result_array();
	}
	
	function selectOption($OPTN_CODE){
		$param = array($this->session->userdata('userId'), $OPTN_CODE);
		$query = "
				SELECT OPTN_NM
					 #, SPECIAL_CHAR_UNESCAPE(OPTN_DETL_COMT) OPTN_DETL_COMT
					 , OPTN_DETL_COMT
					 , FORMAT(WEEK_PRCE, 0) WEEK_PRCE
					 , FORMAT(FRD_PRCE, 0) FRD_PRCE
					 , FORMAT(SAT_PRCE, 0) SAT_PRCE
					 , FORMAT(SUN_PRCE, 0) SUN_PRCE
					 , BASE_QTY
					 , MAX_QTY
					 , UNIT_NM
				     , OPTN_CODE
				     , USE_YN
				     , USE_DAILY_PRICE_YN
                     , OPTN_IMG
				  FROM XROOM_XOP
				 WHERE USER_ID = ?
				   AND OPTN_CODE = ?
				";
		return $this->db->query($query, $param)->result_array();
	}
	
	/**
	 * 옵션저장
	 * */
	function insertOption($fileName){
		
		$this->db->trans_begin();
		
		$param = array(
				$this->input->post('OPTN_NM')
				, nl2br($this->input->post('OPTN_DETL_COMT'))
				, $this->input->post('WEEK_PRCE')
				, $this->input->post('FRD_PRCE')
				, $this->input->post('SAT_PRCE')
				, $this->input->post('SUN_PRCE')
				, $this->input->post('BASE_QTY')
				, $this->input->post('MAX_QTY')
				, $this->input->post('UNIT_NM')
				, $this->session->userdata('userId')
				, $this->session->userdata('userId')
				, $this->session->userdata('userId')
				, $this->session->userdata('userId')
				, $this->session->userdata('userId')
				,$this->input->post('USE_DAILY_PRICE_YN')
				,$fileName
		);
		
		$query = "
				INSERT INTO XROOM_XOP
				   SET OPTN_NM		   = ?
					 , OPTN_DETL_COMT  = ?
					 , WEEK_PRCE       = ?
					 , FRD_PRCE        = ?
					 , SAT_PRCE        = ?
					 , SUN_PRCE        = ?
					 , BASE_QTY        = ?
					 , MAX_QTY         = ?
					 , UNIT_NM         = ?
					 , SORT_NO         = (SELECT IFNULL(MAX(SORT_NO), 0) + 1 FROM XROOM_XOP A WHERE USER_ID = ?)
					 , USER_ID         = ?
					 , REG_USER        = ?
					 , MODI_USER       = ?
					 , OPTN_CODE       = (SELECT IFNULL(MAX(OPTN_CODE), 0)+1 FROM XROOM_XOP A WHERE USER_ID = ?) 
					 , REG_DATE        = NOW()
					 , MODI_DATE       = NOW()
				     , USE_YN		   = 'Y'
				     , USE_DAILY_PRICE_YN = ?
                     , OPTN_IMG = ?
				";
		$this->db->query($query, $param);



        
		
		//옵션추가시 객실옵션사용에 추가해준다.
		$param = array($this->session->userdata('userId'));
		$query = "
				INSERT INTO ROOM_OPTN_DTL
				(
				  USER_ID
				  , OPTN_CODE
				  , ROOM_CODE
				  , REG_USER
				  , REG_DATE
				  , MODI_USER
				  , MODI_DATE
				)
				SELECT A.USER_ID
				     , (SELECT MAX(OPTN_CODE) 
				          FROM XROOM_XOP SQ 
				         WHERE SQ.USER_ID = A.USER_ID)
				     , A.ROOM_CODE
				     , A.USER_ID
				     , NOW()
				     , A.USER_ID
				     , NOW()
				  FROM ROOM_XXX A
				 WHERE A.USER_ID = ?
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
	 * 옵션수정
	 * */
	function updateOption($updateValues,$fileName){		
		$this->db->trans_begin();
		$param = $updateValues;
        if($fileName){
            $query = "
                    UPDATE XROOM_XOP
                       SET OPTN_NM         = ?
                         , OPTN_DETL_COMT  = ?
                         , WEEK_PRCE       = ?
                         , FRD_PRCE        = ?
                         , SAT_PRCE        = ?
                         , SUN_PRCE        = ?
                         , BASE_QTY        = ?
                         , MAX_QTY         = ?
                         , UNIT_NM         = ?
                         , MODI_USER       = ?
                         , USE_DAILY_PRICE_YN = ?
                         , MODI_DATE       = NOW()
                         , OPTN_IMG = ?
                     WHERE USER_ID   = ?
                      AND OPTN_CODE = ? 								
                    ";
            }
        else {
            $query = "
				UPDATE XROOM_XOP
				   SET OPTN_NM         = ?
					 , OPTN_DETL_COMT  = ?
					 , WEEK_PRCE       = ?
					 , FRD_PRCE        = ?
					 , SAT_PRCE        = ?
					 , SUN_PRCE        = ?
					 , BASE_QTY        = ?
					 , MAX_QTY         = ?
					 , UNIT_NM         = ?
					 , MODI_USER       = ?
				     , USE_DAILY_PRICE_YN = ?
					 , MODI_DATE       = NOW()
				 WHERE USER_ID   = ?
				  AND OPTN_CODE = ? 								
				";
        }
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
	 * 옵션수정 사용유무
	 * */
	function updateOptionUseYn($updateValues){
		$this->db->trans_begin();
		$param = $updateValues;
		$query = "
				UPDATE XROOM_XOP
				   SET USE_YN 		   = ?
					 , MODI_USER       = ?
					 , MODI_DATE       = NOW()
				 WHERE USER_ID   = ?
				  AND OPTN_CODE = ?
	
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
     * 옵션수정 공개유무
     * */
    function updateOptionViewYn($updateValues){
        $this->db->trans_begin();
        $param = $updateValues;
        $query = "
				UPDATE XROOM_XOP
				   SET VIEW_YN 		   = ?
					 , MODI_USER       = ?
					 , MODI_DATE       = NOW()
				 WHERE USER_ID   = ?
				  AND OPTN_CODE = ?
	
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
	 * 옵션삭제
	 * */
	function deleteOption($OPTN_CODE){
		$this->db->trans_begin();
		$param = array($this->session->userdata('userId'), $OPTN_CODE);
		$query = "
				DELETE 
				  FROM XROOM_XOP
				 WHERE USER_ID = ?
				   AND OPTN_CODE = ?
				";
		$this->db->query($query, $param);
		
		$param = array($this->session->userdata('userId'), $OPTN_CODE);
		$query = "
				DELETE
				  FROM ROOM_OPTN_DTL
				 WHERE USER_ID = ?
				   AND OPTN_CODE = ?
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
	
	function updateOptionSort($updateValues){
		$this->db->trans_begin();
		$param = $updateValues;
		$query = "
				UPDATE XROOM_XOP
				   SET SORT_NO 		   = ?
					 , MODI_USER       = ?
					 , MODI_DATE       = NOW()
				 WHERE USER_ID   = ?
				  AND OPTN_CODE = ?
		
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



    function groupUpdateOptionSort($updateValues){
		$this->db->trans_begin();
		$param = $updateValues;
		$query = "
				UPDATE GROUP_XOP
				   SET group_sort 	   = ?
				 WHERE USER_ID   = ?
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
	 * 기타선택사항조회
	 * */
	function selectEtcCheckList(){
		
		$param = array($this->session->userdata('userId'));
		$query = "
				SELECT USER_ID
				     , SEQ
				     , SERV_NM
				     , SERV_DETAIL
				     , SERV_OPTION
				     , SORT_NO
				     , REQU_YN
				     , USE_YN
				  FROM ETC_SEL_LIST
				 WHERE USER_ID = ?
				 ORDER BY SORT_NO
				";
		
		return $this->db->query($query, $param)->result_array();
	}
	
	/**
	 * 기타선택사항단건조회
	 * */
	function selectEtcCheckInfo(){
	
		$param = array($this->session->userdata('userId'), $this->input->post('SEQ'));
		$query = "
				SELECT USER_ID
				     , SEQ
				     , SERV_NM
				     , REPLACE(REPLACE(SERV_DETAIL, '\\n', ''), '\\r', '') SERV_DETAIL
				     , SERV_OPTION
				     , SORT_NO
				     , REG_USER
				     , REG_DATE 
				  FROM ETC_SEL_LIST
				 WHERE USER_ID = ?
				   AND SEQ = ?
				";
	
		return $this->db->query($query, $param)->result_array();
	}
	
	/**
	 * 기타선택사항저장
	 * */
	function insertEtcCheck(){
		$this->db->trans_begin();
	
		$param = array($this->session->userdata('userId')    
					    , $this->session->userdata('userId')        
						, $this->input->post('SERV_NM')    
						, nl2br($this->input->post('SERV_DETAIL'))
						, $this->input->post('SERV_OPTION')
						, $this->session->userdata('userId')    
						, $this->session->userdata('userId')   
				);
		$query = "
				INSERT INTO ETC_SEL_LIST 
				   SET USER_ID     = ?
				     , SEQ         = (SELECT IFNULL(MAX(SEQ), 0)+1 FROM ETC_SEL_LIST A WHERE USER_ID = ?)
				     , SERV_NM     = ?
				     , SERV_DETAIL = ?
				     , SERV_OPTION = ?
				     , SORT_NO     = (SELECT IFNULL(MAX(SORT_NO), 0)+1 FROM ETC_SEL_LIST A WHERE USER_ID = ?)
				     , REG_USER    = ?
				     , REG_DATE    = NOW()
				     , REQU_YN     = 'N'
				     , USE_YN      = 'N' 
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
	 * 기타선택사항수정
	 * */
	function updateEtcCheck(){
		$this->db->trans_begin();
	
		$param = array($this->input->post('SERV_NM')     
						, nl2br($this->input->post('SERV_DETAIL'))
						, $this->input->post('SERV_OPTION')
						, $this->session->userdata('userId')    
						, $this->session->userdata('userId')
						, $this->input->post('SEQ')      
		);
		$query = "
				UPDATE ETC_SEL_LIST 
				   SET SERV_NM     = ?
				     , SERV_DETAIL = ?
				     , SERV_OPTION = ?
				     , REG_USER    = ?
				     , REG_DATE    = NOW() 
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
	 * 기타선택사항수정
	 * */
	function deleteEtcCheck(){
		$this->db->trans_begin();
	
		$param = array($this->session->userdata('userId'), $this->input->post('SEQ'));
		$query = "
				DELETE
				  FROM ETC_SEL_LIST
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
	 * 기타선택사항 필수여부수정
	 * */
	function updateRequYnCheck(){
		$this->db->trans_begin();
	
		$param = array($this->input->post('REQU_YN')
				       , $this->session->userdata('userId')
				       , $this->input->post('SEQ')
		);
		$query = "
				UPDATE ETC_SEL_LIST
				   SET REQU_YN     = ?
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
	 * 기타선택사항 사용여부수정
	 * */
	function updateUseYnCheck(){
		$this->db->trans_begin();
	
		$param = array($this->input->post('USE_YN')
				       , $this->session->userdata('userId')
				       , $this->input->post('SEQ')
		);
		$query = "
				UPDATE ETC_SEL_LIST
				   SET USE_YN     = ?
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
	 * 기타선택사항 순서저장
	 * */
	function updateEtcSort(){
		$this->db->trans_begin();
		//echo $this->input->post("sortArr");
		$SORT = $this->input->post("sortArr");
		$SEQ = $this->input->post("seqArr");
		
		$query = "
					UPDATE ETC_SEL_LIST
					   SET SORT_NO = ?
					 WHERE SEQ = ?
				";
		for($i = 0, $cnt = count($SEQ); $i < $cnt; $i++){
			$values = array($SORT[$i], $SEQ[$i]);
			$this->db->query($query, $values);
		}
		if ($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();
		}
		else
		{
			$this->db->trans_commit();
		//	$this->commhistory->hisType2("객실순서 정보 수정");
			return true;
		}
	}


    /**
     * 그룹 옵션목록
     * */
    function selectGorupOptionList($seq){
        $param = array($this->session->userdata('userId'));
        $query = "
				SELECT OPTN_NM
				     , SORT_NO
				     , OPTN_CODE
				     , UNIT_NM
				     , FORMAT(WEEK_PRCE, 0) WEEK_PRCE
				     , USE_YN
				     , USE_DAILY_PRICE_YN
				     , VIEW_YN
				  FROM XROOM_XOP
				 WHERE USER_ID = ?";
/*
        if($seq == "" && isset($seq)) {
            $query .= "
				 AND (GROUP_SEQ IS NULL 
				      OR  GROUP_SEQ = '') 
				 ORDER BY SORT_NO
				";
        }else{
            array_push($param,$seq);
            $query .= "
				 AND (GROUP_SEQ IS NULL 
				      OR  GROUP_SEQ = ?) 
				 ORDER BY SORT_NO
				";

        }*/
        return $this->db->query($query, $param)->result_array();
    }

    /**
     * 그룹 추가
     * */
    function insertGroupOption($user_id, $group_nm, $option_code){
        $this->db->trans_begin();

        $param = array($user_id, $group_nm);
        $query = "
				INSERT INTO GROUP_XOP(USER_ID,GROUP_NM,REG_DATE)
				VALUES(?, ?, NOW())
				";
        $this->db->query($query, $param);
        


        $param = array($user_id);
        $query = "
				SELECT MAX(SEQ) AS SEQ
				FROM GROUP_XOP
				WHERE USER_ID = ?
				";
        $group_seq = $this->db->query($query, $param)->result_array();



        $param = array($user_id);
        $query = "
        SELECT `OPTN_CODE` FROM  XROOM_XOP
                WHERE USER_ID = ?
                ";
        $group_list = $this->db->query($query, $param)->result_array();
        $cnt		=	count($group_list);
            $param = array($user_id,$group_nm);
            $query = 'SELECT `SEQ` FROM  GROUP_XOP WHERE USER_ID = ? AND GROUP_NM = ?';
            $group_optn = $this->db->query($query, $param)->result_array();
            for($i=0;$i<$cnt;$i++){
               
                $param = array($group_list[$i]['OPTN_CODE'],$group_optn[0]['SEQ'],$user_id,$group_nm);
                for($j=0;$j<count($option_code);$j++){ 
                   
                    if($option_code[$j] == $group_list[$i]['OPTN_CODE']){
                        $query = 'insert into GROUP_LIST (OPTN_CODE,GROUP_SEQ,USE_YN,USER_ID,GROUP_NM) values(?,?,"Y",?,?)';
                        break;
                    }else{
                        $query = 'insert into GROUP_LIST (OPTN_CODE,GROUP_SEQ,USE_YN,USER_ID,GROUP_NM) values(?,?,"N",?,?)';
                    }
                }
              
              
                $this->db->query($query, $param);
            }


        $this->groupOptionProcess($user_id,$option_code,$group_seq[0]['SEQ'],"U_I");

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
     * 그룹 업데이트
     * */
    function updateGroupOption($user_id,$seq,$group_nm, $option_code){
        $this->db->trans_begin();

        $param = array($group_nm,$user_id,$seq);
        $query = "
				UPDATE GROUP_XOP
				SET GROUP_NM = ?
				WHERE USER_ID = ?
				AND SEQ = ?
				";
        $this->db->query($query, $param);

        $param = array($user_id,$group_nm);
        $query = "select * from GROUP_LIST where USER_ID = ? AND GROUP_NM = ?";
        $inser_list = $this->db->query($query, $param)->result_array();
        if(count($inser_list) == 0){

            $param = array($user_id);
        $query = "
        SELECT `OPTN_CODE` FROM  XROOM_XOP
                WHERE USER_ID = ?
                ";
        $group_list = $this->db->query($query, $param)->result_array();
        $cnt		=	count($group_list);
            $param = array($user_id,$group_nm);
            $query = 'SELECT `SEQ` FROM  GROUP_XOP WHERE USER_ID = ? AND GROUP_NM = ?';
            $group_optn = $this->db->query($query, $param)->result_array();
            for($i=0;$i<$cnt;$i++){
               
                $param = array($group_list[$i]['OPTN_CODE'],$group_optn[0]['SEQ'],$user_id,$group_nm);
                for($j=0;$j<count($option_code);$j++){ 
                   
                    if($option_code[$j] == $group_list[$i]['OPTN_CODE']){
                        $query = 'insert into GROUP_LIST (OPTN_CODE,GROUP_SEQ,USE_YN,USER_ID,GROUP_NM) values(?,?,"Y",?,?)';
                        break;
                    }else{
                        $query = 'insert into GROUP_LIST (OPTN_CODE,GROUP_SEQ,USE_YN,USER_ID,GROUP_NM) values(?,?,"N",?,?)';
                    }
                }
              
              
                $this->db->query($query, $param);
            }

        }



        $param = array($user_id);
        $query = "
        SELECT `OPTN_CODE` FROM  XROOM_XOP
                WHERE USER_ID = ?
                ";
        $group_list = $this->db->query($query, $param)->result_array();
        $cnt		=	count($group_list);

            $param = array($user_id,$group_nm);
            $query = 'SELECT `SEQ` FROM  GROUP_XOP WHERE USER_ID = ? AND GROUP_NM = ?';
            $group_optn = $this->db->query($query, $param)->result_array();
            for($i=0;$i<$cnt;$i++){
               
                $param = array($user_id,$group_optn[0]['SEQ'],$group_list[$i]['OPTN_CODE']);
                for($j=0;$j<count($option_code);$j++){ 
                   
                    if($option_code[$j] == $group_list[$i]['OPTN_CODE']){
                        $query = 'update GROUP_LIST set USE_YN = "Y" WHERE USER_ID = ? AND GROUP_SEQ = ? AND OPTN_CODE = ?';
                        break;
                    }else{
                        $query = 'update GROUP_LIST set USE_YN = "N" WHERE USER_ID = ? AND GROUP_SEQ = ? AND OPTN_CODE = ?';
                    }
                }
              
              
                $this->db->query($query, $param);
            }



        $this->groupOptionProcess($user_id, "", $seq,"D");
        $this->groupOptionProcess($user_id, $option_code, $seq,"U_I");

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
     * 그룹 삭제
     * */
    function deleteGroupOption($user_id,$seq){
        $this->db->trans_begin();

        $param = array($user_id,$seq);
        $query = "
				DELETE FROM GROUP_XOP
			    WHERE USER_ID = ?
			    AND SEQ = ?;
				";
        $this->db->query($query, $param);


        $param = array($user_id,$seq);
        $query = "
				DELETE FROM GROUP_LIST
			    WHERE USER_ID = ?
			    AND GROUP_SEQ = ?;
				";
        $this->db->query($query, $param);



        $this->groupOptionProcess($user_id,"",$seq,"D");

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
     * 옵션 그룹 추가
     * */
    function groupOptionProcess($user_id,$option_code,$group_seq,$mode){

        if($mode == "U_I") {
            for ($i = 0; $i < count($option_code); $i++) {
                $param = array($group_seq, $this->session->userdata('userId'), $option_code[$i]);
                $query = "
				        UPDATE XROOM_XOP
                        SET GROUP_SEQ = ?
                        WHERE USER_ID = ?
                        AND OPTN_CODE = ?
                        ";
                $this->db->query($query, $param);
            }
        }else if($mode = "D"){
            $param = array($this->session->userdata('userId'),$group_seq);
            $query = "
                        UPDATE XROOM_XOP
                        SET GROUP_SEQ = NULL
                        WHERE USER_ID = ?
                        AND GROUP_SEQ = ?
                        ";
            $this->db->query($query, $param);
        }

    }



    /**
     * 그룹 옵션목록
     * */
    function selectGroupOptionList(){
        $param = array($this->session->userdata('userId'));
        $query = "
				SELECT *
				  FROM GROUP_XOP
				 WHERE USER_ID = ?
				 ORDER BY group_sort
				";
        return $this->db->query($query, $param)->result_array();
    }


    /**
     * 그룹 옵션정보
     * */
    function selectGroupOptionInfo($seq){
        $param = array($this->session->userdata('userId'),$seq);

                $query = "

                SELECT OPTN_CODE,GROUP_NM
				  FROM GROUP_LIST
				 WHERE USER_ID = ?
                 AND GROUP_SEQ =?
                 AND USE_YN = 'Y'
				";
        return $this->db->query($query, $param)->result_array();
    }

}
