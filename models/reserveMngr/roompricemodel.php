<?php
class RoomPriceModel extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    function selectExistUser($userId){
        $param = array($userId);
        $query = "
				SELECT COUNT(USER_ID) CNT
				  FROM ADMIN_XXXX
				 WHERE USER_ID = ?
				";
        $result = $this->db->query($query, $param)->result_array();
        if($result[0]['CNT'] > 0){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 전체가격조회
     * */
    function selectAllPricelist($userId, $roomCode){
        $param = array($userId, $roomCode);

        $query = "
				SELECT A.ROOM_CODE
				     , RPI.PROD_CODE
				     , A.WEEK_PRCE
				     , A.FRD_PRCE
				     , A.SAT_PRCE
				     , A.SUN_PRCE
				     , RPI.PROD_NM 
                     , C.PROD_NM AS PROD_NM_USE 
                     , C.USE_YN AS PROD_USE
				     , A.USE_YN
				     , A.SEQ
				     , C.BEGIN_DATE
				     , C.END_DATE
				  FROM PROD_CODE RPI
				  LEFT OUTER JOIN ROOM_PRICE_INFO A
				    ON RPI.PROD_CODE = A.PROD_CODE
				   AND A.USER_ID = ?
				   AND A.ROOM_CODE = ?
				  LEFT JOIN ROOM_PXX_XXX C
				    ON A.USER_ID = C.USER_ID
				   AND A.PROD_CODE = C.PROD_CODE
				 WHERE A.USE_YN = 'Y'
				 ORDER BY RPI.PROD_CODE, A.ROOM_CODE, A.PROD_CODE
				";
        return $this->db->query($query, $param)->result_array();
    }

    /**
     * 방목록조회
     * */
    function selectRoomList($userId){
        $param = array($userId);
        $orderby = "ORDER BY T.SORT_NO , R.SORT_NO";

        if(($userId === "primera") 
        || ($userId === "siesta")
        || ($userId === "casadebali")
        || ($userId === "elystay" )
        )
        {
          $orderby = "ORDER BY R.SORT_NO, T.SORT_NO";
        }

        $query = "
				SELECT R.ROOM_CODE
				     , R.TYPE_NM
				     , T.TYPE_NAME
				     , T.TYPE_NM_YN
				     , R.ROOM_TYPE
				     , R.ADLT_BASE_PERS
				     , R.ADLT_MAX_PERS
				     , R.TYPE
				     , R.ROOM_IMG
				  FROM ROOM_XXX R LEFT JOIN TYPE_INFO T ON R.TYPE_IDX = T.IDX
			     WHERE R.USER_ID = ?
				   AND (R.USE_YN = 'N' OR R.USE_YN = 'C') #USE_YN이지만 비공개여부이므로 N으로 검색해야함
			" . $orderby;

        return $this->db->query($query, $param)->result_array();
    }


    /**
     * 방목록조회 (고객용)
     * */
    function selectRoomClientList($userId){
        $param = array($userId);

        $query = "
				SELECT ROOM_CODE
				     , TYPE_NM
				     , ROOM_TYPE
				     , ADLT_BASE_PERS
				     , ADLT_MAX_PERS
				     , TYPE
				     , ROOM_IMG
				     , BED_INFO
				     , INTERIOR
				  FROM ROOM_XXX
			     WHERE USER_ID = ?
				   AND (USE_YN = 'N') #USE_YN이지만 비공개여부이므로 N으로 검색해야함
				 ORDER BY SORT_NO
			";
        return $this->db->query($query, $param)->result_array();
    }


    /**
     * 방목록조회 (고객용)
     * */
    function selectRoomClientList4($userId , $typeIdx){
        $param = array($typeIdx , $userId);

        $query = "
				SELECT r.ROOM_CODE
				     , r.TYPE_NM
				     , r.ROOM_TYPE
				     , r.ADLT_BASE_PERS
				     , r.ADLT_MAX_PERS
				     , r.TYPE
				     , r.ROOM_IMG
				  FROM ROOM_XXX r JOIN TYPE_INFO t ON r.type_idx  = ?
			     WHERE r.USER_ID = ?
				   AND (r.USE_YN = 'N') #USE_YN이지만 비공개여부이므로 N으로 검색해야함

                 GROUP BY r.ROOM_CODE
				 ORDER BY r.SORT_NO
			";
        return $this->db->query($query, $param)->result_array();
    }


    /**
     * 방목록조회 영문판 (고객용)
     * */
    function selectRoomClientList4_en($userId , $typeIdx){
        $userId_en = $userId."_en";
        $param = array($userId_en, $userId_en, $typeIdx , $userId);

        $query = "
				SELECT r.ROOM_CODE
             ,(SELECT TYPE_NM FROM ROOM_XXX WHERE USER_ID = ? AND ROOM_CODE = r.ROOM_CODE) AS TYPE_NM
             ,(SELECT ROOM_TYPE FROM ROOM_XXX WHERE USER_ID = ? AND ROOM_CODE = r.ROOM_CODE) AS ROOM_TYPE
				     , r.ADLT_BASE_PERS
				     , r.ADLT_MAX_PERS
				     , r.TYPE
				     , r.ROOM_IMG
				  FROM ROOM_XXX r JOIN TYPE_INFO t ON r.type_idx  = ?
			     WHERE r.USER_ID = ?
				   AND (r.USE_YN = 'N') #USE_YN이지만 비공개여부이므로 N으로 검색해야함

                 GROUP BY r.ROOM_CODE
				 ORDER BY r.SORT_NO
			";
        return $this->db->query($query, $param)->result_array();
    }

    /**
     * 타입으로 방목록조회 (고객용)
     * */
    function selectRoomTypeClientList($userId){
        $param = array($userId);

        $query = "
				SELECT
				     T.TYPE_NAME
				     , T.IDX
				     , R.ROOM_CODE
				     , IF(ISNULL(T.TYPE_NAME ) , R.TYPE_NM , CONCAT(T.TYPE_NAME , '-' ,  R.TYPE_NM )) as TYPE_NM
				     , IF(ISNULL(T.TYPE_NAME ) , R.TYPE_NM ,  R.TYPE_NM ) as TYPE_NM2
				     , R.ROOM_TYPE
				     , T.TYPE_DESC
				     , R.ADLT_BASE_PERS
				     , R.ADLT_MAX_PERS
				     , R.TYPE
				     , R.ROOM_IMG
				  FROM ROOM_XXX R JOIN TYPE_INFO T  ON T.IDX = R.TYPE_IDX
			     WHERE R.USER_ID = ?
				   AND (R.USE_YN = 'N') #USE_YN이지만 비공개여부이므로 N으로 검색해야함
				 ORDER BY T.SORT_NO , R.SORT_NO
			";
        return $this->db->query($query, $param)->result_array();
    }

    /**
     * 타입 목록조회 (고객용)
     */
    function selectTypeClientList($userId){
        $param = array($userId);

        $query = "
				SELECT
				     T.TYPE_NAME
				     , T.IDX , T.TYPE_DESC

				  FROM ROOM_XXX R JOIN TYPE_INFO T ON R.TYPE_IDX = T.IDX
			     WHERE T.USER_ID = ?
				   AND (R.USE_YN = 'N') #USE_YN이지만 비공개여부이므로 N으로 검색해야함
                 GROUP BY T.IDX ORDER BY T.SORT_NO
			";
        return $this->db->query($query, $param)->result_array();
    }


    /**
     * 타입으로  방목록조회 (고객용)
     */
    function selectRoomClientListByType($userId , $typeIdx){
        $param = array($userId, $typeIdx);

        $query = "
				SELECT
				     R.ROOM_CODE
				     , R.TYPE_NM
				     , R.ROOM_TYPE
				     , R.ADLT_BASE_PERS
				     , R.ADLT_MAX_PERS
				     , R.TYPE
				     , R.ROOM_IMG

				  FROM TYPE_INFO T LEFT JOIN ROOM_XXX R  ON T.IDX = R.TYPE_IDX
			     WHERE T.USER_ID = ? AND T.IDX = ?
				   #AND (R.USE_YN = 'N') #USE_YN이지만 비공개여부이므로 N으로 검색해야함
				 ORDER BY R.SORT_NO
			";
        return $this->db->query($query, $param)->result_array();
    }

    /**
     * 할인기간목록조회
     * 한꺼번에 조회해오고 라이브러리에서 직접 필터
     * */
    function selectEventProdList($userId){
        $param = array($userId);
        $query = "
				SELECT B.ROOM_CODE
				     , EVENT_NM 
				     , EVENT_OPTN 
				     , BEGIN_DATE 
				     , END_DATE 
				     , EVENT_PRCE 
				     , WON_UNIT_USE_YN 
				     , EVENT_NM_DISP_YN 
				     , DETL_DAY_OPTN 
                     , RED_FONT_YN
				  FROM EVENT_PROD A 
				     , XEVENT_PROD_ROOM B
				 WHERE A.USER_ID = ?
				   AND A.USER_ID = B.USER_ID
				   AND A.SEQ = B.SEQ
				";
        return $this->db->query($query, $param)->result_array();
    }

    /**
     * 할인제외기간목록조회
     * */
    function selectEventExclDateList($userId){
        $param = array($userId);
        $query = "
				SELECT ROOM_CODE
				     , A.EVENT_EXCL_DATE
				  FROM EVENT_EXCL_DATE_LIST A
				     , XEVENT_PROD_ROOM B
				 WHERE A.USER_ID = ?
				   AND A.USER_ID = B.USER_ID
				   AND A.SEQ = B.SEQ	
			";
        return $this->db->query($query, $param)->result_array();
    }

	function selectEventExclDateList_new($userId,$check_day){
		$check_day_plus_7 = date('Ymd', strtotime($check_day . ' +7 days'));
		
        $param = array($userId,$check_day,$check_day_plus_7);
        $query = "
				SELECT ROOM_CODE, A.SEQ, A.EVENT_EXCL_DATE
				  FROM EVENT_EXCL_DATE_LIST A
				     , XEVENT_PROD_ROOM B
				 WHERE A.USER_ID = ?
				   AND A.USER_ID = B.USER_ID
				   AND A.SEQ = B.SEQ
				   AND A.EVENT_EXCL_DATE BETWEEN ? AND ?
			";
        return $this->db->query($query, $param)->result_array();
    }

    /**
     * 공휴일 목록조회
     * */
    function selectHolydayList($userId){
        $param = array($userId, $userId);
        //업소공휴일 전날도 체크해야된다면 USER_XXX_DATE_LIST 테이블의 ystr_prce_yn을 Y로 설정 하면됨
        $query = "
				SELECT BASE_HOLI_DATE
				     , BASE_HOLI_NM
				     , HOLI_DAY_DIV
				     , YSTR_PRCE_YN
				  FROM (
				      SELECT BASE_HOLI_DATE
				      	   , BASE_HOLI_NM
				           , '1' HOLI_DAY_DIV
				           , YSTR_PRCE_YN
				      	FROM BASE_HOLI_DATE_LIST
				       WHERE USER_ID = ? 
				         AND USE_YN  = 'Y'         
				      
				      UNION ALL 
				               
				      SELECT USER_HOLI_DATE
				           , USER_HOLI_NM
				           , '2'
				           , YSTR_PRCE_YN
				        FROM USER_XXX_DATE_LIST
				       WHERE USER_ID = ?          
				      ) A
                  ORDER BY  BASE_HOLI_DATE DESC   
				 #ORDER BY  BASE_HOLI_DATE
				 
				";

        return $this->db->query($query, $param)->result_array();
    }

    /**
     *  공휴일가격 설정값조회
     * */
    function selectHolydayPriceConf($userId){
        $param = array($userId);

        $query = "
				SELECT HOLY_DAY_YSTD_CONF	
				     , HOLY_DAY_CONF
				  FROM ADMIN_XXXX
				 WHERE USER_ID = ? 		
				";

        $result = $this->db->query($query, $param)->result_array();
        if(count($result) > 0){
            return $result[0];
        }else{
            return null;
        }
    }
    /**
     * 특별가격조회
     * */
    function selectSpDateList($userId, $startdate, $enddate){
        $param = array($userId, $startdate, $enddate);
        $query = "
				SELECT CHCK_IN_DATE
				     , ROOM_CODE
				     , SP_DATE_NM
				     , ROOM_PRCE
				  FROM SPX_X_DATE
				 WHERE USER_ID = ?
				 and CHCK_IN_DATE BETWEEN ? and ?
				";

        return $this->db->query($query, $param)->result_array();
    }

	 function selectSpDateList_rooms($userId,$YYYY,$mm, $monthRange){
		
		$likedate = '%'.$YYYY.$mm.'%';
		$likedate2 = '%'.$YYYY.$mm.'%';
		$likedate3 = '%'.$YYYY.$mm.'%';
		

		if($mm == 12)
		 {
			$YYYY = $YYYY+1;
			$mm = '01';
			$likedate2 = '%'.$YYYY.$mm.'%';
		 }else{
			 if($monthRange > 1 ){
				 for($i=1; $i<=$monthRange; $i++){
					 $j = $i+1;
					 ${"likedate" . $j} = '%' . $YYYY . "0" . ($mm + $i) . '%';
				 }
			 }else{
				 $likedate2 = '%'.$YYYY."0".($mm+$monthRange).'%';
			 }
		 }
		
        //$param = array($userId,$likedate,$likedate2, $likedate3);
        $query = "
				SELECT CHCK_IN_DATE
				     , ROOM_CODE
				     , SP_DATE_NM
				     , ROOM_PRCE
				  FROM SPX_X_DATE
				 WHERE USER_ID = '".$userId."'
				 AND (CHCK_IN_DATE LIKE '".$likedate."'
				 OR CHCK_IN_DATE LIKE '".$likedate2."'
				 OR CHCK_IN_DATE LIKE '".$likedate3."')
				";

        return $this->db->query($query)->result_array();
    }


    function selectSpDateList_light($userId, $startdate, $enddate){
        $param = array($userId, $startdate, $enddate);

        $query = "
				SELECT CHCK_IN_DATE
				     , ROOM_CODE
				     , SP_DATE_NM
				     , ROOM_PRCE
				  FROM SPX_X_DATE
				 WHERE USER_ID = ?
				 and CHCK_IN_DATE BETWEEN ? and ?
				";

        return $this->db->query($query, $param)->result_array();
    }


  /**
     * skin7 특별가격조회
     * */
    function CalendarV7selectSpDateList($userId,$room_code,$chck_in_date){
      $param = array($userId,$room_code,$chck_in_date);
      $query = "
      SELECT CHCK_IN_DATE
           , ROOM_CODE
           , SP_DATE_NM
           , ROOM_PRCE
        FROM SPX_X_DATE
       WHERE USER_ID = ?
       AND ROOM_CODE = ?
       AND CHCK_IN_DATE = ?
      ";

      return $this->db->query($query, $param)->result_array();
    }
	
  /**
     * skin7 방 ROOM_CODE 조회
     * */
    function CalendarV7selectRoomList($userId){
      $param = array($userId);
      $query = "
      SELECT ROOM_CODE
           , TYPE_IDX           
        FROM ROOM_XXX
       WHERE USER_ID = ?
       ORDER BY TYPE_IDX ASC      
      ";

      return $this->db->query($query, $param)->result_array();
    }
	
	/**
     * skin7 특별가격조회 -- 확인완료
     * */
    function CalendarV7TypeSpDate($param_data, $userId){
        // $param = array($userId,$room_code);
        $query = "
				SELECT a.TYPE_IDX, 
                       b.CHCK_IN_DATE, b.SP_DATE_NM, b.ROOM_PRCE
				FROM ROOM_XXX a
                JOIN SPX_X_DATE b ON b.ROOM_CODE = a.ROOM_CODE AND b.USER_Id = a.USER_ID
				WHERE a.USER_ID = '".$userId."' AND a.USE_YN = 'N'
        AND (b.CHCK_IN_DATE >= '".$param_data["start_date"]."' 
        AND b.CHCK_IN_DATE <= '".$param_data["end_date"]."' 
        )
        GROUP BY b.CHCK_IN_DATE, a.TYPE_IDX ";
        
        return $this->db->query($query)->result_array();
    }
    
    
    /**
     * 할인제외기간목록조회 -- 확인완료
     * */
    function CalendarV7TypeEventExcl($param_data, $userId){
        $query = " SELECT a.EVENT_EXCL_DATE,
                          b.ROOM_CODE,
                          c.TYPE_IDX
				   FROM EVENT_EXCL_DATE_LIST a
				   JOIN XEVENT_PROD_ROOM b ON b.SEQ = a.SEQ AND b.USER_ID = a.USER_ID
                   JOIN ROOM_XXX c ON c.ROOM_CODE = b.ROOM_CODE AND c.USER_id = b.USER_ID
				   WHERE a.USER_ID = '".$userId."'
                   AND (a.EVENT_EXCL_DATE >= '".$param_data["start_date"]."' AND  a.EVENT_EXCL_DATE <= '".$param_data["end_date"]."' )
                   AND c.USE_YN = 'N'
                   GROUP BY c.TYPE_IDX, a.EVENT_EXCL_DATE ";
        return $this->db->query($query)->result_array();
    }
    
    /**
     *  연박할인기간목록조회 -- 확인완료 
     */
    function CalendarV7TypeSale($param_data, $user_id){
        $query =" SELECT a.TYPE_IDX,
                         c.CONS_DAYS, c.OPT1, c.AMT, c.OPT2, c.BEGIN_DATE, c.END_DATE, c.WDAY
                  FROM ROOM_XXX a
                  JOIN CONS_SALE_PROD_ROOM b ON b.ROOM_CODE = a.ROOM_CODE AND b.USER_ID = a.USER_Id
                  JOIN CONS_SALE_PROD c ON c.SEQ = b.SEQ AND c.USER_ID = b.USER_Id
                  WHERE a.USER_ID ='".$user_id."' AND a.USE_YN = 'N'
                  AND (c.BEGIN_DATE <= '".$param_data["end_date"]."' )
                  AND (c.END_DATE >= '".$param_data["start_date"]."' )
                  AND c.CONS_DAYS <= '".$param_data["date_len"]."'
                  GROUP BY a.TYPE_IDX";
        
        return $this->db->query($query)->result_array();
    }
}
