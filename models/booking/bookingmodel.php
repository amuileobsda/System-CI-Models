<?php

class BookingModel extends CI_Model
{
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        $this->load->model('/booking/pricemodel');
        $this->load->model('/cardCancel/cardcancelmodel');
    }

    // 사용자 체크
    function getUserCheck($userId) {
        $param = array($userId);
        $query = "
			SELECT
				USER_ID
			FROM
				ADMIN_XXXX 
			WHERE
				USER_ID = ?";

        return empty($this->db->query($query, $param)->row_array()['USER_ID']) ? false : true;
    }

    // userId 와 typeIdx 로 해당업체에 유효한 타입인지 체크
    function getResvTypeCheck($userId, $typeIdx) {
        $param = array($userId, $typeIdx);
        $query = "
			SELECT
				USER_ID
			FROM
				TYPE_INFO 
			WHERE
				USER_ID = ?
				AND IDX = ?";

        return empty($this->db->query($query, $param)->row_array()['USER_ID']) ? false : true;
    }

    //사용자가 지정한 현재달력에서 ~ 달까지 달력 List에 뿌려줄것인지.
    function selectReserveRange($userId)
    {
        $param = array($userId);
        $query = "
			SELECT
				RESV_ABLE_DATE 
			FROM
				ADMIN_XXXX 
			WHERE
				USER_ID = ?";

        return $this->db->query($query, $param)->row_array()['RESV_ABLE_DATE'];
    }

    //관리자, 어드민이 설정한 공휴일 목록조회
    function selectHolydayList($userId)
    {
        //업소공휴일 전날도 체크해야된다면 USER_HOLI_DATE_LIST 테이블의 ystr_prce_yn을 Y로 설정 하면됨
        $param = array($userId, $userId);
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
				        FROM USER_HOLI_DATE_LIST
				       WHERE USER_ID = ?          
				      ) A
                  ORDER BY  BASE_HOLI_DATE DESC";

        return json_encode($this->db->query($query, $param)->result_array());
    }

    function selectNoticeList($userId)
    {
        $param = array($userId);
        $query = "
			SELECT
				* 
			FROM
				NOTICE 
			WHERE
				USER_ID = ? 
				AND USE_YN = 'Y' 
			ORDER BY
				ORDER_NUM ASC";

        return $this->db->query($query, $param)->result_array();
    }

    // 세금 노출 여부
    function selectTaxYn($userId)
    {
        $param = array($userId);
        $query = "
			SELECT
				TAX_YN 
			FROM
				ADMIN_XXXX 
			WHERE
				USER_ID = ?";

        return $this->db->query($query, $param)->row_array()['TAX_YN'];
    }

    // 개별 홈페이지
    function select_viewable($userId)
    {
        $param = array($userId);
        $query = "
			SELECT
                RESV_INFO_VIEWABLE
			FROM
				ADMIN_XXXX 
			WHERE
				USER_ID = ?";

        return $this->db->query($query, $param)->row_array()['RESV_INFO_VIEWABLE'];
    }

    // 세금 노출 여부 옵션
    function selectTaxYnOPT($userId)
    {
        $param = array($userId);
        $query = "
			SELECT
				TAX_YN_OPT
			FROM
				ADMIN_XXXX 
			WHERE
				USER_ID = ?";

        return $this->db->query($query, $param)->row_array()['TAX_YN_OPT'];
    }

    // 업체별 전체 타입 리스트
    function getTypeListService($userId)
    {
        $param = array($userId);
        $query = "
			SELECT
				IDX,
				TYPE_NAME 
			FROM
				TYPE_INFO 
			WHERE
				USER_ID = ?  
			ORDER BY
				SORT_NO";

        return json_encode($this->db->query($query, $param)->result_array());
    }

    function soldOutDayCheck($userId) {
        $result = [];
        $getDate = getDatesStartToLast($_POST['STARTDAY'], $_POST['LASTDAY']);
        array_push($getDate, str_replace('-', '', $_POST['LASTDAY']));

        // 객실 갯수
        $room_query = "SELECT COUNT(*) AS CNT FROM ROOM_XXX WHERE USER_ID = ? AND USE_YN = 'N'";
        $room_cnt = $this->db->query($room_query, array($userId))->row()->CNT;

        // 예약 리스트
        $param = array($userId, $getDate[0], $getDate[count($getDate) - 1]);
        $resv_query = "
				SELECT
					count(*) cnt,
					a.CHCK_IN_DATE
				FROM
					RESV_INFO_ROOM a
					JOIN ROOM_XXX b ON b.ROOM_CODE = a.ROOM_CODE 
					AND b.USER_Id = a.USER_ID
					JOIN RESV_XXXX c ON c.RESV_NO = a.RESV_NO 
					AND c.USER_ID = a.USER_ID 
				WHERE
					a.USER_ID = ? 
					AND a.CHCK_IN_DATE BETWEEN ? AND ? 
					AND b.USE_YN = 'N' 
					AND c.RESV_STAT IN ( '00', '01', '09' ) 
				GROUP BY
					a.CHCK_IN_DATE
                ORDER BY a.CHCK_IN_DATE";

        $result['RESV_LIST'] = $this->db->query($resv_query, $param)->result_array();

        $resv_list = [];
        foreach ($result['RESV_LIST'] as $item) {
            $resv_list[$item['CHCK_IN_DATE']] = $item['cnt'];
        }

        // 방막기된 타입 리스트
        $block_query = "
				SELECT
					count(*) cnt,
					a.DSBL_DATE 
				FROM
					ROOM_XXXX_DATE a
					JOIN ROOM_XXX b ON b.ROOM_CODE = a.ROOM_CODE 
					AND b.USER_ID = a.USER_ID 
				WHERE
					a.USER_ID = ? 
					AND a.DSBL_DATE BETWEEN ? AND ? 
					AND b.USE_YN = 'N' 
				GROUP BY
					a.DSBL_DATE
                ORDER BY a.DSBL_DATE";

        $result['BLOCK_LIST']  = $this->db->query($block_query, $param)->result_array();

        $block_list = [];
        foreach ($result['BLOCK_LIST'] as $item) {
            $block_list[$item['DSBL_DATE']] = $item['cnt'];
        }

        foreach ($getDate as $date) {
            $result['RESV_YN'][$date] = 0;

            if(isset($resv_list[$date])) {
                $result['RESV_YN'][$date] += $resv_list[$date];
            }

            if(isset($block_list[$date])) {
                $result['RESV_YN'][$date] += $block_list[$date];
            }

           $result['RESV_YN'][$date] = $room_cnt <= $result['RESV_YN'][$date] ? 'N' : 'Y';
			
		   //해당 일자별 최소가격 가져오기
		   $query = "SELECT
					MIN(CASE DAYOFWEEK('".$date."')
							WHEN 1 THEN rpi.SUN_PRCE
							WHEN 2 THEN rpi.WEEK_PRCE
							WHEN 3 THEN rpi.WEEK_PRCE
							WHEN 4 THEN rpi.WEEK_PRCE
							WHEN 5 THEN rpi.WEEK_PRCE
							WHEN 6 THEN rpi.FRD_PRCE
							WHEN 7 THEN rpi.SAT_PRCE
						END
						)AS min_price
					FROM
						ROOM_PRICE_INFO rpi
					WHERE rpi.USER_ID = '".$userId."'
					AND (rpi.SUN_PRCE > 0 or rpi.WEEK_PRCE > 0 or rpi.FRD_PRCE > 0 or rpi.SAT_PRCE > 0)
					AND rpi.USE_YN = 'Y';
					";

			$min_price[$date] = $this->db->query($query)->result_array();
			$result['RESV_YN']['MIN_PRICE'][$date] = $min_price[$date][0];
        }
	
		
		return json_encode($result['RESV_YN'], 1);
    }
    // 전체 객실 리스트를 가져온다. & 예약 가능 여부 체크 안함
    function selectRoomListDefault($userId)
    {
        $result = [];
        $param = array($userId);
        $query = "
                SELECT
                    A.TYPE_IDX,
                    A.TYPE_NM AS ROOM_NM,
                    B.TYPE_NAME,
                    A.ROOM_IMG,
                    B.TYPE_DESC,
                    B.TYPE_CONTENT,
                    B.EVENT_TYPE_NM_YN,
                    B.EVENT_TYPE_NAME,
                    B.EVENT_TYPE_CONTENT,
                    A.BED_INFO,
                    A.ADLT_BASE_PERS,
                    A.ADLT_MAX_PERS,
                    A.ETC_DETL,
                    A.ROOM_TYPE 
                FROM
                    ROOM_XXX A,
                    TYPE_INFO B 
                WHERE
                    A.USER_ID = ? 
                    AND A.USER_ID = B.USER_ID 
                    AND A.TYPE_IDX = B.IDX 
                    AND A.USE_YN = 'N'
                ORDER BY
                    A.SORT_NO";

        $result['LIST'] = $this->db->query($query, $param)->result_array();

        return $result;
    }

    // 전체 타입 리스트를 가져온다. & 예약 가능 여부 체크 안함
    function selectTypeListDefault($userId)
    {
        $result = [];
        $param = array($userId);
        $query = "
			SELECT
				A.TYPE_IDX,
				B.TYPE_NAME,
				B.TYPE_IMG,
				B.TYPE_DESC,
				B.TYPE_CONTENT,
				A.BED_INFO,
				A.ADLT_BASE_PERS,
				A.ADLT_MAX_PERS,
				A.ETC_DETL,
				A.ROOM_TYPE,
                B.EVENT_TYPE_NM_YN,
                B.EVENT_TYPE_NAME,
                B.EVENT_TYPE_CONTENT
			FROM
				ROOM_XXX A,
				TYPE_INFO B 
			WHERE
				A.USER_ID = ? 
				AND A.USER_ID = B.USER_ID 
				AND A.TYPE_IDX = B.IDX 
				AND A.USE_YN = 'N' 
			GROUP BY
				TYPE_IDX
			ORDER BY B.SORT_NO";

        $result['LIST'] = $this->db->query($query, $param)->result_array();

        return $result;
    }
    
    // 전체 타입 리스트를 가져온다. & 예약 가능 여부 체크 Type선택형
    function selectTypeListType($userId, $DATE)
    {
        $result = [];
        $param = array($userId);
        $query = "
			SELECT
				A.TYPE_IDX,
				B.TYPE_NAME,
				B.TYPE_IMG,
				B.TYPE_DESC,
				B.TYPE_CONTENT,
                B.EVENT_TYPE_NM_YN,
                B.EVENT_TYPE_NAME,
                B.EVENT_TYPE_CONTENT,
				A.BED_INFO,
				A.ADLT_BASE_PERS,
				A.ADLT_MAX_PERS,
				A.ETC_DETL,
				A.ROOM_TYPE 
			FROM
				ROOM_XXX A,
				TYPE_INFO B 
			WHERE
				A.USER_ID = ? 
				AND A.USER_ID = B.USER_ID 
				AND A.TYPE_IDX = B.IDX 
				AND A.USE_YN = 'N' 
			GROUP BY
				TYPE_IDX
			ORDER BY B.SORT_NO";


        $typeData = $this->db->query($query, $param)->result_array();
        // 예약 가능한 타입 체크
        $resvTypeIdx = [];
        $result['RESV_TYPE_CODE'] = [];
        foreach ($typeData as $key => $type) {
            if ($this->typeIsAbleDtCheck($userId, $DATE, $type['TYPE_IDX']) === "Y") {
                array_push($resvTypeIdx, $type['TYPE_IDX']);

                $roomCodeList = $this->selectResvRoomCode($userId, $DATE, $type['TYPE_IDX']);
                if (empty($roomCodeList)) {
                    // 해당 날짜로 연박이 불가능할때 객실 코드 찾기 (첫날 가능한 객실 하나만 찾기)
                    $typeData[$key]['ROOM_CODE'] = $this->searchCrossRoomCode($userId, $type['TYPE_IDX'], $DATE[0]);

                    // 해당 객실로 연박이 안될 경우 예약 저장시 날짜별로 룸코드를 찾아야 한다.
                    $result['RESV_TYPE_CODE'][$type['TYPE_IDX']] = 'SEARCH';
                } else {
                    // 해당 날짜로 연박이 가능한 객실
                    $typeData[$key]['ROOM_CODE'] = array_values($roomCodeList)[0];

                    // 해당 객실로 연박 가능할경우 예약 저장에서 룸코드를 찾지 않는다.
                    $result['RESV_TYPE_CODE'][$type['TYPE_IDX']] = 'PASS';
                }
            }
        }

        $result['LIST'] = $typeData;
        $result['RESV_TYPE'] = $resvTypeIdx;

        return $result;
    }

    // 전체 타입 리스트를 가져온다. & 예약 가능 여부 체크 Room선택형
    function selectTypeListRoom($userId, $DATE)
    {
        $result = [];
        $param = array($userId);
        $query = "
			SELECT
				A.TYPE_IDX,
				B.TYPE_NAME,
				B.TYPE_IMG,
				B.TYPE_DESC,
				B.TYPE_CONTENT,
                B.EVENT_TYPE_NM_YN,
                B.EVENT_TYPE_NAME,
                B.EVENT_TYPE_CONTENT,
				A.BED_INFO,
				A.ADLT_BASE_PERS,
				A.ADLT_MAX_PERS,
				A.ETC_DETL,
				A.ROOM_TYPE 
			FROM
				ROOM_XXX A,
				TYPE_INFO B 
			WHERE
				A.USER_ID = ? 
				AND A.USER_ID = B.USER_ID 
				AND A.TYPE_IDX = B.IDX 
				AND A.USE_YN = 'N' 
			GROUP BY
				TYPE_IDX
			ORDER BY B.SORT_NO";

        $typeData = $this->db->query($query, $param)->result_array();

        // 예약 가능한 타입 체크
        $resvTypeIdx = [];
        foreach ($typeData as $key => $type) {
            $roomCodeList = $this->selectResvRoomCode($userId, $DATE, $type['TYPE_IDX']);
            if (!empty($roomCodeList)) {
                array_push($resvTypeIdx, $type['TYPE_IDX']);
            }
        }

        $result['LIST'] = $typeData;
        $result['RESV_TYPE'] = $resvTypeIdx;

        return $result;
    }

    // 타입안에 객실 리스트를 가져온다.
    function getTypeRoomList($userId, $typeIdx)
    {
        $param = array($userId, $typeIdx);
        $query = "
			SELECT
				ROOM_CODE,
				ROOM_EXTN,
                ROOM_IMG,
				TYPE_NM AS ROOM_NM,
				TYPE_NM_EN AS ROOM_NM_EN,
				TYPE_IDX,
				( SELECT TYPE_NAME FROM TYPE_INFO WHERE USER_ID = A.USER_ID AND IDX = A.TYPE_IDX ) AS TYPE_NM,
				( SELECT TYPE_IMG FROM TYPE_INFO WHERE USER_ID = A.USER_ID AND IDX = A.TYPE_IDX ) AS TYPE_IMG,
				BED_INFO,
				ADLT_BASE_PERS,
				ADLT_MAX_PERS,
				ROOM_IMG,
				INTERIOR,
				ASSIST_CONTENT,
				ASSIST_CONTENT_YN,
				ETC_DETL,
				ROOM_TYPE
			FROM
				ROOM_XXX A 
			WHERE
				USER_ID = ?
				AND TYPE_IDX = ?
			    AND USE_YN = 'N'
			ORDER BY SORT_NO";

        return $this->db->query($query, $param)->result_array();
    }

    // 전체 객실 리스트를 가져온다.
    function getRLRoomList($userId)
    {
        $param = array($userId);
        $query = "
			SELECT
				ROOM_CODE,
				ROOM_EXTN,
                ROOM_IMG,
				TYPE_NM AS ROOM_NM,
				TYPE_NM_EN AS ROOM_NM_EN,
				TYPE_IDX,
				( SELECT TYPE_NAME FROM TYPE_INFO B WHERE B.USER_ID = USER_ID AND B.IDX = TYPE_IDX ) AS TYPE_NM,
				( SELECT TYPE_IMG FROM TYPE_INFO B WHERE B.USER_ID = USER_ID AND B.IDX =TYPE_IDX ) AS TYPE_IMG,
				BED_INFO,
				ADLT_BASE_PERS,
				ADLT_MAX_PERS,
				ROOM_IMG,
				INTERIOR,
				ASSIST_CONTENT,
				ASSIST_CONTENT_YN,
				ETC_DETL,
				ROOM_TYPE
			FROM
				ROOM_XXX
			WHERE
				USER_ID = ?
			    AND USE_YN = 'N'
			ORDER BY SORT_NO";

        return $this->db->query($query, $param)->result_array();
    }

    // 선택된 객실 정보를 가져온다.
    function selectRoomInfo($userId, $roomcode)
    {
        $param = array($userId, $roomcode);
        $query = "
			SELECT
				ROOM_CODE,
				ROOM_EXTN,
                ROOM_IMG,
				TYPE_NM AS ROOM_NM,
				TYPE_IDX,
				( SELECT TYPE_NAME FROM TYPE_INFO WHERE USER_ID = A.USER_ID AND IDX = A.TYPE_IDX ) AS TYPE_NM,
				( SELECT TYPE_IMG FROM TYPE_INFO WHERE USER_ID = A.USER_ID AND IDX = A.TYPE_IDX ) AS TYPE_IMG,
				(SELECT IFNULL(NUM_PER_DIV_ADT, '성인') FROM ADMIN_XXXX WHERE USER_ID = A.USER_ID) AS NUM_PER_DIV_ADT,
				(SELECT IFNULL(NUM_PER_DIV_KID, '아동') FROM ADMIN_XXXX WHERE USER_ID = A.USER_ID) AS NUM_PER_DIV_KID,
				(SELECT IFNULL(NUM_PER_DIV_INF, '영유아') FROM ADMIN_XXXX WHERE USER_ID = A.USER_ID) AS NUM_PER_DIV_INF,
				BED_INFO,
				ADLT_BASE_PERS,
				ADLT_MAX_PERS,
				KIDS_MAX_PERS,
				INFT_MAX_PERS,
				ADLT_EXCS_PRCE,
				KIDS_EXCS_PRCE,
				INFT_EXCS_PRCE,
				INFT_INCL_YN,
				KIDS_USE_YN,
				INFT_USE_YN,
				ROOM_IMG,
				INTERIOR,
				ASSIST_CONTENT,
				ASSIST_CONTENT_YN,
				ETC_DETL,
				ROOM_TYPE
			FROM
				ROOM_XXX A 
			WHERE
				USER_ID = ?
				AND ROOM_CODE = ?";


        return $this->db->query($query, $param)->row_array();
    }

    // 선택된 타입에 정보를 가져온다.
    function getResvTypInfo($userId, $typeIdx)
    {
        $param = array($userId, $userId, $typeIdx);
        $query = "
				SELECT
					a.TYPE_NAME,
					a.TYPE_IMG,
					a.TYPE_CONTENT,
                    a.EVENT_TYPE_NM_YN,
                    a.EVENT_TYPE_NAME,
                    a.EVENT_TYPE_CONTENT,
					a.IDX,
					a.TYPE_DESC,
					b.* 
				FROM
					TYPE_INFO a
					JOIN (
					SELECT
						count(*) cnt,
						ROOM_CODE,
						BED_INFO,
						ADLT_BASE_PERS,
						ADLT_MAX_PERS,
						TYPE_NM,
						ROOM_IMG,
						INTERIOR,
						ASSIST_CONTENT,
						ASSIST_CONTENT_YN,
						ETC_DETL,
						ROOM_TYPE,
						TYPE_IDX 
					FROM
						ROOM_XXX 
					WHERE
						USER_ID = ? 
						AND USE_YN = 'N' 
					GROUP BY
						TYPE_IDX 
					) b ON b.TYPE_IDX = a.IDX 
				WHERE
					a.USER_ID = ? 
					AND a.IDX = ? 
				GROUP BY
					a.IDX 
				ORDER BY
					a.SORT_NO ASC";

        return $this->db->query($query, $param)->row_array();
    }

    // 객실 타입으로 예약 가능 객실 찾기
    function selectResvRoomCode($userId, $DATE, $typeIdx)
    {
        // 예약 가능한 객실 리스트
        $room_param = array($userId, $typeIdx, $userId, $DATE[0], $DATE[count($DATE) - 1]);
        $resv_room_query = "
				SELECT
					ROOM_CODE 
				FROM
					ROOM_XXX 
				WHERE
					USER_ID = ? 
					AND USE_YN = 'N'
					AND TYPE_IDX = ?
					AND ROOM_CODE NOT IN (
						SELECT
							B.ROOM_CODE 
						FROM
							RESV_XXXX A,
							RESV_INFO_ROOM B 
						WHERE
							A.USER_ID = ? 
							AND B.USER_ID = A.USER_ID 
							AND B.CHCK_IN_DATE BETWEEN ? AND ? 
							AND A.RESV_NO = B.RESV_NO 
							AND A.RESV_STAT IN ( '00', '01', '09' ) 
						GROUP BY
							B.ROOM_CODE)";

        $res = $this->db->query($resv_room_query, $room_param)->result_array();

        $resv_room = [];
        foreach ($res as $roomcode) {
            array_push($resv_room, $roomcode['ROOM_CODE']);
        }

        // 방막기된 객실
        $block_room_query = "
					SELECT
						ROOM_CODE 
					FROM
						ROOM_XXX 
					WHERE
						USER_ID = ?
						AND USE_YN = 'N' 
						AND TYPE_IDX = ?
						AND ROOM_CODE IN (
						SELECT
							ROOM_CODE 
						FROM
							ROOM_XXXX_DATE 
						WHERE
							USER_ID = ? 
							AND DSBL_DATE BETWEEN ?
							AND ? 
					GROUP BY
						ROOM_CODE)";

        $res = $this->db->query($block_room_query, $room_param)->result_array();

        $block_room = [];
        foreach ($res as $roomcode) {
            array_push($block_room, $roomcode['ROOM_CODE']);
        }

        // 예약 가능 객실 에서 방막기된 방을 제외하고 남은 실제 예약 가능한 객실 코드
        return array_diff($resv_room, $block_room);
    }

    // 전체 객실중 예약 가능 객실 찾기
    function selectRLResvRoomCode($userId, $DATE)
    {
        // 예약 가능한 객실 리스트
        $room_param = array($userId, $userId, $DATE[0], $DATE[count($DATE) - 1]);
        $resv_room_query = "
				SELECT
					ROOM_CODE 
				FROM
					ROOM_XXX 
				WHERE
					USER_ID = ? 
					AND USE_YN = 'N'
					AND ROOM_CODE NOT IN (
						SELECT
							B.ROOM_CODE 
						FROM
							RESV_XXXX A,
							RESV_INFO_ROOM B 
						WHERE
							A.USER_ID = ? 
							AND B.USER_ID = A.USER_ID 
							AND B.CHCK_IN_DATE BETWEEN ? AND ? 
							AND A.RESV_NO = B.RESV_NO 
							AND A.RESV_STAT IN ( '00', '01', '09' ) 
						GROUP BY
							B.ROOM_CODE)";

        $res = $this->db->query($resv_room_query, $room_param)->result_array();

        $resv_room = [];
        foreach ($res as $roomcode) {
            array_push($resv_room, $roomcode['ROOM_CODE']);
        }

        // 방막기된 객실
        $block_room_query = "
					SELECT
						ROOM_CODE 
					FROM
						ROOM_XXX 
					WHERE
						USER_ID = ?
						AND USE_YN = 'N'
						AND ROOM_CODE IN (
						SELECT
							ROOM_CODE 
						FROM
							ROOM_XXXX_DATE 
						WHERE
							USER_ID = ? 
							AND DSBL_DATE BETWEEN ?
							AND ? 
					GROUP BY
						ROOM_CODE)";

        $res = $this->db->query($block_room_query, $room_param)->result_array();

        $block_room = [];
        foreach ($res as $roomcode) {
            array_push($block_room, $roomcode['ROOM_CODE']);
        }

        // 예약 가능 객실 에서 방막기된 방을 제외하고 남은 실제 예약 가능한 객실 코드
        return array_diff($resv_room, $block_room);
    }

    // 선택된 타입안에 있는 객실 리스트 중 예약 가능 여부와 가격 정보 가져오기
    function getRoomListDetail($userId, $DATE, $typeIdx)
    {
        $roomCodeList = $this->selectResvRoomCode($userId, $DATE['ARR_DATE'], $typeIdx);

        // 예약 가능한 객실의 가격 정보를 담을 배열
        $result = [];

        $this->load->model('booking/pricemodel');

        // 공휴일 설정값
        $holydayConf = $this->pricemodel->getHolydayPriceConf($userId);

        // 공휴일 리스트
        $holyday_info = $this->pricemodel->getHolydayList($userId);

        // 성수기 기간
        $prod_info = $this->pricemodel->getProdInfo($userId);

        foreach ($roomCodeList as $code) {
            // 가격 정보
            $priceInfo = $this->pricemodel->getPriceInfo($userId, $code);
            $result[$code] = $this->pricemodel->getpriceCalculation($priceInfo, $DATE, $userId, $code, $holydayConf, $holyday_info, $prod_info);
        }

        return $result;
    }

    // 전체 객실 리스트 중 예약 가능 여부와 가격 정보 가져오기
    function getRLRoomListDetail($userId, $DATE)
    {
        $roomCodeList = $this->selectRLResvRoomCode($userId, $DATE['ARR_DATE']);

        // 예약 가능한 객실의 가격 정보를 담을 배열
        $result = [];

        $this->load->model('booking/pricemodel');

        // 공휴일 설정값
        $holydayConf = $this->pricemodel->getHolydayPriceConf($userId);

        // 공휴일 리스트
        $holyday_info = $this->pricemodel->getHolydayList($userId);

        // 성수기 기간
        $prod_info = $this->pricemodel->getProdInfo($userId);

        foreach ($roomCodeList as $code) {
            // 가격 정보
            $priceInfo = $this->pricemodel->getPriceInfo($userId, $code);
            $result[$code] = $this->pricemodel->getpriceCalculation($priceInfo, $DATE, $userId, $code, $holydayConf, $holyday_info, $prod_info);
        }

        return $result;
    }

    // 타입별 객실 가격 정보 가져오기
    function getTypePrice($userId, $DATE, $arrTypeIdx)
    {
        $result = [];

        $this->load->model('booking/pricemodel');

        // 공휴일 설정값
        $holydayConf = $this->pricemodel->getHolydayPriceConf($userId);

        // 공휴일 리스트
        $holyday_info = $this->pricemodel->getHolydayList($userId);

        // 성수기 기간
        $prod_info = $this->pricemodel->getProdInfo($userId);

        foreach ($arrTypeIdx as $idx) {
            // 타입안에 객실 최소 가격 정보
            $priceInfo = $this->pricemodel->getTypePriceInfo($userId, $idx);
            $result[$idx] = $this->pricemodel->getpriceCalculation($priceInfo, $DATE, $userId, $priceInfo[0]['ROOM_CODE'], $holydayConf, $holyday_info, $prod_info);
        }

        return $result;
    }

    // 타입별 객실 최소 가격 정보 가져오기
    function getTypeMinPrice($userId, $DATE, $arrTypeIdx)
    {
        $result = [];
        $this->load->model('booking/pricemodel');

        // 공휴일 설정값
        $holydayConf = $this->pricemodel->getHolydayPriceConf($userId);

        // 공휴일 리스트
        $holyday_info = $this->pricemodel->getHolydayList($userId);

        // 성수기 기간
        $prod_info = $this->pricemodel->getProdInfo($userId);

        foreach ($arrTypeIdx as $idx) {
            $param = array($userId, $idx);
            $query = "
				SELECT
					ROOM_CODE 
				FROM
					ROOM_XXX 
				WHERE USER_ID = ? 
					AND TYPE_IDX = ? ";

            $arrRoomCode = $this->db->query($query, $param)->result_array();

            foreach ($arrRoomCode as $item) {
                // 타입안에 객실 최소 가격 정보
                $priceInfo = $this->pricemodel->getTypeMinPriceInfo($userId, $idx);

                // 해당 날짜에 예약된 객실이 있는지 체크
                $param = array($userId, $DATE['BEGIN_DATE'], $DATE['END_DATE'], $item['ROOM_CODE']);
                $resv_room_query = "
				SELECT
					B.ROOM_CODE 
				FROM
					RESV_XXXX A,
					RESV_INFO_ROOM B 
				WHERE
					A.USER_ID = ? 
					AND B.USER_ID = A.USER_ID 
					AND B.CHCK_IN_DATE BETWEEN ? AND ? 
					AND A.RESV_NO = B.RESV_NO 
					AND B.ROOM_CODE = ?
					AND A.RESV_STAT IN ( '00', '01', '09' )";

                $res = $this->db->query($resv_room_query, $param)->result_array();

                if (empty($res)) {
                    // 해당 날짜에 방막기된 객실이 있는지 체크
                    $block_room_query = "
                        SELECT
                            ROOM_CODE 
                        FROM
                            ROOM_XXXX_DATE 
                        WHERE
                            USER_ID = ? 
                            AND DSBL_DATE BETWEEN ? AND ?
                            AND ROOM_CODE = ?";

                    $res = $this->db->query($block_room_query, $param)->result_array();

                    if (empty($res)) {
                        $minPrice = $this->pricemodel->getpriceCalculation($priceInfo, $DATE, $userId, $item['ROOM_CODE'], $holydayConf, $holyday_info, $prod_info);
                        if(empty($result[$idx])) {
                            $result[$idx] = $minPrice;
                        }else {
                            if($result[$idx]['TOTAL_PRICE'] > $minPrice['TOTAL_PRICE']) {
                                $result[$idx] = $minPrice;
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    // 선택된 객실 옵션 정보
    function getSelectRoomOptInfo($userId, $roomcode)
    {
        $param = array($userId, $roomcode);
        $query = " 
			SELECT
				a.OPTN_NM,
				a.WEEK_PRCE,
				a.FRD_PRCE,
				a.SAT_PRCE,
				a.SUN_PRCE,
				a.BASE_QTY,
				a.MAX_QTY,
				a.UNIT_NM,
				a.USE_DAILY_PRICE_YN,
				a.OPTN_CODE,
				a.OPTN_DETL_COMT,
                a.OPTN_IMG
			FROM
				ROOM_OPTN a
				JOIN ROOM_OPTN_DTL b ON b.OPTN_CODE = a.OPTN_CODE 
				AND b.USER_ID = a.USER_ID 
			WHERE
				a.USER_ID = ? 
				AND a.USE_YN = 'Y' 
				AND ( a.VIEW_YN != 'Y' OR a.VIEW_YN IS NULL ) 
				AND b.ROOM_CODE = ? 
			ORDER BY
				a.SORT_NO ASC";

        return $this->db->query($query, $param)->result_array();
    }

    // 룸코드로 예약 가능한지 체크 (객실 선택형)
    function roomIsAbleDtCheck($userId, $arrDate, $roomcode)
    {
        $BEGIN_DATE = date("Ymd", strtotime($arrDate[0]));
        $END_DATE = date("Ymd", strtotime($arrDate[count($arrDate) - 1]));

        // 해당 날짜에 예약된 객실이 있는지 체크
        $param = array($userId, $BEGIN_DATE, $END_DATE, $roomcode);
        $resv_room_query = "
				SELECT
					B.ROOM_CODE 
				FROM
					RESV_XXXX A,
					RESV_INFO_ROOM B 
				WHERE
					A.USER_ID = ? 
					AND B.USER_ID = A.USER_ID 
					AND B.CHCK_IN_DATE BETWEEN ? AND ? 
					AND A.RESV_NO = B.RESV_NO 
					AND B.ROOM_CODE = ?
					AND A.RESV_STAT IN ( '00', '01', '09' )";

        $res = $this->db->query($resv_room_query, $param)->result_array();

        if (empty($res)) {
            // 해당 날짜에 방막기된 객실이 있는지 체크
            $block_room_query = "
				SELECT
					ROOM_CODE 
				FROM
					ROOM_XXXX_DATE 
				WHERE
					USER_ID = ? 
					AND DSBL_DATE BETWEEN ? AND ?
					AND ROOM_CODE = ?";

            $res = $this->db->query($block_room_query, $param)->result_array();

            if (empty($res)) {
                return "Y";
            } else {
                return "N";
            }
        } else {
            return "N";
        }
    }

    // 타입으로 예약 가능한지 체크 (타입선택형)
    function typeIsAbleDtCheck($userId, $arrDate, $type_idx)
    {
        //타입 정보
        $type_info = $this->getResvTypInfo($userId, $type_idx);

        // 예약된 타입 리스트
        $type_param = array($userId, $arrDate[0], $arrDate[count($arrDate) - 1], $type_idx);
        $resv_type_query = "
				SELECT
					count(*) cnt,
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
					AND a.CHCK_IN_DATE BETWEEN ? AND ? 
					AND b.USE_YN = 'N' 
					AND c.RESV_STAT IN ( '00', '01', '09' ) 
					AND b.TYPE_IDX = ? 
				GROUP BY
					b.TYPE_IDX,
					a.CHCK_IN_DATE";

        $type_resv_list = $this->db->query($resv_type_query, $type_param)->result_array();

        // 방막기된 타입 리스트
        $block_type_query = "
				SELECT
					count(*) cnt,
					b.TYPE_IDX,
					a.DSBL_DATE 
				FROM
					ROOM_XXXX_DATE a
					JOIN ROOM_XXX b ON b.ROOM_CODE = a.ROOM_CODE 
					AND b.USER_ID = a.USER_ID 
				WHERE
					a.USER_ID = ? 
					AND a.DSBL_DATE BETWEEN ? AND ? 
					AND b.USE_YN = 'N' 
					AND b.TYPE_IDX = ? 
				GROUP BY
					b.TYPE_IDX,
					a.DSBL_DATE";

        $type_block_list = $this->db->query($block_type_query, $type_param)->result_array();

        $type_resv_cnt = count($type_resv_list);
        for ($j = 0; $j < $type_resv_cnt; $j++) {
            $type_info["CHCK_DATE"][$type_resv_list[$j]["CHCK_IN_DATE"]] += $type_resv_list[$j]["cnt"];
        }

        $type_block_cnt = count($type_block_list);
        for ($j = 0; $j < $type_block_cnt; $j++) {
            $type_info["CHCK_DATE"][$type_block_list[$j]["DSBL_DATE"]] += $type_block_list[$j]["cnt"];
        }

        //예약 불가능한 방 예외처리 -  기존 룸 개수 <= 현재 예약 된룸 + 방막기된룸 일경우 방막음
        foreach ($type_info["CHCK_DATE"] as $key => $value) {
            if ($type_info["cnt"] <= $value) {
                $type_info["cnt"] = 0;
            }
        }

        return $type_info["cnt"] > 0 ? "Y" : "N";
    }

    // 타입선택형 예약일떄 연박이 안되는 객실일경우 날짜별로 예약 가능한 객실 찾기 (크로스 예약)
    function searchCrossRoomCode($userId, $typeIdx, $date)
    {
        $type_room_param = array($userId, $typeIdx);
        $type_room_query = "
				SELECT
					ROOM_CODE 
				FROM
					ROOM_XXX 
				WHERE
					USER_ID = ? 
					AND TYPE_IDX = ?";

        $res = $this->db->query($type_room_query, $type_room_param)->result_array();

        $arr_type_roomcode = [];
        foreach ($res as $roomcode) {
            array_push($arr_type_roomcode, $roomcode['ROOM_CODE']);
        }

        // 예약 되어있는 객실
        $param = array($userId, $date, $userId, $typeIdx);
        $resv_room_query = "
					SELECT
						B.ROOM_CODE 
					FROM
						RESV_XXXX A,
						RESV_INFO_ROOM B 
					WHERE
						A.USER_ID = ? 
						AND B.USER_ID = A.USER_ID 
						AND B.CHCK_IN_DATE = ? 
						AND A.RESV_NO = B.RESV_NO 
						AND A.RESV_STAT IN ( '00', '01', '09' )
						AND B.ROOM_CODE IN ( SELECT ROOM_CODE FROM ROOM_XXX WHERE USER_ID = ? AND TYPE_IDX = ? )";

        $res = $this->db->query($resv_room_query, $param)->result_array();

        $resv_room = [];
        foreach ($res as $roomcode) {
            array_push($resv_room, $roomcode['ROOM_CODE']);
        }

        $arr_type_roomcode = array_values(array_diff($arr_type_roomcode, $resv_room));

        // 방막기된 객실
        $block_room_query = "
					SELECT
						ROOM_CODE 
					FROM
						ROOM_XXXX_DATE 
					WHERE
						USER_ID = ? 
						AND DSBL_DATE = ?
						AND ROOM_CODE IN ( SELECT ROOM_CODE FROM ROOM_XXX WHERE USER_ID = ? AND TYPE_IDX = ? )";

        $res = $this->db->query($block_room_query, $param)->result_array();

        $block_room = [];
        foreach ($res as $roomcode) {
            array_push($block_room, $roomcode['ROOM_CODE']);
        }

        $arr_type_roomcode = array_values(array_diff($arr_type_roomcode, $block_room));

        return $arr_type_roomcode[0];
    }

    // 환불 기준 조회
    function selectRefu($userId)
    {
        $param = array($userId);
        $query = "
			SELECT 
				STD_DAY, 
				CNCL_COMS, 
				REFU_PRCE
			FROM 
				REFU_INFO
			WHERE 
				USER_ID = ?
			ORDER BY 
				CAST(STD_DAY AS DECIMAL)";

        return $this->db->query($query, $param)->result_array();
    }


    // 기타 선택사항 조회
    function selectEtcCheckList($userId)
    {

        $param = array($userId);
        $query = "
			SELECT
				USER_ID,
				SEQ,
				SERV_NM,
				SERV_DETAIL,
				SERV_OPTION,
				SORT_NO,
			CASE
					
					WHEN REQU_YN = 'Y' THEN
					'REQUIRED' 
				END REQU_YN,
				USE_YN 
			FROM
				ETC_SEL_LIST 
			WHERE
				USER_ID = ? 
				AND USE_YN = 'Y' 
			ORDER BY
				SORT_NO";

        return $this->db->query($query, $param)->result_array();
    }


    // 환불규정
    function selectCnclList($userId)
    {
        $param = array($userId);
        $query = "
			SELECT
				CNCL_COMM,
				PONT_YN,
				USE_YN 
			FROM
				CNCL_COMM 
			WHERE
				USER_ID = ? 
				AND (
				USE_YN IS NULL 
				OR USE_YN = 'N')";

        return $this->db->query($query, $param)->result_array();
    }

    // 펜션 카드사용 유무 및 펜션명
    function selectAdminCardInfo($userId)
    {
        $param = array($userId);
        $query = "
			SELECT
				IFNULL( CARD_USE_YN, 'N' ) CARD_USE_YN,
				CARD_KEY,
				BUSI_NM,
				ROOM_KEY_YN 
			FROM
				ADMIN_XXXX 
			WHERE
				USER_ID = ?";

        return $this->db->query($query, $param)->row_array();
    }

    // 펜션 객실별 카드사용시 카드번호
    function selectRoomCardInfo($userId, $ROOM_CODE)
    {
        $param = array($userId, $ROOM_CODE);
        $query = "
			SELECT
				CARD_KEY 
			FROM
				ROOM_XXX 
			WHERE
				USER_ID = ? 
            AND ROOM_CODE = ?";

        return $this->db->query($query, $param)->row_array();
    }

    // 펜션 결제용 카드키 정보 가져오기
    function getcardSysUseRoom()
    {
        $userId = $_POST['USER_ID'];
        $ROOM_CODE = $_GET['ROOM_CODE'];

        if ($userId === '') {
            return json_encode([], true);
        }

        // 펜션 카드사용유무및 펜션명
        $info = $this->selectAdminCardInfo($userId);

        //	룸별 카드키가 등록된 경우 룸별 카드키 확인
        if($info['ROOM_KEY_YN'] == "Y"){
            $room_card_key = $this->selectRoomCardInfo($userId, $ROOM_CODE);

            // 룸별 카드키가 있는 경우 룸별 카드키로 결제, 없는 경우 펜션 카드키 사용
            if(!empty($room_card_key['CARD_KEY'])) {
                $info['CARD_KEY'] = $room_card_key['CARD_KEY'];
            }
        }

        return json_encode($info, true);
    }

	function get_key_info($userId, $ROOM_CODE)
    {
        if ($userId === '') {
            return json_encode([], true);
        }

        // 펜션 카드사용유무및 펜션명
        $info = $this->selectAdminCardInfo($userId);

        //	룸별 카드키가 등록된 경우 룸별 카드키 확인
        if($info['ROOM_KEY_YN'] == "Y"){
            $room_card_key = $this->selectRoomCardInfo($userId, $ROOM_CODE);

            // 룸별 카드키가 있는 경우 룸별 카드키로 결제, 없는 경우 펜션 카드키 사용
            if(!empty($room_card_key['CARD_KEY'])) {
                $info['CARD_KEY'] = $room_card_key['CARD_KEY'];
            }
        }

        return json_encode($info, true);
    }
	function get_key_info2($userId)
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

    // 신규 예약번호조회
    function getReserveNo()
    {
        $this->db->trans_begin();

        $param = array($_POST['USER_ID']);
        $query1 = "INSERT INTO RESV_NO_INDEX SET USER_ID = ?";

        $this->db->query($query1, $param);

        $query = "
				SELECT SUBSTRING( CONCAT('0000000000', CONVERT( MAX(RESV_NO), UNSIGNED)), -10 )  RESERVE_CODE
				  FROM RESV_NO_INDEX
				 WHERE USER_ID = ?
				";
        $result = $this->db->query($query, $param)->result_array();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return "error";
        } else {
            $this->db->trans_commit();

            $query2 = "
			SELECT
				SUBSTRING( CONCAT( '0000000000', CONVERT ( MAX( RESV_NO ), UNSIGNED )), - 10 ) RESERVE_CODE 
			FROM
				RESV_NO_INDEX 
			WHERE
				USER_ID = ?";

            $result = $this->db->query($query2, $param)->row_array();

            return $result['RESERVE_CODE'];
        }
    }

    // 타입 선택형 예약저장 & 문자발송
    public function reserveProcessType()
    {
        $this->load->library('SmsCommonLib');

        $userId = $_POST['USER_ID'];
		if( $userId == '' ) $userId = $_POST['userId'];

        $RESV = $_POST['RESV'];

        // 객실 예약전 예약 가능여부 한번더 체크
        if ($this->typeIsAbleDtCheck($userId, $_POST['DATE'], $_POST['TYPE_IDX']) === 'Y') {
            //객실 예약정보 저장
            $resvNo = $this->insertReserveType();

            if ($resvNo['resvNo']) {
                $this->load->model('smsmngt/smsmngtmodel');

                $resvSmsInfo = $this->smsmngtmodel->selectResvSmsInfo($userId, $resvNo['resvNo']);

                if ($RESV['PAYM_KIND'] == '00') {
                    $this->smscommonlib->busiSmsSend($userId, 1, $resvSmsInfo);
                    $this->smscommonlib->busiSmsSend($userId, 2, $resvSmsInfo);
                    $this->smscommonlib->busiSmsSend($userId, 3, $resvSmsInfo);
                } else if ($RESV['PAYM_KIND'] == '01' || $RESV['PAYM_KIND'] === "02" || $RESV['PAYM_KIND'] === "03" || $RESV['PAYM_KIND'] === "04") {
                    $this->smscommonlib->busiSmsSend($userId, 17, $resvSmsInfo);
                    $this->smscommonlib->busiSmsSend($userId, 18, $resvSmsInfo);
//                    $this->smscommonlib->busiSmsSend($userId, 4, $resvSmsInfo);
                    $this->smscommonlib->busiSmsSend($userId, 5, $resvSmsInfo);
                }
            }

            return $resvNo['resvNo'];
        }else {

        }
    }
 
    // 예약저장 (타입 선택형) 크로스 예약 가능
    function insertReserveType()
    {
        $this->db->trans_begin();

        try {

            $userId = $_POST['USER_ID'];

            $RESV = $_POST['RESV'];
            $resvNo = $RESV['RESV_NO'];

            $DATE = $_POST['DATE'];
            $TYPE_IDX = $_POST['TYPE_IDX'];
            $ROOM_CODE = $_POST['ROOM_CODE'];
            $RESV_TYPE_CODE = $_POST['RESV_TYPE_CODE'];

            if (!array_key_exists('RESV_GROUP_NO', $RESV)) {
                $misec = explode(" ", microtime());
                $RESV['RESV_GROUP_NO'] = date('Ymdhis') . "_" . substr($misec[0], 2, 6);
            }

            // 예약번호 확인
            if ($resvNo == '' || $resvNo == null) {
                return;
            }

            // 예약될 객실 룸코드 찾기
            $arrRoomCode = [];
            if ($RESV_TYPE_CODE === 'PASS') {
                // 연박으로 같은 객실에 예약이 가능할경우
                foreach ($DATE as $key => $value) {
                    array_push($arrRoomCode, $ROOM_CODE);
                }
            } else {
                // 연박으로 같은 객실에 예약이 불가능 할 경우 크로스 예약으로 날짜별 룸코드 가져오기
                // 단 첫날 예약 룸코드는 값으로 넘어온 룸코드로 한다
                foreach ($DATE as $key => $value) {
                    if ($key === 0) {
                        array_push($arrRoomCode, $ROOM_CODE);
                    } else {
                        array_push($arrRoomCode, $this->searchCrossRoomCode($userId, $TYPE_IDX, $value));
                    }
                }
            }

            //	자동취소 보류시간 포함
            $param = array($userId);
            $query = "
					SELECT
						DATE_ADD( NOW(), INTERVAL A.WAIT_TIME HOUR ) RESV_CNCL_TIME,
						AUTO_CACL_USE_TM_YN,
						AUTO_CACL_USE_TM_S,
						AUTO_CACL_USE_TM_E 
					FROM
						( SELECT DEPO_WAIT_TIME WAIT_TIME, AUTO_CACL_USE_TM_YN, AUTO_CACL_USE_TM_S, AUTO_CACL_USE_TM_E FROM ADMIN_XXXX WHERE USER_ID = ? ) A";

            $result = $this->db->query($query, $param)->row_array();

            $st_tm = $result['AUTO_CACL_USE_TM_S'];
            $ed_tm = $result['AUTO_CACL_USE_TM_E'];
            $is_wait = $result['AUTO_CACL_USE_TM_YN'];
            $RESV_CNCL_TIME = $result['RESV_CNCL_TIME'];

            //	자동취소 보류로 설정된 경우 시간을 AUTO_CACL_USE_TM_E  로 한다.
            if ($is_wait == "Y") {
                // 예약시간 기준에서 예약 취소 시간 기준으로 변경
                $cncl_time = strtotime($RESV_CNCL_TIME);
                $cncl_hour = date("G", $cncl_time);

                //1~3 사이에 걸린경우 03:59:59 로 한다.
                if ($cncl_hour >= $st_tm && $cncl_hour <= $ed_tm) {
                    // 시간만 바꾸어 사용한다.
                    if ($ed_tm < 10) {
                        $ed_tm = "0" . $ed_tm;
                    }
                    $RESV_CNCL_TIME = date("Y", $cncl_time) . "-" . date("m", $cncl_time) . "-" . date("d", $cncl_time) . " " . $ed_tm . ":59:59";
                }

                //	22 ~ 07 형태의 경우 당일 07:59:59 에 전송한다. (0~7 처리)
                if ($st_tm > $ed_tm && $cncl_hour <= $ed_tm) {
                    $RESV_CNCL_TIME = date("Y", $cncl_time) . "-" . date("m", $cncl_time) . "-" . date("d", $cncl_time) . " " . $ed_tm . ":59:59";
                }

                //	22 ~ 07 형태의 경우 익일 07:59:59 에 전송한다. (22, 23 처리)
                if ($st_tm > $ed_tm && $cncl_hour >= $st_tm) {        //	날짜가 변한 경우  1일을 더하고 시간을 바꾸어 사용한다.
                    $now_time2 = $cncl_time + 86400;
                    $RESV_CNCL_TIME = date("Y", $now_time2) . "-" . date("m", $now_time2) . "-" . date("d", $now_time2) . " " . $ed_tm . ":59:59";
                }
            }

            //결제금액
            $paymPrce = 0;
            $resvStat = '00';
            if ($RESV['PAYM_KIND'] === "01" || $RESV['PAYM_KIND'] === "02" || $RESV['PAYM_KIND'] === "03" || $RESV['PAYM_KIND'] === "04") {
                $paymPrce = $RESV['TOT_PRCE'];
                $resvStat = '01';
            }

            if ($RESV['CONS_SALE_PRCE'] == '' || $RESV['CONS_SALE_PRCE'] == null) {
                $RESV['CONS_SALE_PRCE'] = 0;
            }

            if ($RESV['USE_EMONEY'] == '' || $RESV['USE_EMONEY'] == null) {
                $RESV['USE_EMONEY'] = 0;
            }

            $RESV['SEND_RSLT_CODE'] = '';

            //예약정보저장
            $param = array(
                $userId,
                $resvNo,
                $RESV['RESV_NM'],
                $RESV['RESV_TEL'],
                $RESV['RESV_EMGS_TEL'],
                addslashes(nl2br($RESV['RESV_COMT'])),
                $RESV['ROOM_PRCE'],
                $RESV['PERS_PRCE'],
                $RESV['TOT_PRCE'],
                $RESV['OPTN_PRCE'],
                $RESV['CONS_SALE_PRCE'],
                0,
                $resvStat,
                $RESV['ACCO_SMS_SEND_YN'],
                $RESV['RESV_SMS_SEND_YN'],
                $userId,
                $RESV_CNCL_TIME,
                $RESV['PAYM_KIND'],
                $RESV['IS_MOBILE'],
                $RESV['USE_EMONEY'],
                $RESV['RCMM_TEL'],
                $this->input->ip_address(),
                $paymPrce,
                empty($RESV['PROVIDER']) ? "" : $RESV['PROVIDER'],
                $RESV['RESV_GROUP_NO']
            );

            $query = "
						INSERT INTO RESV_XXXX 
							SET USER_ID = ?,
							RESV_NO = ?,
							RESV_NM = ?,
							RESV_TEL = ?,
							RESV_EMGS_TEL = ?,
							RESV_COMT = ?,
							ROOM_PRCE = ?,
							PERS_PRCE = ?,
							TOT_PRCE = ?,
							OPTN_PRCE = ?,
							CONS_SALE_PRCE = ?,
							ADJU_PRCE = ?,
							RESV_STAT = ?,
							ACCO_SMS_SEND_YN = ?,
							RESV_SMS_SEND_YN = ?,
							REG_USER = ?,
							RESV_CNCL_TIME = ?,
							RESV_CNCL_SMS_SEND_YN = 'N',
							PAYM_KIND = ?,
							IS_MOBILE = ?,
							RESV_REG_DATE = NOW(),
							REG_DATE = NOW(),
							USE_EMONEY = ?,
							RCMM_TEL = ?,
							RESV_IP = ?,
							PAYM_PRCE = ?,
							RESV_SELLER = '',
							PROVIDER = ?,
							RESV_GROUP_NO = ?
						";

            $this->db->query($query, $param);

            //객실정보저장
            foreach ($RESV['RESV_INFO_ROOMS'] as $key => $item) {

                $param = array(
                    $userId,
                    $resvNo,
                    $arrRoomCode[$key],
                    $item['CHCK_IN_DATE'],
                    $item['ADIT_ADLT_NUM'],
                    $item['ADIT_KIDS_NUM'],
                    $item['ADIT_INFT_NUM'],
                    $item['ROOM_PRCE'],
                    $item['ADIT_ADLT_PRCE'],
                    $item['ADIT_KIDS_PRCE'],
                    $item['ADIT_INFT_PRCE'],
                    $userId,
                    $userId,
                    $resvNo,
                    $RESV['RESV_GROUP_NO']
                );

                $query = "
						INSERT INTO RESV_INFO_ROOM 
						SET USER_ID = ?,
						RESV_NO = ?,
						ROOM_CODE = ?,
						CHCK_IN_DATE = ?,
						ADIT_ADLT_NUM = ?,
						ADIT_KIDS_NUM = ?,
						ADIT_INFT_NUM = ?,
						ROOM_PRCE = ?,
						ADIT_ADLT_PRCE = ?,
						ADIT_KIDS_PRCE = ?,
						ADIT_INFT_PRCE = ?,
						REG_USER = ?,
						SEQ = (
							SELECT
								IFNULL( MAX( SEQ ), 0 ) + 1 
							FROM
								RESV_INFO_ROOM A 
							WHERE
								USER_ID = ? 
							AND RESV_NO = ?),
							REG_DATE = NOW(),
							RESV_GROUP_NO = ?";

                $this->db->query($query, $param);
            }

            //옵션저장
            foreach ($RESV['RESV_INFO_DTLS'] as $item) {
                if ($item['QTY'] > 0) {
                    $param = array(
                        $userId,
                        $resvNo,
                        $userId,
                        $resvNo,
                        $item['OPTN_CODE'],
                        $item['QTY'],
                        $item['OPTN_PRCE'],
                        $userId,
                        $RESV['RESV_GROUP_NO']
                    );

                    $query = "
							INSERT INTO RESV_INFO_DTL 
							SET USER_ID = ?,
							RESV_NO = ?,
							SEQ = (
								SELECT
									IFNULL( MAX( SEQ ), 0 ) + 1 
								FROM
									RESV_INFO_DTL A 
								WHERE
									USER_ID = ? 
								AND RESV_NO = ?),
								OPTN_CODE = ?,
								QTY = ?,
								OPTN_PRCE = ?,
								REG_USER = ?,
								REG_DATE = NOW(),
								RESV_GROUP_NO = ?";

                    $this->db->query($query, $param);
                }
            }

            //기타선택리스트
            foreach ($RESV['ETC_CHECK_LIST'] as $item) {
                $param = array(
                    $userId,
                    $resvNo,
                    $item['SEQ'],
                    $item['ETC_SEL_VALUE'],
                    $userId,
                    $RESV['RESV_GROUP_NO']
                );

                $query = "
						INSERT INTO RESV_INFO_ETC 
						SET USER_ID = ?,
						RESV_NO = ?,
						ETC_SEQ = ?,
						ETC_SEL_VALUE = ?,
						REG_USER = ?,
						REG_DATE = NOW(),
						RESV_GROUP_NO = ?";
                $this->db->query($query, $param);
            }

        } catch (Exception $e) {
            echo $e;
            $this->db->trans_rollback();
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
        } else {
            $this->db->trans_commit();
            return array('resvNo' => $resvNo, 'paymKind' => $RESV['PAYM_KIND']);
        }
    }

    // 무통장입금 자동 취소시간
    function getCancelTime($userId, $resvNo)
    {
        $param = array($userId, $resvNo);
        $query = "
			SELECT
				DATE_FORMAT( RESV_CNCL_TIME, '%Y년 %m월 %d일 %h시 %i분' ) RESV_CNCL_TIME 
			FROM
				RESV_XXXX 
			WHERE
				USER_ID = ? 
				AND RESV_NO = ?";

        return $this->db->query($query, $param)->row_array()['RESV_CNCL_TIME'];
    }

    // 입금대기시간-기본
    function getDepoWaitTime($userId)
    {
        $param = array($userId);
        $query = "
			SELECT
				DEPO_WAIT_TIME
			FROM
				ADMIN_XXXX 
			WHERE
				USER_ID = ?";

        return $this->db->query($query, $param)->row_array()['DEPO_WAIT_TIME'];
    }

    // 입금 계좌 번호
    function getAcco($userId, $roomcode)
    {
        $param = array($userId, $userId, $roomcode);
        $query = "
			SELECT
				USER_ACCO 
			FROM
				ADMIN_ACCO_INFO 
			WHERE
				USER_ID = ? 
				AND SEQ = (SELECT ACCO_INFO FROM ROOM_XXX WHERE USER_ID = ? AND ROOM_CODE = ?)";

        return $this->db->query($query, $param)->row_array()['USER_ACCO'];
    }

    // 입실전필독사항
    function selectEntrList($userId)
    {
        $param = array($userId);
        $query = "
			SELECT
				ENTR_COMM,
				PONT_YN,
				USE_YN 
			FROM
				ENTR_COMM 
			WHERE
				USER_ID = ? 
				AND (
				USE_YN IS NULL 
				OR USE_YN = 'N')";

        return $this->db->query($query, $param)->result_array();
    }

    // 예약저장전 일정 시간 랜덤 으로 지연시키기기 위한 랜덤 시간 생성
    function randSleepTime()
    {
        $sleepTime = [];

        for ($i = 200000; $i < 900000; $i += 10000) {
            array_push($sleepTime, $i);
        }

        return array_rand($sleepTime);
    }

	 //적립금서비스 사용유무
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
}
