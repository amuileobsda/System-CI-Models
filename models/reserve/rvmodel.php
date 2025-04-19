<?php
class RvModel extends CI_Model {
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

 
    //사용자가 지정한 현재달력에서 ~달까지 달력 List에 뿌려줄것인지.
    function selectReserveRange($userId){
        $param = array($userId);
        $query = "
				SELECT RESV_ABLE_DATE
				  FROM ADMIN_XXXX
				 WHERE USER_ID = ? 
				";
        $result = $this->db->query($query, $param)->result_array();
        return $result[0]['RESV_ABLE_DATE'];
    }

    /**
     * 사용자예약시 달력 클릭전 저장전 체크인이 가능한지 조회
     * */
    function selectRvAbleDate(){
        $isRvAble1 = "";//예약테이블의 예약가능일 확인
        $isRvAble2 = "";//카드결제시 진행중 테이블의 예약가능일 확인


        $stayingPeriodByDataList = stripDateFormat($this->input->post('DATE'));
        $dSql = '';
        $CHCK_IN_DATE = array();
        if(count($stayingPeriodByDataList) > 0){

            foreach($stayingPeriodByDataList as $item){

                $CHCK_IN_DATE[] = "'".stripDateFormat($item)."'";
            }
            $CHCK_IN_DATE = implode(',', $CHCK_IN_DATE);
            $dSql = "AND B.CHCK_IN_DATE IN (".$CHCK_IN_DATE.")";

        }

        $param = array($this->input->post('USER_ID'), $this->input->post('ROOM_CODE'), $this->input->ip_address());
        $query = "
				SELECT CASE WHEN COUNT(B.CHCK_IN_DATE) <= 0 THEN 'Y'
				            ELSE 'N'
				        END	IS_RV_ABLE			  
				  FROM RESV_XXXX A
				     , RESV_INFO_ROOM B
				 WHERE A.USER_ID = ?
				   AND A.RESV_STAT IN('00','01','09')
				   AND A.USER_ID = B.USER_ID
				   AND A.RESV_NO = B.RESV_NO
				   AND B.ROOM_CODE = ?
				".$dSql;
        $result = $this->db->query($query, $param)->result_array();
        $isRvAble1 = $result[0]['IS_RV_ABLE'];

        $query2 = "
				SELECT CASE WHEN COUNT(CHCK_IN_DATE) <= 0 THEN 'Y'
				            ELSE 'N'
				        END	IS_RV_ABLE
				  FROM XRESV_XING B
				 WHERE USER_ID = ?
				   AND ROOM_CODE = ?
				   AND USER_IP != ?
				   ".$dSql;
        $result2 = $this->db->query($query2, $param)->result_array();
        $isRvAble2 = $result2[0]['IS_RV_ABLE'];

        if($isRvAble1 == 'Y' && $isRvAble2 == 'Y'){
            return 'Y';
        }else{
            return 'N';
        }
    }

    /**
     * 사용자예약 정보 입력시 옵션정보 조회
     * */
    function selectOptionInfo($userId, $roomCode, $WDAY){
        $param = array($WDAY, $WDAY, $WDAY, $userId, $roomCode);
       
                    $param = array($WDAY, $WDAY, $WDAY,$userId, $roomCode,$WDAY, $WDAY, $WDAY, $userId, $roomCode);

                    $query = "

                    SELECT A.OPTN_CODE
                    , OPTN_NM
                    , SORT_NO
                    , BASE_QTY
                    , MAX_QTY
                    , UNIT_NM
                    , WEEK_PRCE
                     , SUN_PRCE
                     , FRD_PRCE
                     , SAT_PRCE
                    , A.GROUP_SEQ
                    , NULL AS GROUP_NM
                    , CASE WHEN ? = '0' THEN SUN_PRCE
                    WHEN ? = '5' THEN FRD_PRCE
                    WHEN ? = '6' THEN SAT_PRCE
                    ELSE WEEK_PRCE
                END PRICE
             #, SPECIAL_CHAR_UNESCAPE(OPTN_DETL_COMT) OPTN_DETL_COMT
                    , NULL AS group_sort
                    , OPTN_DETL_COMT
                    , USE_DAILY_PRICE_YN
                    FROM  ROOM_OPTN A
                    JOIN XROOM_OP_DTL B
                    ON A.OPTN_CODE = B.OPTN_CODE
                    AND A.USER_ID = B.USER_ID
                    WHERE B.USER_ID = ?
                    AND A.GROUP_SEQ IS  NULL
                    AND A.USE_YN = 'Y'
                    AND B.ROOM_CODE = ?
                    AND (A.VIEW_YN != 'Y'
                    OR A.VIEW_YN IS NULL)
                    UNION
                    SELECT A.OPTN_CODE
                    , OPTN_NM
                    , SORT_NO
                    , BASE_QTY
                    , MAX_QTY
                    , UNIT_NM
                    , WEEK_PRCE
                     , SUN_PRCE
                     , FRD_PRCE
                     , SAT_PRCE
                    , D.GROUP_SEQ
                    , D.GROUP_NM
                    , CASE WHEN ? = '0' THEN SUN_PRCE
                    WHEN ? = '5' THEN FRD_PRCE
                    WHEN ? = '6' THEN SAT_PRCE
                    ELSE WEEK_PRCE
                END PRICE
             #, SPECIAL_CHAR_UNESCAPE(OPTN_DETL_COMT) OPTN_DETL_COMT
                    , C.group_sort
                    , OPTN_DETL_COMT
                    , USE_DAILY_PRICE_YN
                    FROM  ROOM_OPTN A

                    JOIN XROOM_OP_DTL B
                    ON A.OPTN_CODE = B.OPTN_CODE
                    AND A.USER_ID = B.USER_ID,
                    GROUP_LIST D,
                    GROUP_OPTN C
                    WHERE B.USER_ID = ?
                    AND C.SEQ = D.GROUP_SEQ
                    AND D.USER_ID = A.USER_ID
                    AND D.USE_YN = 'Y'
                    AND D.OPTN_CODE=  A.OPTN_CODE
                    AND A.USE_YN = 'Y'
                    AND B.ROOM_CODE = ?
                    AND (A.VIEW_YN != 'Y'
                    OR A.VIEW_YN IS NULL)
                    ORDER BY group_sort,SORT_NO;





           ";

        return $this->db->query($query, $param)->result_array();
    }

    /**
     * 사용자예약 정보 입력시 옵션정보 조회 (영문판)
     * */
    function selectOptionInfo_en($userId, $roomCode, $WDAY){
        $param = array($WDAY, $WDAY, $WDAY, $userId, $roomCode);
        $query = "
				SELECT A.OPTN_CODE
				     , (SELECT OPTN_NM FROM ROOM_OPTN WHERE USER_ID = CONCAT(A.USER_ID, '_en') AND OPTN_CODE =  A.OPTN_CODE) AS OPTN_NM
				     , BASE_QTY
				     , MAX_QTY
				     , (SELECT UNIT_NM FROM ROOM_OPTN WHERE USER_ID = CONCAT(A.USER_ID, '_en') AND OPTN_CODE =  A.OPTN_CODE) AS UNIT_NM
					 , WEEK_PRCE
             		 , SUN_PRCE
             		 , FRD_PRCE
             		 , SAT_PRCE
				     , CASE WHEN ? = '0' THEN SUN_PRCE
				            WHEN ? = '5' THEN FRD_PRCE
				            WHEN ? = '6' THEN SAT_PRCE
							ELSE WEEK_PRCE
				        END PRICE
				     #, SPECIAL_CHAR_UNESCAPE(OPTN_DETL_COMT) OPTN_DETL_COMT
				     , (SELECT OPTN_DETL_COMT FROM ROOM_OPTN WHERE USER_ID = CONCAT(A.USER_ID, '_en') AND OPTN_CODE =  A.OPTN_CODE) AS OPTN_DETL_COMT
				     , USE_DAILY_PRICE_YN
				  FROM ROOM_OPTN A
				     , XROOM_OP_DTL B
				 WHERE A.USE_YN = 'Y'
				   AND A.USER_ID = ?
				   AND A.USER_ID = B.USER_ID
                   AND A.OPTN_CODE = B.OPTN_CODE
                   AND B.ROOM_CODE = ?
                   AND (A.VIEW_YN != 'Y'
                   OR A.VIEW_YN IS NULL)
				 ORDER BY SORT_NO
				";
        return $this->db->query($query, $param)->result_array();
    }

    /**
     * 사용자예약 정보 입력시 객실정보 조회
     * */
    function selectRoomInfo($userId, $roomCode){
        $param = array($userId, $roomCode);
        $query = "
				SELECT R.ROOM_CODE
				     , R.TYPE_NM
				     , R.ADLT_BASE_PERS
				     , R.ADLT_MAX_PERS
				     , R.KIDS_MAX_PERS
				     , R.INFT_MAX_PERS
				     , R.KIDS_USE_YN
				     , R.INFT_USE_YN
				     , R.ADLT_EXCS_PRCE
					 , R.KIDS_EXCS_PRCE
					 , R.INFT_EXCS_PRCE
				     , R.INFT_INCL_YN
				     , T.TYPE_NAME
				     , R.ASSIST_CONTENT
				     , R.ASSIST_CONTENT_YN
				  FROM ROOM_XXX R LEFT JOIN TYPE_INFO T ON R.TYPE_IDX = T.IDX
				 WHERE R.USER_ID = ?
				   AND R.ROOM_CODE = ?
				   AND R.USE_YN = 'N'
				   ORDER BY R.SORT_NO;
				";
        return $this->db->query($query, $param)->result_array();
    }


    /**
     * 사용자예약 정보 입력시 객실정보 조회
     * */
    function selectRoomInfoV7($userId, $roomCode, $type_idx){
        $param = array($userId, $roomCode , $type_idx);
        $query = "
				SELECT R.ROOM_CODE
				     , R.TYPE_NM
				     , R.ADLT_BASE_PERS
				     , R.ADLT_MAX_PERS
				     , R.KIDS_MAX_PERS
				     , R.INFT_MAX_PERS
				     , R.KIDS_USE_YN
				     , R.INFT_USE_YN
				     , R.ADLT_EXCS_PRCE
					 , R.KIDS_EXCS_PRCE
					 , R.INFT_EXCS_PRCE
				     , R.INFT_INCL_YN
				     , D.TYPE_NAME
				     , R.ASSIST_CONTENT
				     , R.ASSIST_CONTENT_YN
				  FROM ROOM_XXX R LEFT JOIN DANDI_TYPE_ROOM_MAPPING T ON R.ROOM_CODE = T.ROOM_CODE
				  JOIN DANDI_TYPE_INFO D
				  ON D.IDX = T.TYPE_IDX
				 WHERE R.USER_ID = ?
				   AND R.ROOM_CODE = ?
				   AND R.USE_YN = 'N'
				   AND D.IDX = ?
				   ORDER BY R.SORT_NO
				";
        return $this->db->query($query, $param)->result_array();
    }

    /**
     * 연박할인설정
     * */
    function selectConsAccoInfo($userId, $roomCode){
        $param = array($userId, $roomCode);
        $query = "
				SELECT A.CONS_DAYS
				     , A.OPT1
				     , A.AMT
				     , A.OPT2
				     , A.BEGIN_DATE
				     , A.END_DATE
				     , A.WDAY
				  FROM CONS_SALE_PROD A
				 INNER JOIN CONS_SALE_PROD_ROOM B
				    ON A.USER_ID = B.USER_ID
				   AND A.SEQ = B.SEQ
				 WHERE A.USER_ID = ?
				   AND B.ROOM_CODE = ?
				   AND (DATE_FORMAT(NOW(), '%Y%m%d') BETWEEN BEGIN_DATE AND END_DATE
                        OR DATE_FORMAT(NOW(), '%Y%m%d') < BEGIN_DATE )
				";
        return $this->db->query($query, $param)->result_array();
    }

    /**
     * 인원 명칭 조회
     * */
    function selectPersonDivNm($userId){
        $param = array($userId);
        $query = "
				SELECT CASE WHEN NUM_PER_DIV_ADT = NULL OR NUM_PER_DIV_ADT = '' THEN '성인'
			            ELSE NUM_PER_DIV_ADT
				        END NUM_PER_DIV_ADT
				     , CASE WHEN NUM_PER_DIV_KID = NULL OR NUM_PER_DIV_KID = '' THEN '아동/유아'
				            ELSE NUM_PER_DIV_KID
				        END NUM_PER_DIV_KID
				     , CASE WHEN NUM_PER_DIV_INF = NULL OR NUM_PER_DIV_INF = '' THEN '영유아'
				            ELSE NUM_PER_DIV_INF
				        END NUM_PER_DIV_INF
                     , TAX_YN
					 , TAX_YN_OPT
				  FROM ADMIN_XXXX 
				 WHERE USER_ID = ?
				";
        $result = $this->db->query($query, $param)->result_array();
        if(count($result) > 0){
            return $result[0];
        }
        return null;
    }

    /**
     * 인원 명칭 조회
     * */
    function selectPersonDivNm_en($userId){
        $param = array($userId);
        $query = "
				SELECT CASE WHEN NUM_PER_DIV_ADT = NULL OR NUM_PER_DIV_ADT = '' THEN 'Adult'
			            ELSE NUM_PER_DIV_ADT
				        END NUM_PER_DIV_ADT
				     , CASE WHEN NUM_PER_DIV_KID = NULL OR NUM_PER_DIV_KID = '' THEN 'Children/Infants'
				            ELSE NUM_PER_DIV_KID
				        END NUM_PER_DIV_KID
				     , CASE WHEN NUM_PER_DIV_INF = NULL OR NUM_PER_DIV_INF = '' THEN 'Toddler'
				            ELSE NUM_PER_DIV_INF
				        END NUM_PER_DIV_INF
                     , TAX_YN
					 , TAX_YN_OPT
				  FROM ADMIN_XXXX 
				 WHERE USER_ID = ?
				";
        $result = $this->db->query($query, $param)->result_array();
        if(count($result) > 0){
            return $result[0];
        }
        return null;
    }

    /***
     * 환불기준조회
     *
     */
    function selectRefu($userId){
        $param = array($userId);
        $query = "
				SELECT STD_DAY
				     , CNCL_COMS
				     , REFU_PRCE
				  FROM REFU_INFO
				 WHERE USER_ID = ?
				 ORDER BY CAST(STD_DAY AS DECIMAL)
				";
        return $this->db->query($query, $param)->result_array();
    }

    /***
     * 환불기준조회
     *
     */
    function selectRefuDesc($userId){
        $param = array($userId);
        $query = "
				SELECT STD_DAY
				     , CNCL_COMS
				     , REFU_PRCE
				  FROM REFU_INFO
				 WHERE USER_ID = ?
				 ORDER BY CAST(STD_DAY AS DECIMAL) DESC
				";
        return $this->db->query($query, $param)->result_array();
    }


    /**
     * 기타선택사항조회
     * */
    function selectEtcCheckList($userId){

        $param = array($userId);
        $query = "
				SELECT USER_ID
				     , SEQ
				     , SERV_NM
				     , SERV_DETAIL
				     , SERV_OPTION
				     , SORT_NO
				     , CASE WHEN REQU_YN = 'Y' THEN 'REQUIRED'
				        END REQU_YN
				     , USE_YN
				  FROM ETC_SEL_LIST
				 WHERE USER_ID = ?
				   AND USE_YN = 'Y'
				 ORDER BY SORT_NO
				";

        return $this->db->query($query, $param)->result_array();
    }

    /**
     * 신규 예약번호조회
     * */
    function selectReserveNo($userId){
	    
        $param = array($userId);
        $query = "
				INSERT INTO RESV_NO_INDEX
				   SET USER_ID = ?
				";
        $this->db->query($query, $param);

        $query = "
				SELECT SUBSTRING( CONCAT('0000000000', CONVERT( MAX(RESV_NO), UNSIGNED)), -10 )  RESERVE_CODE
				  FROM RESV_NO_INDEX
				 WHERE USER_ID = ?
				";
        $result = $this->db->query($query, $param)->result_array();


        if(count($result) > 0){
            return $result[0]['RESERVE_CODE'];
        }else{
            return;
        }
    }

    function selectStep3($userId, $resvNo){

        $param = array($userId, $resvNo);
        $query = "
				SELECT FORMAT(B.TOT_PRCE, 0) TOT_PRCE
				     , (SELECT USER_ACCO FROM ADMIN_ACCO_INFO SQ WHERE SQ.USER_ID = A.USER_ID AND SQ.SEQ = A.ACCO_INFO) USER_ACCO 
				     , DATE_FORMAT(B.RESV_CNCL_TIME, '%Y년 %m월 %d일 %h시 %i분') RESV_CNCL_TIME
				     , (SELECT SQ.DEPO_WAIT_TIME
				          FROM ADMIN_XXXX SQ
						 WHERE SQ.USER_ID = A.USER_ID) ENF_TIME
				     , B.RESV_NM
				     , B.RESV_TEL
				     , B.RESV_EMGS_TEL
				     , B.USE_EMONEY
				     , B.PAYM_KIND
				     , B.RESV_EMAIL
				  FROM ROOM_XXX A
				     , (SELECT A.TOT_PRCE
				             , A.RESV_NM
				             , A.RESV_TEL
				             , A.RESV_EMGS_TEL
				             , B.ROOM_CODE
				             , A.USER_ID
				             , A.RESV_CNCL_TIME
				             , A.USE_EMONEY
				             , A.PAYM_KIND
				             , A.RESV_EMAIL
				          FROM RESV_XXXX A
				             , RESV_INFO_ROOM B
				         WHERE A.USER_ID = ?
				           AND A.RESV_NO = ?
				           AND A.USER_ID = B.USER_ID
				           AND A.RESV_NO = B.RESV_NO
				         GROUP BY A.TOT_PRCE
				                 , A.RESV_NM
				                 , A.RESV_TEL
				                 , A.RESV_EMGS_TEL
				                 , B.ROOM_CODE
				                 , A.USER_ID
				                 , A.RESV_CNCL_TIME) B
				 WHERE A.USER_ID = B.USER_ID
				   AND A.ROOM_CODE = B.ROOM_CODE
				";
        return $this->db->query($query, $param)->result_array();
    }

    //예약확인
    function selectMyorder($userId){
        $param = array($userId, $this->input->post('resvNm'), $this->input->post('resvTel'));
        $query = "
                SELECT
					( SELECT RESV_INFO_VIEWABLE FROM ADMIN_XXXX WHERE USER_ID = A.USER_ID ) AS INFO_VIEWABLE,
                    A.RESV_NO,
                    A.RESV_SELLER,
                    ( SELECT DAOUTRX FROM RESV_CARD_XXXX WHERE RESV_NO = A.RESV_NO AND USER_ID = A.USER_ID ) AS DAOUTRX,
                    DATE_FORMAT( ( SELECT MIN( CHCK_IN_DATE ) FROM RESV_INFO_ROOM WHERE RESV_NO = A.RESV_NO ), '%Y-%m-%d' ) AS MIN_DATE,
                    DATE_FORMAT( DATE_ADD(( SELECT MAX( CHCK_IN_DATE ) FROM RESV_INFO_ROOM WHERE RESV_NO = A.RESV_NO ), INTERVAL + 1 DAY ), '%Y-%m-%d' ) AS MAX_DATE,
                    A.PAYM_KIND,
                    A.RESV_STAT,
                    B.CHCK_IN_DATE,
                    C.TYPE_NM,
                    ( SELECT TYPE_NAME FROM TYPE_INFO WHERE USER_ID = A.USER_ID AND IDX = C.TYPE_IDX ) ROOM_TYPE_NM,
                    ( SELECT SUM( ROOM_PRCE ) FROM RESV_INFO_ROOM SQ WHERE SQ.RESV_NO = A.RESV_NO ) ROOM_PRCE,
                    ( SELECT SUM( ADIT_ADLT_PRCE ) + SUM( ADIT_KIDS_PRCE ) + SUM( ADIT_INFT_PRCE ) FROM RESV_INFO_ROOM SQ WHERE SQ.RESV_NO = A.RESV_NO ) PERS_PRCE,
                    ( SELECT IFNULL( SUM( OPTN_PRCE ), 0 ) FROM RESV_INFO_DTL WHERE USER_ID = A.USER_ID AND RESV_NO = A.RESV_NO ) OPTN_PRCE,
                    (
                    SELECT
                        SUM( ROOM_PRCE ) + SUM( ADIT_ADLT_PRCE ) + SUM( ADIT_KIDS_PRCE ) + SUM( ADIT_INFT_PRCE ) 
                    FROM
                        RESV_INFO_ROOM SQ 
                    WHERE
                        SQ.RESV_NO = A.RESV_NO 
                    ) + ( SELECT IFNULL( SUM( OPTN_PRCE ), 0 ) FROM RESV_INFO_DTL WHERE USER_ID = A.USER_ID AND RESV_NO = A.RESV_NO ) TOT_PRCE,
                    CONCAT(
                        DATE_FORMAT( B.CHCK_IN_DATE, '%Y-%m-%d' ),
                        ' ~ ',
                    DATE_FORMAT( DATE_ADD( MAX( B.CHCK_IN_DATE ), INTERVAL + 1 DAY ), '%Y-%m-%d' )) PERIOD,
                    (
                    SELECT
                        GROUP_CONCAT( CONCAT( SQ2.OPTN_NM, '||', SQ1.QTY, '||', SQ2.UNIT_NM ) SEPARATOR '^^' ) 
                    FROM
                        RESV_INFO_DTL SQ1
                        INNER JOIN ROOM_OPTN SQ2 ON SQ1.USER_ID = SQ2.USER_ID 
                        AND SQ1.OPTN_CODE = SQ2.OPTN_CODE 
                    WHERE
                        SQ1.USER_ID = A.USER_ID 
                        AND SQ1.RESV_NO = A.RESV_NO 
                    ) OPTION_LIST,
                    (
                    SELECT
                        GROUP_CONCAT( CONCAT( SQ2.SERV_NM, '||', SQ1.ETC_SEL_VALUE ) SEPARATOR '^^' ) 
                    FROM
                        RESV_INFO_ETC SQ1
                        INNER JOIN ETC_SEL_LIST SQ2 ON SQ1.USER_ID = SQ2.USER_ID 
                        AND SQ1.ETC_SEQ = SQ2.SEQ 
                    WHERE
                        SQ1.USER_ID = A.USER_ID 
                        AND SQ1.RESV_NO = A.RESV_NO 
                    ) ETC_SEL_LIST,
                    A.RESV_COMT 
                FROM
                    RESV_XXXX A
                    INNER JOIN RESV_INFO_ROOM B ON B.USER_ID = A.USER_ID 
                    AND B.RESV_NO = A.RESV_NO 
                    AND B.CHCK_IN_DATE >= DATE_FORMAT( NOW(), '%Y%m%d' )
                    INNER JOIN ROOM_XXX C ON C.USER_ID = A.USER_ID 
                    AND C.ROOM_CODE = B.ROOM_CODE 
                WHERE
                    A.USER_ID = ? 
                    AND A.RESV_NM = ? 
                    AND A.RESV_TEL = ? 
                    AND A.RESV_STAT IN ( '00', '01', '09' ) 
                GROUP BY
                    A.RESV_NO";

					echo $query;

        $result = $this->db->query($query, $param)->result_array();

        // 스위치문에 해당하는 업체는 객실명을 타입명으로 변경
        if(!empty($result)) {
            foreach ($result as $key=>$value) {
                switch ($userId) {
                    case 'brium':
                        $result[$key]['TYPE_NM'] = $result[$key]['ROOM_TYPE_NM'];
                        break;
                    case 'becoming':
                        $result[$key]['TYPE_NM'] = $result[$key]['ROOM_TYPE_NM'];
                        break;
                    case 'vieprivee':
                        $result[$key]['TYPE_NM'] = $result[$key]['ROOM_TYPE_NM'];
                        break;
                }

                unset($result[$key]['ROOM_TYPE_NM']);
            }
        }

        return $result;
    }
  
    //예약확인
    function insertRefund($userId){
        $param = array(
            $userId
        , $this->input->post('RESV_NO')
        , $this->input->post('RESV_DATE')
        , $this->input->post('REQ_DATE')
        , $this->input->post('DAYS')
        , $this->input->post('REFUND_MONEY')
        , $this->input->post('REFUND_COMS')
        , $this->input->post('REFUND_COMS_MONEY')
        , $this->input->post('AMOUNT')
        , $this->input->post('BANK_NM')
        , $this->input->post('BANK_NUM')
        , $this->input->post('BANK_OWN')
        , $this->input->post('RESERV_DTLS')
        , $this->input->post('IPADDR')
        );
        $query = "
				INSERT INTO REFUND_REQ
				(
				USER_ID
				,RESV_NO
				,RESV_DATE
				,REQ_DATE
				,DAYS
				,REFUND_MONEY
				,REFUND_COMS
				,REFUND_COMS_MONEY
				,AMOUNT
				,BANK_NM
				,BANK_NUM
				,BANK_OWN
				,RESERV_DTLS
				,IPADDR
				,IS_REFUND
				,REG_DATE) 
				VALUES 
				(
				?
				,?
				,?
				,?
				,?
				,?
				,?
				,?
				,?
				,?
				,?
				,?
				,?
				,?
				,'N'
				,NOW()
				)
								
				";

        $this->db->query($query, $param);
        return $result;
    }

    /**
     * 예약데이터 insert 전 예약 가능여부 한번더 체크
     * */
    function insertReserveCheck($RESV, $userId) {
        $param_data    = array();
        $arr_resv_date = array();

        for ($i = 0; $i < count($RESV['RESV_INFO_ROOMS']); $i++) {
            array_push($arr_resv_date, $RESV['RESV_INFO_ROOMS'][$i]['CHCK_IN_DATE']);
        }

        $USER_ID        	= $userId;
        $ROOM_CODE          = $RESV['ROOM_CODE'];

        $param_data["start_date"] = date("Ymd", strtotime($arr_resv_date[0]));
        $param_data["end_date"]   = date("Ymd", strtotime($arr_resv_date[count($arr_resv_date) - 1]));

        //예약가능여부 확인
        $param = array($USER_ID, $param_data["start_date"], $param_data["end_date"], $ROOM_CODE,);
        $resvRoomQuery = "
				SELECT
					a.RESV_NO,
					a.CHCK_IN_DATE,
					a.ROOM_CODE,
					b.TYPE_IDX
				FROM
					RESV_INFO_ROOM a
					JOIN ROOM_XXX b ON b.ROOM_CODE = a.ROOM_CODE
					AND b.USER_Id = a.USER_ID
					JOIN RESV_XXXX c ON c.RESV_NO = a.RESV_NO
					AND c.USER_ID = a.USER_ID
				WHERE
					a.USER_ID = ?
					AND a.CHCK_IN_DATE >= ?
					AND a.CHCK_IN_DATE <= ?
					AND b.USE_YN = 'N'
					AND c.RESV_STAT IN ( '00', '01', '09' )
					AND a.ROOM_CODE = ?
				GROUP BY a.ROOM_CODE
				";

        $resvRoom = $this->db->query($resvRoomQuery, $param)->result_array();

        // 예약 하려는 날짜에 존재하는 예약리스트 체크
        if (!empty($resvRoom)) {
            // 예약 불가능 상태로 변경 & 해당기간에 예약 할수 없기에 반복문 종료
            return "N";
        }

        $blockRoomQuery = "
				SELECT
					b.ROOM_CODE,
					b.TYPE_IDX,
					a.DSBL_DATE
				FROM
					ROOM_XXXX_DATE a
					JOIN ROOM_XXX b ON b.ROOM_CODE = a.ROOM_CODE
					AND b.USER_ID = a.USER_ID
				WHERE
					a.USER_ID = ?
					AND a.DSBL_DATE >= ?
					AND a.DSBL_DATE <= ?
					AND b.USE_YN = 'N'
					AND b.ROOM_CODE = ?
				GROUP BY b.ROOM_CODE
				";

        $blockRoom = $this->db->query($blockRoomQuery, $param)->result_array();

        // 예약 하려는 날짜에 존재하는 방막기 체크
        if (!empty($blockRoom)) {
            // 예약 불가능 상태로 변경 & 해당기간에 예약 할수 없기에 반복문 종료
            return "N";
        }

        return 'Y';
    }

    /**
     * 환불규정
     * */
    function selectCnclList($userId){
        $param = array($userId);
        $query = "
				SELECT CNCL_COMM
					 , PONT_YN
					 , USE_YN
				  FROM CNCL_COMM
				 WHERE USER_ID = ? 
				 AND (USE_YN is null OR USE_YN = 'N')
				";
        return $this->db->query($query, $param)->result_array();
    }

    /**
     * 결제내역
     * */
    function selectPayInfo($daoutrx){
        $param = array($daoutrx);
        $query = "
				SELECT * 
					FROM RESV_CARD_XXXX 
				WHERE DAOUTRX   = ?
				";
        return $this->db->query($query, $param)->result_array();
    }

    /**
     * 입실전필독사항
     * */
    function selectEntrList($userId){
        $param = array($userId);
        $query = "
				SELECT ENTR_COMM
				     , PONT_YN
				     , USE_YN
				  FROM ENTR_COMM
				 WHERE USER_ID = ?
				 AND (USE_YN is null OR USE_YN = 'N')
				";
        return $this->db->query($query, $param)->result_array();
    }

    /**
     * 입실전필독사항
     * */
    function selectEntrList_en($userId){
        $param = array($userId);
        $query = "
				SELECT ENTR_COMM
				     , PONT_YN
				     , USE_YN
				  FROM ENTR_COMM
				 WHERE USER_ID = CONCAT(?, '_en')
				";
        return $this->db->query($query, $param)->result_array();
    }

    function selectRoomList($userId){
        $param = array($userId);
        $query = "
				SELECT TYPE_NM
				     , ROOM_EXTN
				     , ADLT_BASE_PERS
				     , ADLT_MAX_PERS
				     , INTERIOR
				  FROM ROOM_XXX A
				 WHERE USER_ID = ?
				";
        return $this->db->query($query, $param)->result_array();
    }



    function selectRoomPrice($userId){
        $param = array($userId);
        $query = "
				SELECT A.ROOM_CODE 
				     , A.TYPE_NM
				     , A.ROOM_EXTN
				     , A.ADLT_BASE_PERS
				     , A.ADLT_MAX_PERS
				     , B.PROD_CODE
				     , (SELECT PROD_NM
				          FROM PROD_CODE SQ
				         WHERE SQ.PROD_CODE = B.PROD_CODE) PROD_NM
				     , FORMAT(B.WEEK_PRCE, 0) WEEK_PRCE
				     , FORMAT(B.FRD_PRCE, 0) FRD_PRCE
				     , FORMAT(B.SAT_PRCE, 0) SAT_PRCE
				     , FORMAT(B.SUN_PRCE, 0) SUN_PRCE
				  FROM ROOM_XXX A
				 INNER JOIN ROOM_PRICE_INFO B
				    ON A.USER_ID = B.USER_ID
				   AND A.ROOM_CODE = B.ROOM_CODE
				 WHERE A.USER_ID = ?
				 ORDER BY A.ROOM_CODE, B.PROD_CODE
				";
        return $this->db->query($query, $param)->result_array();
    }

    function selectAddSerivceUseYn($userId){
        $param = array($userId);
        $query = "
				SELECT IFNULL(USE_YN, 'N') USE_YN
				  FROM ADMIN_SERVICE  
				 WHERE USER_ID = ?
				   AND SERVICE_TYPE = '01'
				";
        $result = $this->db->query($query, $param)->result_array();
        if(count($result) > 0){
            return $result[0]['USE_YN'];
        }
        return 'N';
    }

    //적립금 적용요일조회
    function selectEmoneyApplyWdays($userId){
        $param = array($userId);
        $query = "
				SELECT WDAYS
				  FROM ACCU_CONF
				 WHERE USER_ID = ?
				";
        $result = $this->db->query($query, $param)->result_array();
        if(count($result) > 0){
            return $result[0]['WDAYS'];
        }
        return null;
    }

    //적립금설정조회
    function selectEmoneyConf($userId){
        $param = array($userId);
        $query = "
				SELECT ACCU_TYPE
				     , ACCU_AMT
				     , RCMM_ACCU_AMT
				     , USE_MAX_EMONEY
				     , USE_MAX_EMONEY_DIV
				     , WDAYS
				  FROM ACCU_CONF
				 WHERE USER_ID = ?
				";
        $result = $this->db->query($query, $param)->result_array();
        if(count($result) > 0){
            return $result[0];
        }
        return null;
    }

    //적립금조회
    function selectEmoney($userId, $telNo, $resvNm){
        $param = array($userId, $resvNm, $telNo);
        $query = "
				SELECT FORMAT(EMONEY, 0) EMONEY
				  FROM EMONEY A
				     , (SELECT RESV_TEL
				             , USER_ID
				          FROM RESV_XXXX A
				         WHERE USER_ID = ?
				           AND RESV_NM = ?
				           AND RESV_TEL = ?) B
				 WHERE A.USER_ID = B.USER_ID
				   AND A.TEL_NO = B.RESV_TEL
				";
        $result = $this->db->query($query, $param)->result_array();
        if(count($result) > 0){
            return $result[0]['EMONEY'];
        }
        return 0;
    }

    //예약자 예약기간
    function selectUserPeriod($userId, $resvNo){
        $param = array($userId, $resvNo);
        $query = "
				SELECT CHCK_IN_DATE
				  FROM RESV_INFO_ROOM
				 WHERE USER_ID = ? 
				   AND RESV_NO = ?
				";
        return $this->db->query($query, $param)->result_array();
    }

    //사용자 예약시 이력추적을위한 데이터 저장
    function insertUserReserveHistory($userId){
        $param = array(
            $userId
        , $userId
        , $this->input->post('roomCode')
        , $this->input->post('resvDate')
        , $this->input->post('amt')
        , $this->input->ip_address()
        , $this->input->post('resv_content')
        );
        $query = "
				INSERT INTO USER_RESERVE_HISTORY
				(
				USER_ID
				, SEQ
				, ROOM_CODE
				, RESV_DATE
				, AMT
				, REG_IP
				, REG_DATE
				, RESV_CONTENT) 
				VALUES 
				(
				?
				, (SELECT IFNULL(MAX(SEQ), 0) + 1 FROM USER_RESERVE_HISTORY SQ WHERE SQ.USER_ID = ?)
				, ?
				, ?
				, ?
				, ?
				, NOW()
				, ?
				)
								
				";
        $this->db->query($query, $param);
    }

    //객실정보
    function selectRoomList2($userId){
        $param = array($userId);
        $query = "
				SELECT TYPE_NM
				     , TYPE_NM_EN
				     , ROOM_EXTN
				     , ROOM_TYPE
				     , FLHT_ROOM_CNT
				     , BED_ROOM_CNT
				     , TOLT_CNT
				     , ADLT_BASE_PERS
				     , ADLT_MAX_PERS
				     , KIDS_MAX_PERS
				     , INFT_MAX_PERS
				     , ADLT_EXCS_PRCE
				     , KIDS_EXCS_PRCE
				     , INFT_EXCS_PRCE
				     , INFT_INCL_YN
				     , TYPE
				     , INTERIOR
				     , ETC_DETL
				     , KIDS_USE_YN
				     , INFT_USE_YN
				     , R.SORT_NO
				     , R.BED_INFO
				     , T.TYPE_NAME
				     , R.ASSIST_CONTENT
				     , R.ASSIST_CONTENT_YN  
				     , T.TYPE_DESC
				     , T.TYPE_CONTENT
					 , T.EVENT_TYPE_CONTENT
					 , I.PATH
					 , USE_YN
				 FROM ROOM_XXX R 
				 LEFT JOIN TYPE_INFO T ON R.TYPE_IDX = T.IDX
				 LEFT JOIN ROOM_IMG I  ON R.USER_ID = I.USER_ID AND R.ROOM_CODE = I.ROOM_CODE
				 WHERE R.USER_ID = ?
				 ORDER BY R.SORT_NO ASC
				";
        return $this->db->query($query, $param)->result_array();
    }

    //업체정보
    function selectPensionInfo($userId){
        $param = array($userId);
        $query = "
			  SELECT BUSI_NM
			       , USER_TEL1
			       , USER_TEL2
			       , BUSI_NO
			       , BUSI_PRE_NM
			       , COMM_SALE_NO
			       , USER_EMAIL
			       , USER_ADDR
			       , NEW_USER_ADDR  
			       , (SELECT USER_ACCO FROM ADMIN_ACCO_INFO SQ WHERE SQ.USER_ID = A.USER_ID AND SQ.MAIN_DISP_YN = 'Y') USER_ACCO
			       , AUTO_CACL_USE_YN
			       , DEPO_WAIT_TIME
			       , RESV_ABLE_DATE
			       , RESV_INFO_TEXT
			       , CHECK_IN
			       , CHECK_OUT
			       , CHECKIN_UNTIL
			       , NUM_PER_DIV_ADT
			       , NUM_PER_DIV_KID
			       , NUM_PER_DIV_INF
                   , wire_transfer_YN
                   , wire_transfer_unit
                   , wire_transfer_money
			    FROM ADMIN_XXXX A
			   WHERE USER_ID = ?
				";
        $result = $this->db->query($query, $param)->result_array();
        return $result[0];
    }


    //이벤트목록
    function selectEventList($userId){
        $param = array($userId);
        $query = "
				  SELECT EVENT_NM
				       , EVENT_PRCE
				       , CONCAT(DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d'), ' ~ ', DATE_FORMAT(END_DATE, '%Y-%m-%d')) PERIOD
				       , CASE EVENT_OPTN WHEN '0' THEN '정률할인'
				                         WHEN '1' THEN '금액할인'
										 WHEN '2' THEN '고정가판매'
						  END EVENT_OPTN
				    FROM EVENT_PROD
				   WHERE USER_ID = ?
				";
        return $this->db->query($query, $param)->result_array();
    }

    //연박
    function selectConsList($userId){
        $param = array($userId);
        $query = "
				  SELECT SUBJECT EVENT_NM
				        , CONCAT(DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d'), ' ~ ', DATE_FORMAT(END_DATE, '%Y-%m-%d')) PERIOD
				        , CONS_DAYS
				        , AMT
				        , CASE OPT1 WHEN '1' THEN '%'
				                    WHEN '2' THEN '원'
							END OPT
				     FROM CONS_SALE_PROD
			 		WHERE USER_ID = ?
				";
        return $this->db->query($query, $param)->result_array();
    }

     * 펜션 카드사용유무및 펜션명
     * */
    function selectAdminCardInfo($userId){
        $param = array($userId);
        $query = "
				SELECT IFNULL(CARD_USE_YN, 'N') CARD_USE_YN
				     , CARD_KEY
				     , BUSI_NM
					 , ROOM_KEY_YN
				  FROM ADMIN_XXXX
				 WHERE USER_ID = ?
				";
        return $this->db->query($query, $param)->result_array();
    }

    /*
     * 펜션 객실별 카드사용시 카드번호
     * */
    function selectRoomCardInfo($userId, $ROOM_CODE){
        $param = array($userId, $ROOM_CODE);
        $query = "
				SELECT CARD_KEY FROM ROOM_XXX
				 WHERE USER_ID = ?
				   AND    ROOM_CODE = ?
				";
        return $this->db->query($query, $param)->result_array();
    }

    /*
     * 예약정보 요약 업데이트
     * */
    function update_kium_INFOM($RESV){
        $param = array($RESV['RESV_INFOM'],$RESV['userId'], $RESV['ORDERNO']);
        $query = " update RESV_CARD_XXXX set RESV_INFOM = ? WHERE USER_ID = ? AND RESV_NO = ?";
        return $this->db->query($query, $param);
    }
	
    function return_check($USER_ID,$RESV_INFO){

        $query = "SELECT * FROM RESV_CARD_XXXX WHERE USER_ID = ? AND RESV_NO =? AND STATE = '결제성공'";
        $param = array(
            $USER_ID,
            $RESV_INFO
        );
        return $this->db->query($query,$param)->result_array();
    }
	
    function new_return_check($USER_ID,$RESV_NO){
      $query = "SELECT STATE FROM RESV_CARD_XXXX 
				WHERE USER_ID = '".$USER_ID."' 
				AND RESV_NO = '".$RESV_NO."'
				";
        return $this->db->query($query)->result_array();
    }

	function createCardKiwonPay($_L)
	{
		$query		=	"INSERT INTO RESV_CARD_XXXX(
									   USER_ID
									  ,RESV_NO
									  ,CPID
									  ,AMOUNT
									  ,PRODUCTNAME
									  ,USERNAME
									  ,REG_DATE
									  ,CPNAME
									  ,CPTELNO
									  ,CPTELNO2
									  ,RESV_COMT
									  ,STATE
									  ,OPTION
									  ,AGENT
									  ,RESV
									)
							VALUES(									
										'".$_L['USER_ID']."',
										'".$_L['RESV_NO']."',
										'".$_L['CPID']."',
										'".$_L['AMOUNT']."',
										'".$_L['PRODUCTNAME']."',
										'".$_L['CPNAME']."',
										NOW(),
										'".$_L['CPNAME']."',
										'".$_L['CPTELNO']."',
										'".$_L['CPTELNO2']."',
										'".$_L['RESV_COMT']."',
										'결제대기',
										'".$_L['OPTION']."',
										'".$_L['AGENT']."',
										'".$_L['RESV']."'
									)
						";
		return $this->db->query($query);
	}
	function delCardKiwonPay($_L)
	{
		$query  = "UPDATE RESV_CARD_XXXX SET									
										STATE		=	'결제취소'
									WHERE
									RESV_NO		=	'".$_L['RESV_NO']."'
									AND
									USER_ID		=   '".$_L['USER_ID']."'
									AND
									STATE = '결제대기'
							";
		return $this->db->query($query);
	}
	function delResvInfoEtc($_L)
	{
		$query  = "DELETE FROM RESV_INFO_ETC 
									WHERE
									RESV_NO		=	'".$_L['RESV_NO']."'
									AND
									USER_ID		=   '".$_L['USER_ID']."'
							";
		return $this->db->query($query);
	}
	function dupliCardKiwonPay($USER_ID, $RESV_NO,$get_data)
	{
		$query  = "UPDATE RESV_CARD_XXXX SET
										PAYMETHOD = '".$get_data['PAYMETHOD']."',
										DAOUTRX = '".$get_data['DAOUTRX']."',
										SETTDATE = '".$get_data['SETTDATE']."',
										CARDCODE = '".$get_data['CARDCODE']."',
										CARDNAME = '".iconv('EUC-KR', 'UTF-8', $get_data['CARDNAME'])."',
										PRODUCTCODE = '".$get_data['PRODUCTCODE']."',
										RESERVEDINDEX3 = '0',
										AUTHNO = '".$get_data['AUTHNO']."',
										STATE		=	'중복예약'
									WHERE
									RESV_NO		=	'".$RESV_NO."'
									AND
									USER_ID		=   '".$USER_ID."'
									AND
									STATE = '결제대기'
							";
		return $this->db->query($query);
	}

	function updateCardKiwonPay($userId){
        foreach($_GET as $k => $v){
            log_message('debug',$k."=".$v." iconv_get_encoding = ".iconv_get_encoding($v)." ".convertEncoding($v));
        }
        $query = " update RESV_CARD_XXXX set 
					PAYMETHOD = '".$this->input->GET("PAYMETHOD")."',
					CPID = '".$this->input->GET("CPID")."',
					DAOUTRX = '".$this->input->GET("DAOUTRX")."',
					SETTDATE = '".$this->input->GET("SETTDATE")."',
					CARDCODE = '".$this->input->GET("CARDCODE")."',
					CARDNAME = '".iconv('EUC-KR', 'UTF-8', $this->input->GET("CARDNAME"))."',
					PRODUCTCODE = '".$this->input->GET("PRODUCTCODE")."',
					RESERVEDINDEX1 = '".$this->input->GET("RESERVEDINDEX1")."',
					RESERVEDINDEX2 = '".$this->input->GET("RESERVEDINDEX2")."',
					RESERVEDINDEX3 = '0',
					AUTHNO = '".$this->input->GET("AUTHNO")."',
					STATE = '결제성공'
					WHERE USER_ID = '".$userId."' AND RESV_NO = '".$this->input->GET("PRODUCTCODE")."'";


		return $this->db->query($query);
    }

	function insertCardKiwonPay($userId){
        foreach($_GET as $k => $v){
            log_message('debug',$k."=".$v." iconv_get_encoding = ".iconv_get_encoding($v)." ".convertEncoding($v));
        }

        log_message('error',"카드결제저장");

        $param = array(
		    $userId,
            $this->input->GET("PRODUCTCODE"),
            $this->input->GET("PAYMETHOD"),
            $this->input->GET("CPID"),
            $this->input->GET("DAOUTRX"),
            $this->input->GET("AMOUNT"),
            iconv('EUC-KR', 'UTF-8', $this->input->GET("PRODUCTNAME")),
            $this->input->GET("SETTDATE"),
            $this->input->GET("CARDCODE"),
			iconv('EUC-KR', 'UTF-8', $this->input->GET("CARDNAME")),
            $this->input->GET("PRODUCTCODE"),
			iconv('EUC-KR', 'UTF-8', $this->input->GET("USERNAME")),
            $this->input->GET("RESERVEDINDEX1"),
            $this->input->GET("RESERVEDINDEX2"),
            $this->input->GET("RESERVEDINDEX3"), // 모듈구분
            $this->input->GET("AUTHNO"),
			iconv('EUC-KR', 'UTF-8', $this->input->GET("USERNAME")),
            $this->input->GET("CPTELNO"),
            ' '
        );

        log_message('error', implode("|", $param));

        $query = "
				INSERT INTO RESV_CARD_XXXX(
				   USER_ID
				  ,RESV_NO
				  ,PAYMETHOD
				  ,CPID
				  ,DAOUTRX
				  ,AMOUNT
				  ,PRODUCTNAME
				  ,SETTDATE
				  ,CARDCODE
				  ,CARDNAME
				  ,PRODUCTCODE
				  ,USERNAME
				  ,RESERVEDINDEX1
				  ,RESERVEDINDEX2
				  ,RESERVEDINDEX3
				  ,REG_DATE
				  ,AUTHNO
				  ,CPNAME
				  ,CPTELNO
				  ,RESV_INFOM
				) VALUES (
				   ?
				  ,?
				  ,?
				  ,?
				  ,?
				  ,?
				  ,?
				  ,?
				  ,?
				  ,?
				  ,?
				  ,?
				  ,?
				  ,?
				  ,?
				  ,NOW()
				  ,?
				  ,?
				  ,?
                  ,?
				)
			";

        return $this->db->query($query, $param);
    }


    function insertCardTest(){
        foreach($_REQUEST as $k => $v){
            log_message('debug',$k."=".$v." iconv_get_encoding = ".iconv_get_encoding($v)." ".convertEncoding($v));

        }

        log_message('error',"카드결제저장");

        $param = array(
            $this->input->POST("USERID"),
            $this->input->POST("ORDERNO"),
            $this->input->POST("PAYMETHOD"),
            $this->input->POST("CPID"),
            $this->input->POST("DAOUTRX"),
            $this->input->POST("AMOUNT"),
            $this->input->POST("PRODUCTNAME"),
            $this->input->POST("SETTDATE"),
            $this->input->POST("CARDCODE"),
            $this->input->POST("CARDNAME"),
            iconv('EUC-KR', 'UTF-8//TRANSLIT', $this->input->POST("PRODUCTCODE")),
            $this->input->POST("USERNAME"),
            iconv('EUC-KR', 'UTF-8//TRANSLIT', $this->input->POST("RESERVEDINDEX1")),
            iconv('EUC-KR', 'UTF-8//TRANSLIT', $this->input->POST("RESERVEDINDEX2")),
            iconv('EUC-KR', 'UTF-8//TRANSLIT', $this->input->POST("RESERVEDSTRING")),
            $this->input->POST("AUTHNO"),
            $this->input->POST("CPNAME"),
            $this->input->POST("CPTELNO"),
            $this->input->POST("RESV_INFOM"),
        );

        log_message('error', implode("|", $param));

        $query = "
				INSERT INTO RESV_CARD_XXXX(
				   USER_ID
				  ,RESV_NO
				  ,PAYMETHOD
				  ,CPID
				  ,DAOUTRX
				  ,AMOUNT
				  ,PRODUCTNAME
				  ,SETTDATE
				  ,CARDCODE
				  ,CARDNAME
				  ,PRODUCTCODE
				  ,USERNAME
				  ,RESERVEDINDEX1
				  ,RESERVEDINDEX2
				  ,RESERVEDINDEX3
				  ,REG_DATE
				  ,AUTHNO
				  ,CPNAME
				  ,CPTELNO
				  ,RESV_INFOM
				) VALUES (
				   ?
				  ,?
				  ,?
				  ,?
				  ,?
				  ,?
				  ,?
				  ,?
				  ,?
				  ,?
				  ,?
				  ,?
				  ,?
				  ,?
				  ,?
				  ,NOW()
				  ,?
				  ,?
				  ,?
				  ,?
				)
			";

        return $this->db->query($query, $param);
    }

    //예약진행시 예약진행중인 객실의 날짜를 저장후 예약을 일정시간 막는 용도로 사용
    function insertResvIng($userId){
        $this->db->trans_begin();
        $RESV = $this->input->post('RESV');


        $RESV_INFO_ROOMS = array_key_exists('RESV_INFO_ROOMS', $RESV) ? $RESV['RESV_INFO_ROOMS'] : array();

        foreach($RESV_INFO_ROOMS as $item){
            $param = array($userId
            , $RESV['ROOM_CODE']
            , $item['CHCK_IN_DATE']
            , $this->input->ip_address()
            );

            $query1 = "
					SELECT COUNT(*) CNT 
					  FROM XRESV_XING
					 WHERE USER_ID = ?
					   AND ROOM_CODE = ?
					   AND CHCK_IN_DATE = ?
					   AND USER_IP = ?
					";

            $result = $this->db->query($query1, $param)->result_array();

            if($result[0]['CNT'] <= 0){
                $query2 = "
						INSERT INTO XRESV_XING
						   SET USER_ID = ?
						     , ROOM_CODE = ?
						     , CHCK_IN_DATE = ?
						     , USER_IP = ?
						     , REG_DATE = NOW()
						";
                $this->db->query($query2, $param);
            }
        }

        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            return false;
        }
        else
        {
            $this->db->trans_commit();
            return true;
        }

    }
    //카드결제후 결제테이블에 저장이 되었는지 확인
    function selectCardPayComfirm($userId){
        $RESV = $this->input->post('RESV');
        $param = array(
            $userId
        , $RESV['RESV_NO']
        );

        $query = "
				SELECT CASE WHEN COUNT(*) > 0 THEN 'Y'
				            ELSE 'N'
				        END CARD_PAYED_YN
				 FROM RESV_CARD_XXXX
				WHERE USER_ID = ?
				  AND RESV_NO = ?
				";
        $result = $this->db->query($query, $param)->result_array();
        $cardPayedYn = $result[0]['CARD_PAYED_YN'];

        return $cardPayedYn;
    }

    //카드결제후 결제테이블에 저장이 되었는지 확인
    function selectCardPayComfirmKiwom($resvNo){
        $param = array(
			$resvNo
        );

        $query = "
				SELECT CASE WHEN COUNT(*) > 0 THEN 'Y'
				            ELSE 'N'
				        END CARD_PAYED_YN
				 FROM RESV_CARD_XXXX
				WHERE RESV_NO = ?
				";
        $result = $this->db->query($query, $param)->result_array();
        $cardPayedYn = $result[0]['CARD_PAYED_YN'];

        return $cardPayedYn;
    }

	 function check_resv($user_id,$resvNo){

        $query = "
				SELECT CASE WHEN COUNT(*) > 0 THEN 'Y'
				            ELSE 'N'
				        END CARD_PAYED_YN
				 FROM RESV_CARD_XXXX
				WHERE RESV_NO = '".$resvNo."'
				AND USER_ID = '".$user_id."'
				";
        $result = $this->db->query($query, $param)->result_array();
        $cardPayedYn = $result[0]['CARD_PAYED_YN'];

        return $cardPayedYn;
    }
	

	function getResvInfo($user_id,$resvNo){

        $query = "
				SELECT *
				 FROM RESV_CARD_XXXX
				WHERE RESV_NO = '".$resvNo."'
				AND USER_ID = '".$user_id."'
				";
        $result = $this->db->query($query)->result_array();

		return $result[0];
    }

    //카드결제후 결제테이블에 저장되어있는 데이터 호출
    function selectCardPayComfirmKiwomData($resvNo){
        $param = array(
			$resvNo
        );

        $query = "
				SELECT *
				 FROM RESV_CARD_XXXX
				WHERE RESV_NO = ?
				";
        $result = $this->db->query($query, $param)->result_array();
        return $result;
    }

    //카드결제진행중 테이블 10분지난 데이터 삭제
    function deleteAutoDeleteResvIng(){
        $param = array();
        $query = "
			  DELETE
				FROM XRESV_XING
			   WHERE DATE_FORMAT(NOW(), '%Y%m%d%H%i') - DATE_FORMAT(REG_DATE, '%Y%m%d%H%i') > 10
				";
        $this->db->query($query, $param);
    }

    function selectSpecial($userId){
        $param = array($userId);

        $query = "
                SELECT CONTENT,
					   CONTENT1,
					   CONTENT2,
					   CONTENT3,
					   CONTENT4,
                        TITLE_KR,
                        TITLE_EN,
						IMAGE_URL,
						IMAGE_COUNT,
                        ORDER_NUM
                FROM SPECIAL
                WHERE USER_ID = ? AND USE_YN = 'Y'
        ";

        return $this->db->query($query,$param)->result_array();

    }
    function selectFacility($userId){
        $param = array($userId);

        $query = "
                SELECT CONTENT,
					   CONTENT1,
					   CONTENT2,
					   CONTENT3,
					   CONTENT4,
                        TITLE_KR,
                        TITLE_EN,
                        ORDER_NUM,
						IMAGE_URL,
						IMAGE_COUNT
                FROM FACILITY
                WHERE USER_ID = ? AND USE_YN = 'Y'
				ORDER BY ORDER_NUM Asc
        ";

        return $this->db->query($query,$param)->result_array();

    }
    function selectService($userId){
        $param = array($userId);

        $query = "
                SELECT CONTENT,
					   CONTENT1,
					   CONTENT2,
					   CONTENT3,
					   CONTENT4,
                        TITLE_KR,
                        TITLE_EN,
                        ORDER_NUM,
						IMAGE_URL,
						IMAGE_COUNT
                FROM SERVICE
                WHERE USER_ID = ? AND USE_YN = 'Y'
        ";

        return $this->db->query($query,$param)->result_array();

    }

    function selectOtherManagement($userId){
        $param = array($userId);

        $query = "
                SELECT CONTENT,
					   CONTENT1,
					   CONTENT2,
					   CONTENT3,
					   CONTENT4,
                        TITLE_KR,
                        TITLE_EN,
                        ORDER_NUM
                FROM OTHER_MANAGEMENT
                WHERE USER_ID = ? AND USE_YN = 'Y'
        ";

        return $this->db->query($query,$param)->result_array();

    }

    function selectTour($userId){

        $param = array($userId);
        $query = "
	            SELECT CONTENT,
                        TITLE,
                        DISTANCE,
                        ORDER_NUM
                 FROM TOUR
                 WHERE USER_ID = ? AND  USE_YN = 'Y'
				 ORDER BY  ORDER_NUM ASC
	    ";

        return $this->db->query($query,$param)->result_array();

    }

    function selectBusiNum($userId){

        $param = Array($userId);
        $query = "SELECT *
                  FROM ADMIN_XXXX_VICE
	              WHERE USER_ID = ?";


        return $this->db->query($query,$param)->result_array();

    }


    function selectAdminTaxYn($userId){
        $param = Array($userId);
        $query = "
					SELECT TAX_YN
					FROM ADMIN_XXXX
					WHERE USER_ID = ?";
        $result = $this->db->query($query,$param)->result_array();
        return $result[0]['TAX_YN'];
    }

    function selectAdminTaxYnOPT($userId){
        $param = Array($userId);
        $query = "
					SELECT TAX_YN_OPT
					FROM ADMIN_XXXX
					WHERE USER_ID = ?";
        $result = $this->db->query($query,$param)->result_array();
        return $result[0]['TAX_YN_OPT'];
    }

    function update_block_content($block_content,
                                  $room_code,
                                  $userId,
                                  $dsbl_date){

        $param = array($block_content,
            $room_code,
            $userId,
            $dsbl_date);
        $query = "
	              UPDATE ROOM_XXXX_DATE
	              SET BLOCK_CONTENT = ?
	              WHERE ROOM_CODE = ?
	              AND USER_ID = ?
	              AND DSBL_DATE = ?;
	    ";

        $this->db->query($query,$param);
    }

    function selectNoticeList($userId){
        $param = array($userId);

        $query = "
                    SELECT * FROM NOTICE
                    WHERE USER_ID  = ?
                    AND USE_YN ='Y'
                    ORDER BY ORDER_NUM ASC;
                  ";

        return $this->db->query($query,$param)->result_array();
    }

    function sucheduler_resvDays($room_code, $user_id, $check_date){

        $param = array($check_date,
            $room_code,
            $user_id,
            $check_date);
        $query = " 
                    SELECT DATEDIFF(MIN(A.CHCK_IN_DATE),?) Dday
                    FROM RESV_INFO_ROOM A JOIN RESV_XXXX B
                    ON A.RESV_NO = B.RESV_NO AND A.USER_ID = B.USER_ID
                    WHERE A.ROOM_CODE = ?
				    AND A.USER_ID = ?
                    AND A.CHCK_IN_DATE >= ?
                    AND B.RESV_STAT IN('00','01','09') #RESV_STAT == 예약현황 00 == 입금대기 / 01 == 예약완료 / 02 == 예약취소
                    ORDER BY A.CHCK_IN_DATE ASC;
                    ";

        return $this->db->query($query,$param)->result_array();
    }

    function suchedulerBlockDays ($room_code, $user_id, $check_date){

        $param = array($check_date, $room_code, $user_id, $check_date);

        $query = " 
                    SELECT DATEDIFF(MIN(DSBL_DATE),?) Dday
                    FROM ROOM_XXXX_DATE
                    WHERE ROOM_CODE = ?
				    AND USER_ID = ?
                    AND DSBL_DATE >= ?
                    GROUP BY DSBL_DATE
                    ORDER BY DSBL_DATE ASC;
                    ";

        return $this->db->query($query,$param)->result_array();
    }

    /**
     * 타입목록 스킨6 방문자용
     */
    function selectTypeListV6($user_id){
        $param = array($user_id);
        $query = "
				SELECT IDX
				     , TYPE_NAME
				     , TYPE_DESC
				     , SORT_NO
				     , TYPE_IMG
				     , TYPE_CONTENT
				  FROM TYPE_INFO
				 WHERE USER_ID = ?
				 ORDER BY SORT_NO
				";
        return $this->db->query($query, $param)->result_array();
    }

    /**
     * 사용자예약시 달력 클릭전 저장전 체크인이 가능한지 조회
     * */
    function selectTypeRvAbleDate($data, $user_id){
        $query = "
				SELECT count(*) cnt, b.TYPE_IDX
				FROM XRESV_XING a
                JOIN ROOM_XXX b ON b.ROOM_CODE = a.ROOM_CODE AND b.USER_ID = a.USER_ID
				WHERE a.USER_ID = '".$user_id."' AND a.CHCK_IN_DATE >= '".$data["start_date"]."' AND a.CHCK_IN_DATE <= '".$data["end_date"]."' AND a.USER_IP != '".$this->input->ip_address()."'
				AND b.TYPE_IDX = ".$data["TYPE_IDX"]." 
                GROUP BY b.TYPE_IDX, a.CHCK_IN_DATE ";

        return $this->db->query($query)->result_array();
    }

    /**
     * 사용자예약 확인시 예약취소 버튼 노출 여부 확인
     * */
    function selectAdminInfoRefund($user_id){

        $param = array($user_id);

        $query = "
				SELECT REFU_DISP_YN
				FROM ADMIN_XXXX                
				WHERE USER_ID = ?				
                ";

        $result = $this->db->query($query,$param)->result_array();

        return $result[0]['REFU_DISP_YN'];

    }

    /**
     * 사용자예약 확인시 예약취소 버튼 노출 여부 확인
     * */
    function selectRoomType_en($user_id, $room_code){

        $param = array($user_id, $room_code);

        $query = "
				SELECT 
					ROOM_CODE,
					TYPE_NM,
					ROOM_TYPE,
					ADLT_BASE_PERS,
					ADLT_MAX_PERS,
					TYPE,
					ROOM_IMG 
				FROM 
					ROOM_XXX
				WHERE USER_ID = ?
				AND ROOM_CODE = ?	
                ";

        return $this->db->query($query,$param)->result_array()[0];

    }

    // 업체별 전체 타입 리스트
    function getTypeListService($userId) {
        $param = array($userId);
        $query = "
			SELECT
				IDX,
				TYPE_NAME,
				TYPE_DESC
			FROM
				TYPE_INFO 
			WHERE
				USER_ID = ?  
			ORDER BY
				SORT_NO";

        return $this->db->query($query, $param)->result_array();
    }
	
	function get_key_info($userId)
    {
        $query = "
			SELECT
				CARD_USE_YN
			FROM
				ADMIN_XXXX
			WHERE
				USER_ID = '".$userId."'";

        $result = $this->db->query($query, $param)->result_array();
		return $result[0]['CARD_USE_YN'];
    }
	
	function get_easy_key_info($userId)
    {
        $query = "
			SELECT
				EASY_CARD_USE_YN
			FROM
				ADMIN_XXXX
			WHERE
				USER_ID = '".$userId."'";

        $result = $this->db->query($query, $param)->result_array();
		return $result[0]['EASY_CARD_USE_YN'];
    }

} 
