<?php
class PeriodMngtModel extends CI_Model{
	
	/**
	 * 기본기간목록
	 * */
	function selectPeriodList($VALUES){
		$param = $VALUES;
		$query = "
				SELECT PROD_CODE
				     , PROD_CODE PROD_CD
				     , PROD_NM
				     , DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d') BEGIN_DATE
				     , DATE_FORMAT(END_DATE, '%Y-%m-%d') END_DATE
					 , SEQ
                     , USE_YN
				  FROM ROOM_PXX_XXX
				 WHERE USER_ID = ?
				   AND PROD_CODE != 'P01'
				 ORDER BY SEQ DESC
				";
		return $this->db->query($query, $param)->result_array();
	}
	
	/**
	 * 기본기간수정시 호출
	 * */
	function selectPeriod($VALUES){
		$param = $VALUES;
		$query = "
				SELECT PROD_CODE
				     , PROD_NM
				     , DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d') BEGIN_DATE
				     , DATE_FORMAT(END_DATE, '%Y-%m-%d') END_DATE
				     , SEQ
                     , USE_YN
				  FROM ROOM_PXX_XXX
				 WHERE USER_ID = ?
				   AND SEQ = ?
				 ORDER BY SEQ DESC
				";
		return $this->db->query($query, $param)->result_array();
	}
	
	/**
	 *기본기간 등록
	 * */
	function insertPeriod($VALUES){
		
		$this->db->trans_begin();
		
		$param = $VALUES;
		$query = "
				INSERT INTO ROOM_PXX_XXX
				(
				  PROD_NM
				, BEGIN_DATE
				, END_DATE
				, REG_USER
				, MODI_USER
				, USER_ID
				, PROD_CODE
				, SEQ
				, REG_DATE
				, MODI_DATE
                ,  USE_YN
				) 
				VALUES 
				(
				  ? 
				, ? 
				, ? 
				, ? 
				, ? 
				, ? 
				, ?
				, (SELECT IFNULL(MAX(SEQ), 0) + 1 FROM ROOM_PXX_XXX A WHERE USER_ID = ?)
				, NOW()
				, NOW()
                ,?
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
	 *기본기간 수정
	 * */
	function updatePeriod($VALUES){
		
		$this->db->trans_begin();
		
		$param = $VALUES;
		$query = "
				UPDATE ROOM_PXX_XXX
				   SET PROD_NM    = ? 
				     , BEGIN_DATE = ? 
				     , END_DATE   = ?
				     , PROD_CODE = ? 
				     , MODI_USER  = ?
                     , USE_YN   = ?
				WHERE USER_ID	= ?
				  AND SEQ       = ?
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
	 *기본기간 삭제
	 * */
	function deletePeriod($VALUES){
		
		$this->db->trans_begin();
		
		$param = $VALUES;
		$query = "
				DELETE 
				  FROM ROOM_PXX_XXX   
				 WHERE USER_ID		= ?
				   AND PROD_CODE = ?
				   AND SEQ       = ?
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
	 * 새로운 이벤트기간코드
	 * */
	function selectNewEventSeq($values){
		$query = "
				SELECT COUNT(SEQ)+1 SEQ
    			  FROM EVENT_PROD
				";
		$result = $this->db->query($query, $values)->result_array();
		if(count($result) > 0){
			return $result[0]; 
		}else{
			return null;
		}
	}
	
	/**
	 * 이벤트기간목록
	 * */
	function selectEventPeriodList($values){
		$query = "
				SELECT SEQ
				     , EVENT_NM
				     , FORMAT(EVENT_PRCE, 0) EVENT_PRCE
				     , EVENT_OPTN
				     , DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d') BEGIN_DATE
				     , DATE_FORMAT(END_DATE, '%Y-%m-%d') END_DATE
				     , DETL_DAY_OPTN
				     , EVENT_NM_DISP_YN
				     , REG_USER
				     , REG_DATE
				  FROM EVENT_PROD
				 WHERE USER_ID = ?
				";
		return $this->db->query($query, $values)->result_array();
	}
	
	/**
	 * 수정시 호출할 이벤트기간정보
	 * */
	function selectEventPeriodInfo($values){
		$query = "
				SELECT SEQ
				     , EVENT_NM
				     , FORMAT(EVENT_PRCE, 0) EVENT_PRCE
				     , EVENT_OPTN
				     , DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d') BEGIN_DATE
				     , DATE_FORMAT(END_DATE, '%Y-%m-%d') END_DATE
				     , DETL_DAY_OPTN
				     , EVENT_NM_DISP_YN
				     , WON_UNIT_USE_YN
				     , REG_USER
                     , RED_FONT_YN
				  FROM EVENT_PROD
				 WHERE USER_ID = ?
				   AND SEQ = ?
				";
		$result = $this->db->query($query, $values)->result_array();
		if(count($result) > 0){
			return $result[0];
		}else{
			return null;
		}
	}
	
	/**
	 * 이벤트기간 저장
	 * */
	function insertEventPeriodInfo(){
		$query = "
				INSERT INTO EVENT_PROD 
				   SET USER_ID 			= ?
				     , SEQ 				= ?
				     , EVENT_NM 		= ?
				     , EVENT_OPTN 		= ?
				     , BEGIN_DATE 		= ?
				     , END_DATE 		= ?
				     , EVENT_PRCE 		= ?
				     , WON_UNIT_USE_YN 	= ?
				     , EVENT_NM_DISP_YN = ?
				     , DETL_DAY_OPTN 	= ?
				     , REG_USER 		= ?
				     , MODI_USER 		= ?
				     , REG_DATE 		= NOW()     
				     , MODI_DATE 		= NOW()
                     , RED_FONT_YN = ?
				";
		return $query;
	}

    /**
     * 특별기간 저장
     * */
    function insertSpecialPeriodInfo(){


        $query = "
				INSERT INTO XX_DATE_LIST 
				   SET USER_ID 			    = ?
				     , CHCK_STAR_DATE 		= ?
				     , CHCK_END_DATE 		= ?
				     , SP_DATE_NM           = ?
				     , ROOM_PRCE 	        = ?
				     , REG_DATE 		    = NOW()
				     , SP_DATE_NM_DISP_YN   = ?
				";

        return $query;
    }
	
	/**
	 * 이벤트기간 수정
	 * */
	function updateEventPeriodInfo(){
		$query = "UPDATE EVENT_PROD
				     SET EVENT_NM 			= ?
					   , EVENT_OPTN 		= ?
					   , BEGIN_DATE 		= ?
					   , END_DATE 			= ?
					   , EVENT_PRCE 		= ?
					   , WON_UNIT_USE_YN 	= ?
					   , EVENT_NM_DISP_YN   = ?
					   , DETL_DAY_OPTN 	    = ?
					   , MODI_USER 			= ?
					   , MODI_DATE 			= NOW()
                       , RED_FONT_YN = ?
				 WHERE USER_ID = ?
				   AND SEQ = ? 
				";
		return $query;
	}

    /**
     * 특별기간 수정
     * */
    function updateSpecialPeriodInfo(){

        $query = "UPDATE XX_DATE_LIST
				     SET CHCK_STAR_DATE 	= ?
				     , CHCK_END_DATE 		= ?
				     , SP_DATE_NM           = ?
				     , ROOM_PRCE 	        = ?
				     , REG_DATE 		    = NOW()  
				     , SP_DATE_NM_DISP_YN   = ?
				 WHERE USER_ID = ?
				   AND SEQ = ? 
				";
        return $query;
    }
	
	/**
	 * 이벤트기간 삭제
	 * */
	function deleteEventPeriodInfo(){
		$query = "
				DELETE 
				  FROM EVENT_PROD
				 WHERE USER_ID = ?
				   AND SEQ = ?
				";
		return $query;
	}
	
	
	/**
	 * 이벤트기간사용 객실 삭제
	 * */
	function deleteEventPeriodRoomInfo(){
		$query = "
				DELETE 
				  FROM EVENT_PROD_ROOM 
				 WHERE USER_ID = ?
				   AND SEQ = ?
				";
		return $query;
	}


    /**
     * 이벤트기간사용 객실 저장
     * */
    function insertEventPeriodRoomInfo(){
        $query = "
				INSERT INTO EVENT_PROD_ROOM 
				   SET USER_ID = ?
				     , SEQ = ?
				     , ROOM_CODE = ?
				     , REG_USER = ?
				     , REG_DATE = NOW() 
				";
        return $query;
    }


	/**
	 * 이벤트기간사용 객실 저장
	 * */
	function insertSpecialPeriodRoomInfo(){
		$query = "
				INSERT INTO SPX_X_DATE 
				   SET USER_ID = ?
				     , CHCK_IN_DATE = ?
				     , ROOM_CODE = ?
				     , SP_DATE_NM = ?
				     , ROOM_PRCE = ?
				     , REG_USER = ?
				     , REG_DATE = NOW()
				     , PARENTS_SEQ = ?
				";
		return $query;
	}


    /**
     * 이벤트기간사용 객실 조회
     * */
    function selectSpecialPeriodRoomInfo($values){
        $query = "
				SELECT ROOM_CODE
				  FROM SPX_X_DATE
				 WHERE USER_ID = ?
				   AND PARENTS_SEQ = ? 
				   GROUP BY ROOM_CODE
				";
        return $this->db->query($query, $values)->result_array();
    }


    /**
	 * 이벤트기간사용 객실 조회
	 * */
	function selectEventPeriodRoomInfo($values){
		$query = "
				SELECT ROOM_CODE
				  FROM EVENT_PROD_ROOM
				 WHERE USER_ID = ?
				   AND SEQ = ? 
				";
		return $this->db->query($query, $values)->result_array();
	}
	
	/**
	 * 이벤트기간 제외날짜조회
	 * */
	function selectEventExclDate($values){
		$query = "
				SELECT USER_ID
				     , SEQ
				     , DATE_FORMAT(EVENT_EXCL_DATE, '%Y-%m-%d') EVENT_EXCL_DATE
				  FROM XEVENT_EXX_DATE_LIST
				 WHERE USER_ID = ?
				   AND SEQ = ?
				";
		return $this->db->query($query, $values)->result_array();
	}
	
	/**
	 * 이벤트기간 제외날짜삭제
	 * */
	function deleteEventExclDate(){
		$query = "
				DELETE
				  FROM XEVENT_EXX_DATE_LIST
				 WHERE USER_ID = ?
				   AND SEQ = ?
				";
		return $query;
	}
	
	/**
	 * 이벤트기간 제외날짜저장
	 * */
	function insertEventExclDate(){
		
		$query = "
				INSERT INTO XEVENT_EXX_DATE_LIST 
				   SET USER_ID = ?
				     , SEQ = ?
				     , EVENT_EXCL_DATE = ?
				     , REG_USER = ?
				     , REG_DATE = NOW()
				";
		return $query;
	}
	
	
	function salePeriodProcess(){
		$mode = $this->input->post('mode');
		$this->db->trans_begin();
		
		$SEQ = $this->input->post('SEQ');
		//할인기간정보 저장
		if($mode == 'I')
		{	
			$values = array($this->session->userdata('userId'));
			$query = "
					SELECT IFNULL(MAX(SEQ), 0) + 1 SEQ 
					  FROM EVENT_PROD A 
					 WHERE USER_ID = ?
					";
			$result = $this->db->query($query, $values)->result_array();
			$SEQ = $result[0]['SEQ'];
			
			$values = array(
					$this->session->userdata('userId')
					, $SEQ
					, $this->input->post('EVENT_NM')
					, $this->input->post('EVENT_OPTN')
					, stripDateFormat($this->input->post('BEGIN_DATE'))
					, stripDateFormat($this->input->post('END_DATE'))
					, stripComma($this->input->post('EVENT_PRCE'))
					, isChecked($this->input->post('WON_UNIT_USE_YN'))
					, isChecked($this->input->post('EVENT_NM_DISP_YN'))
					, $this->input->post('DETL_DAY_OPTN')
					, $this->session->userdata('userId')
					, $this->session->userdata('userId')
                    , isChecked($this->input->post('RED_FONT_YN'))
			);
			$this->db->query($this->insertEventPeriodInfo(), $values);
		}
		else if($mode == 'U')
		{
			$values = array(
					$this->input->post('EVENT_NM')
					, $this->input->post('EVENT_OPTN')
					, stripDateFormat($this->input->post('BEGIN_DATE'))
					, stripDateFormat($this->input->post('END_DATE'))
					, stripComma($this->input->post('EVENT_PRCE'))
					, isChecked($this->input->post('WON_UNIT_USE_YN'))
					, isChecked($this->input->post('EVENT_NM_DISP_YN'))
					, $this->input->post('DETL_DAY_OPTN')
					, $this->session->userdata('userId')
                    , isChecked($this->input->post('RED_FONT_YN'))
					, $this->session->userdata('userId')
					, $this->input->post('SEQ')
			);
			$this->db->query($this->updateEventPeriodInfo(), $values);
		}
		else if($mode == 'D')
		{
			$values = array(
					$this->session->userdata('userId')
					, $SEQ
			);
			$this->db->query($this->deleteEventPeriodInfo(), $values);
			
			$this->db->query($this->deleteEventExclDate(), $values);
			
			$this->db->query($this->deleteEventPeriodRoomInfo(), $values);
		}
		
		if($mode == 'I' || $mode == 'U'){
			//할인기간 제외날짜 저장
			$EXCL_DATES = $this->input->post('EXCL_DATES');
			
			$values = array(
					$this->session->userdata('userId')
					, $SEQ
			);
			$this->db->query($this->deleteEventExclDate(), $values);
			
			if(count($EXCL_DATES) > 0 && is_array($EXCL_DATES)){
				for($i = 0, $cnt = count($EXCL_DATES); $i < $cnt; $i++){
					$values = array(
							$this->session->userdata('userId')
							, $SEQ
							, $EXCL_DATES[$i]
							, $this->session->userdata('userId')
					);
					$this->db->query($this->insertEventExclDate(), $values);
				}
			}
				
			//할인기간 방목록 저장
			$ROOM_CODE = $this->input->post('ROOM_CODE');
			if(count($ROOM_CODE) > 0 && is_array($ROOM_CODE)){
				$values = array(
						$this->session->userdata('userId')
						, $SEQ
				);
				$this->db->query($this->deleteEventPeriodRoomInfo(), $values);
		
		
				for($i = 0, $cnt = count($ROOM_CODE); $i < $cnt; $i++){
					$values = array(
							$this->session->userdata('userId')
							, $SEQ
							, $ROOM_CODE[$i]
							, $this->session->userdata('userId')
					);
					$this->db->query($this->insertEventPeriodRoomInfo(), $values);
				}
			}
		}
		
		if ($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();
		}
		else
		{
			$this->db->trans_commit();
			if($mode == 'I')
			{
				$this->commhistory->hisType3($this->input->post('EVENT_NM')." 할인기간 추가");
			}
			else if($mode == 'U')
			{
				$this->commhistory->hisType3($this->input->post('EVENT_NM')." 할인기간 수정");
			}
			else if($mode == 'D')
			{
				$this->commhistory->hisType3($this->input->post('EVENT_NM')." 할인기간 삭제");
			}
		}
		
	}


    function specialPeriodProcess()
    {
        $mode = $this->input->post('mode');
        $this->db->trans_begin();

        $SEQ = $this->input->post('SEQ');
        //할인기간정보 저장

        if ($mode == 'I') {

            $values = array(
                $this->session->userdata('userId')
            , stripDateFormat($this->input->post('CHCK_STAR_DATE'))
            , stripDateFormat($this->input->post('CHCK_END_DATE'))
            , $this->input->post('SP_DATE_NM')
            , $this->input->post('ROOM_PRCE')
            , isChecked($this->input->post('SP_DATE_NM_DISP_YN'))
            );
			$this->db->query($this->insertSpecialPeriodInfo(), $values);
			
			$values = array($this->session->userdata('userId'));
			$query = "
						SELECT MAX(SEQ) AS SEQ
						FROM XX_DATE_LIST						
						";
			$result = $this->db->query($query, $values)->result_array();
			$SEQ = $result[0]['SEQ'];

        } else if ($mode == 'U') {
            $values = array(
                stripDateFormat($this->input->post('CHCK_STAR_DATE'))
            , stripDateFormat($this->input->post('CHCK_END_DATE'))
            , $this->input->post('SP_DATE_NM')
            , $this->input->post('ROOM_PRCE')
            , isChecked($this->input->post('SP_DATE_NM_DISP_YN'))
            , $this->session->userdata('userId')
            , $this->input->post('SEQ')
            );
            $this->db->query($this->updateSpecialPeriodInfo(), $values);
            print_r($this->db->last_query());

            $SEQ = $this->input->post('SEQ');
        } else if ($mode == 'D') {

            $values = array(
                $this->session->userdata('userId')
            , $this->input->post('SEQ')
            );

            $this->db->query($this->deleteSpecialPeriodInfo(), $values);
            $this->db->query($this->deleteSpecialPeriodRoomInfo(), $values);

        }

        if ($mode == 'I' || $mode == 'U') {
            //할인기간 방목록 저장
            $ROOM_CODE = $this->input->post('ROOM_CODE');

            print_r($ROOM_CODE);


            $s_date = $this->input->post('CHCK_STAR_DATE');
            $e_date = $this->input->post('CHCK_END_DATE');

            $array_date = array();

            $term = intval((strtotime($e_date) - strtotime($s_date)) / 86400); //날짜 사이의 일수를 구한다.
            for ($i = 0; $i <= $term; $i++) {
                array_push($array_date, stripDateFormat(date("Y-m-d", strtotime($s_date . '+' . $i . ' day')))); //두 날짜사이의 날짜를 구한다.
            }

            if (count($ROOM_CODE) > 0 && is_array($ROOM_CODE)) {
                $values = array(
                    $this->session->userdata('userId')
                , $SEQ
                );
                $this->db->query($this->deleteSpecialPeriodRoomInfo(), $values);


                for ($i = 0, $cnt = count($ROOM_CODE); $i < $cnt; $i++) {

                    for ($_i = 0; $_i < count($array_date); $_i++) {
                        $values = array(
                            $this->session->userdata('userId')
                        , $array_date[$_i]
                        , $ROOM_CODE[$i]
                        , $this->input->post('SP_DATE_NM')
                        , $this->input->post('ROOM_PRCE')
                        , $this->session->userdata('userId')
                        , $SEQ
                        );

                        $this->db->query($this->insertSpecialPeriodRoomInfo(), $values);
                    }
                }
            }
        }
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
        } else {
            $this->db->trans_commit();
            if ($mode == 'I') {
                $this->commhistory->hisType7($this->input->post('EVENT_NM') . " 특별기간 추가", "");
            } else if ($mode == 'U') {
                $this->commhistory->hisType7($this->input->post('EVENT_NM') . " 특별기간 수정", "");
            } else if ($mode == 'D') {
                $this->commhistory->hisType7($this->input->post('EVENT_NM') . " 특별기간 삭제", "");
            }


        }
    }

	/**
	 * 공휴일목록*/
	function selectHolidayList(){
		$param = array($this->session->userdata('userId'));
		$query = "
                    SELECT DATE_FORMAT(BASE_HOLI_DATE, '%Y-%m-%d') BASE_HOLI_DATE 
                         , USE_YN
                         , BASE_HOLI_NM
                         , BASE_HOLI_DATE CALBASE_HOLI_DATE
                         , YSTR_PRCE_YN
                      FROM BASE_HOX_DATE_LIST
				 WHERE USER_ID = ?
				";
		return $this->db->query($query, $param)->result_array();
	}
	
	function selectHoliList(){
		$param = array();
		$query = "
				SELECT DATE_FORMAT(HOLI_DATE, '%Y-%m-%d') HOLI_DATE
				     , HOLI_NM
				     , USE_YSTD_CONF_YN YSTD_CONF_YN
				     , HOLI_DATE CALBASE_HOLI_DATE
				 FROM HOLIDAY_LIST
				WHERE SUBSTRING(HOLI_DATE, 1, 4) >= DATE_FORMAT(NOW(), '%Y')
				";
		return $this->db->query($query, $param)->result_array();
	}
	
	
	/**
	 * 공휴일로 사용지정 추가
	 * */
	function insertHoliday($_USE_YN,
                           $_BASE_HOLI_DATE,
                           $_BASE_HOLI_NM,
                           $_YDTD_CONF_YN
                            ){
		$this->db->trans_begin();
		$param = array($this->session->userdata('userId'));
		$query = "
				DELETE
				  FROM BASE_HOX_DATE_LIST
				 WHERE USER_ID = ?
				";
		$this->db->query($query, $param);
		
		
		$query = "
				INSERT INTO BASE_HOX_DATE_LIST
		           SET USER_ID 			= ?
		             , BASE_HOLI_DATE 	= ?
				     , BASE_HOLI_NM     = ?
		             , USE_YN 			= ?
		             , REG_USER 		= ?
		             , REG_DATE 		= NOW()
		             , YSTR_PRCE_YN 	= ?
				";
		$USE_YN = explode(",", $_USE_YN);
		$BASE_HOLI_DATE = explode(",", $_BASE_HOLI_DATE);
		$BASE_HOLI_NM = explode(",", $_BASE_HOLI_NM);
		$YDTD_CONF_YN = explode(",", $_YDTD_CONF_YN);
		
		for($i = 0, $cnt = count($BASE_HOLI_DATE); $i < $cnt; $i++){
			$param = array(
					$this->session->userdata('userId')
					, stripDateFormat($BASE_HOLI_DATE[$i])
					, $BASE_HOLI_NM[$i]
					, $USE_YN[$i]
					, $this->session->userdata('userId')
					, $YDTD_CONF_YN[$i]
			);
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
	 * 공휴일로 사용지정 삭제
	 * */
	function deleteHoliday($param){
		$this->db->trans_begin();
		$query = "
				DELETE
				  FROM BASE_HOX_DATE_LIST
				 WHERE USER_ID = ?
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
	 * 관리자지정 공휴일 추가
	 * */
	function insertUserHoliday($param){
		$this->db->trans_begin();
		$query = "
				INSERT INTO USER_HOLI_DATE_LIST
				   SET USER_HOLI_DATE	= ?
				     , USER_HOLI_NM 	= ?
				     , USER_ID 			= ?
				     , REG_USER 		= ?
				     , REG_DATE 		= NOW()
				     , YSTR_PRCE_YN 	= ?
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
	 * 관리자지정 공휴일 삭제
	 * */
	function deleteUserHoliday($param){
		$this->db->trans_begin();
		$query = "
				DELETE
				  FROM USER_HOLI_DATE_LIST
				 WHERE USER_ID = ?
				   AND USER_HOLI_DATE = ?
				   
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
	 * 관리자지정 공휴일 목록
	 * */
	function selectUserHoliday(){
		$param = array($this->session->userdata('userId'));
		$query = "
				SELECT DATE_FORMAT(USER_HOLI_DATE, '%Y-%m-%d') USER_HOLI_DATE
				     , USER_HOLI_NM
				  FROM USER_HOLI_DATE_LIST
				 WHERE USER_ID = ?
				 ORDER BY USER_HOLI_DATE
				";
		return $this->db->query($query, $param)->result_array();
	}
	
	/**
	 * 공휴일 가격지정정보
	 * */
	function selectHolidayPriceConf(){
		$param = array($this->session->userdata('userId'));
		$query = "
				SELECT HOLY_DAY_YSTD_CONF
				     , HOLY_DAY_CONF
				  FROM ADMIN_XXXX
				 WHERE USER_ID = ?
				";
		$result = $this->db->query($query, $param)->result_array();
		if(count($result) > 0){
			return $result[0];
		}
	}
	
	/**
	 * 공휴일 가격지정정보수정 - 전날
	 * */
	function updateHolidayYstdConf($param){
		$this->db->trans_begin();
		
		$query = "
				UPDATE ADMIN_XXXX
				   SET HOLY_DAY_YSTD_CONF = ?
				 WHERE USER_ID = ? 
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
	 * 공휴일 가격지정정보수정 -당일
	 * */
	function updateHolidayConf($param){
		$this->db->trans_begin();
		
		$query = "
				UPDATE ADMIN_XXXX
				   SET HOLY_DAY_CONF = ?
				 WHERE USER_ID = ?
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
	 * 연박할인설정목록
	 * */
	function selectConsAccoList(){
		$userId = $this->session->userdata('userId');
		$param = array($userId);
		$query = "
				SELECT SEQ        
					 , SUBJECT    
					 , DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d') BEGIN_DATE 
					 , DATE_FORMAT(END_DATE, '%Y-%m-%d') END_DATE   
					 , CONS_DAYS  
					 , OPT1       
					 , AMT        
					 , OPT2
				  FROM XXX_SALE_PROD
				 WHERE USER_ID = ? 
				";
		return $this->db->query($query, $param)->result_array();
	}
	
	/**
	 * 연박할인설정단건
	 * */
	function selectConsAcco(){
		$userId = $this->session->userdata('userId');
		$param = array($userId, $this->input->post('SEQ'));
		$query = "
				SELECT SEQ        
					 , SUBJECT    
					 , DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d') BEGIN_DATE 
					 , DATE_FORMAT(END_DATE, '%Y-%m-%d') END_DATE   
					 , CONS_DAYS  
					 , OPT1       
					 , AMT        
					 , OPT2
				     , WDAY
				  FROM XXX_SALE_PROD
				 WHERE USER_ID = ? 
				   AND SEQ = ?
				";
		return $this->db->query($query, $param)->result_array();
	}
	
	/**
	 * 이벤트기간사용 객실 조회
	 * */
	function selectConsAccoRoomInfo(){
		$userId = $this->session->userdata('userId');
		$param = array($userId, $this->input->post('SEQ'));
		$query = "
				SELECT ROOM_CODE
				  FROM XXX_SALE_PROD_ROOM
				 WHERE USER_ID = ?
				   AND SEQ = ?
				";
		return $this->db->query($query, $param)->result_array();
	}
	
	
	/**
	 * 연박할인설정저장
	 * */
	function insertConsAcco(){
		$this->db->trans_begin();
		$userId = $this->session->userdata('userId');
		$ROOM_CODES = $this->input->post('ROOM_CODE');

		$values = array($this->session->userdata('userId'));
		$query = "
					SELECT IFNULL(MAX(SEQ), 0) + 1 SEQ
					  FROM XXX_SALE_PROD A
					 WHERE USER_ID = ?
					";
		$result = $this->db->query($query, $values)->result_array();
		$SEQ = $result[0]['SEQ'];
		
		$param = array($userId
						, $SEQ
						, $this->input->post('SUBJECT')
						, stripDateFormat($this->input->post('BEGIN_DATE'))
						, stripDateFormat($this->input->post('END_DATE'))
						, $this->input->post('CONS_DAYS')
						, $this->input->post('OPT1')
						, $this->input->post('AMT')
						, $this->input->post('OPT2')
				        , implode("||", $this->input->post('WDAY'))
						, $userId
						, $userId
		);
		$query = "
				INSERT INTO XXX_SALE_PROD
				   SET USER_ID    = ?
					 , SEQ        = ?
					 , SUBJECT    = ?
					 , BEGIN_DATE = ?
					 , END_DATE   = ?
					 , CONS_DAYS  = ?
					 , OPT1       = ?
					 , AMT        = ?
					 , OPT2       = ?
				     , WDAY       = ?
					 , REG_USER   = ?
					 , MODI_USER  = ?
					 , REG_DATE   = NOW()
					 , MODI_DATE  = NOW()
				";
		$this->db->query($query, $param);
		
		foreach($ROOM_CODES as $item){
			$param = array($userId  
							, $SEQ      
							, $item
							, $userId 
			);
			$query = "
					INSERT INTO XXX_SALE_PROD_ROOM
					   SET USER_ID   = ?
						 , SEQ       = ?
						 , ROOM_CODE = ?
						 , REG_USER  = ?
						 , REG_DATE  = NOW()
					";
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
	 * 연박할인설정수정
	 * */
	function updateConsAcco(){
		$this->db->trans_begin();
		$userId = $this->session->userdata('userId');
		$ROOM_CODES = $this->input->post('ROOM_CODE');
		$SEQ = $this->input->post('SEQ');
		
		$param = array($this->input->post('SUBJECT')
				, stripDateFormat($this->input->post('BEGIN_DATE'))
				, stripDateFormat($this->input->post('END_DATE'))
				, $this->input->post('CONS_DAYS')
				, $this->input->post('OPT1')
				, $this->input->post('AMT')
				, $this->input->post('OPT2')
				, implode("||", $this->input->post('WDAY'))
				, $userId
				, $userId
				, $SEQ
		);
		$query = "
			  UPDATE XXX_SALE_PROD
				 SET SUBJECT    = ?
					 , BEGIN_DATE = ?
					 , END_DATE   = ?
					 , CONS_DAYS  = ?
					 , OPT1       = ?
					 , AMT        = ?
					 , OPT2       = ?
				     , WDAY       = ?
					 , MODI_USER  = ?
					 , MODI_DATE  = NOW()
			   WHERE USER_ID = ?
			     AND SEQ = ?
				";
		$this->db->query($query, $param);
		
		
		$param = array($userId
				, $SEQ
		);
		$query = "
				DELETE
				  FROM XXX_SALE_PROD_ROOM
				 WHERE USER_ID = ?
			       AND SEQ = ? 
				";
		$this->db->query($query, $param);
		
		
		foreach($ROOM_CODES as $item){
			$param = array($userId
					, $SEQ
					, $item
					, $userId
			);
			$query = "
					INSERT INTO XXX_SALE_PROD_ROOM
					   SET USER_ID   = ?
						 , SEQ       = ?
						 , ROOM_CODE = ?
						 , REG_USER  = ?
						 , REG_DATE  = NOW()
					";
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
	 * 연박할인설정삭제
	 * */
	function deleteConsAcco(){
		$this->db->trans_begin();
		$userId = $this->session->userdata('userId');
		$SEQ = $this->input->post('SEQ');
		
		$param = array($userId
				, $SEQ
		);
		
		$query = "
				DELETE 
				  FROM XXX_SALE_PROD
				 WHERE USER_ID = ?
				   AND SEQ = ?
				";
		$this->db->query($query, $param);

		$query = "
				DELETE
				  FROM XXX_SALE_PROD_ROOM
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
     * 특별기간 삭제
     * */
    function deleteSpecialPeriodInfo(){
        $query = "
				DELETE 
				  FROM XX_DATE_LIST
				 WHERE USER_ID = ?
				   AND SEQ = ?
				";
        return $query;
    }


    /**
     * 특별기간사용 객실 삭제
     * */
    function deleteSpecialPeriodRoomInfo(){
        $query = "
				DELETE 
				  FROM SPX_X_DATE 
				 WHERE USER_ID = ?
				   AND PARENTS_SEQ = ?
				";
        return $query;
    }



    /**
     * 특별기간목록
     * */
    function selectSpecialPeriodList($values){
        $query = "
				SELECT SEQ
				     , USER_ID
				     , DATE_FORMAT(CHCK_STAR_DATE, '%Y-%m-%d') CHCK_STAR_DATE
				     , DATE_FORMAT(CHCK_END_DATE, '%Y-%m-%d') CHCK_END_DATE
				     , ROOM_CODE
				     , SP_DATE_NM
				     , ROOM_PRCE
				     , SP_DATE_NM_DISP_YN
				     , REG_DATE
				  FROM XX_DATE_LIST
				 WHERE USER_ID = ?
				";
        return $this->db->query($query, $values)->result_array();
    }

    /**
     * 수정시 호출할 특별기간정보
     * */
    function selectSpecialPeriodInfo($values){
        $query = "
				SELECT SEQ
				     , USER_ID
				     , FORMAT(ROOM_PRCE, 0) ROOM_PRCE
				     , DATE_FORMAT(CHCK_STAR_DATE, '%Y-%m-%d') CHCK_STAR_DATE
				     , DATE_FORMAT(CHCK_END_DATE, '%Y-%m-%d') CHCK_END_DATE
				     , ROOM_CODE
				     , SP_DATE_NM
				     , ROOM_PRCE
				     , REG_DATE
				     , SP_DATE_NM_DISP_YN
				  FROM XX_DATE_LIST
				 WHERE USER_ID = ?
				   AND SEQ = ?
				";
        $result = $this->db->query($query, $values)->result_array();
        if(count($result) > 0){
            return $result[0];
        }else{
            return null;
        }
    }



}
