<?php
class MeetMeatModel extends CI_Model {
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    function getUseMeetmeat()
    {
        $userId = $this->session->userdata('userId');
        $row = $this->db->select('MEETMEAT_USE_YN')->where([
            'USER_ID' => $userId,
            'MEETMEAT_USE_YN' => 'Y'
        ])->get('ADMIN_XXXX')->row_array();
        return $row ? true: false;
    }

    /*
    * 한우 리스트 출력
    */
    function getMeatmeatList()
    {
        $userId = $this->session->userdata('userId');

        $query = "
            SELECT 		
                D.USER_ID,
                A.BUSI_NM,
                CASE
                    WHEN R.USER_ID != 'testmeat' THEN A.USER_ADDR
                    ELSE (SELECT SHIPPING_ADDRESS FROM RESV_XXXXX_DTL rmd WHERE rmd.USER_ID = R.USER_ID AND rmd.RESV_NO = R.RESV_NO)
                END AS SHIPPING_ADDRESS, 
                R.RESV_NM,
                R.RESV_STAT,
                R.REG_USER,
                CASE
                    WHEN R.REG_USER = 'NAVER' THEN '네이버'
                    ELSE '홈페이지'
                END AS REG_USER_KR,
                CASE 
                    WHEN R.RESV_STAT  = '00' THEN '입금대기'
                    WHEN R.RESV_STAT  ='01' THEN '입금완료'
                    WHEN R.RESV_STAT  ='02' THEN '미입금취소'
                    WHEN R.RESV_STAT  ='03' THEN '환불'
                    WHEN R.RESV_STAT  ='09' THEN '입금대기'
                END AS RESV_STAT,
                R.RESV_TEL,
                R.RESV_EMGS_TEL,
                ROOM.CHCK_IN_DATE,
                ROOM.ADIT_ADLT_NUM,
                ROOM.ADIT_KIDS_NUM,
                ROOM.ADIT_INFT_NUM,
                ROOM.ROOM_CODE,
                ROOM.TYPE_NM,			 
                D.RESV_NO,			 
                O.OPTN_NM,
                D.OPTN_PRCE,
                D.QTY,
                R.REG_DATE,
                R.MEETMEAT_CHECK_YN,			 
                R.MEETMEAT_DELIVERY_YN 
            FROM RESV_XXX_DTL D
                INNER JOIN 
                ( SELECT A.RESV_NO,MIN(A.CHCK_IN_DATE) AS CHCK_IN_DATE,B.ROOM_CODE,B.TYPE_NM, A.ADIT_ADLT_NUM,A.ADIT_KIDS_NUM,A.ADIT_INFT_NUM					 
                FROM RESV_INFO_ROOM A
                INNER JOIN ROOM_XXX B
                ON A.USER_ID = B.USER_ID 
                AND A.ROOM_CODE = B.ROOM_CODE
                GROUP BY A.RESV_NO
                ) ROOM
                ON D.RESV_NO = ROOM.RESV_NO
                INNER JOIN RESV_XXXX R
                ON R.RESV_NO = D.RESV_NO
                INNER JOIN ADMIN_XXXX A
                ON A.USER_ID = D.USER_ID
                LEFT JOIN ROOM_OPTN O
                ON D.USER_ID = O.USER_ID 
                AND D.OPTN_CODE = O.OPTN_CODE
            WHERE
                D.USER_ID = '{$userId}'
                AND O.OPTN_NM like ('%1++한우%')
                AND R.RESV_STAT = '01'
            ORDER BY ROOM.CHCK_IN_DATE ASC
        ";

        $rows = $this->db->query($query);
        $result = [];

        foreach ($rows->result_array() as $row) {
            //네이버는 옵션가격에 개수 방식이고 그외는 합산금액에 개수 방식이라 구분한다.
            $temp_price = 0;
            $temp_toral = 0;
            if ($row['REG_USER'] !== 'NAVER') {
                $temp_price = (int)$row['OPTN_PRCE'] / (int)$row['QTY'];
                $temp_toral = (int)$row['OPTN_PRCE'];
            } else {
                $temp_price =  (int)$row['OPTN_PRCE'];
                $temp_toral = (int)$row['OPTN_PRCE'] * (int)$row['QTY'];
            }

            if (!isset($result[$row['RESV_NO']])) {
                $result[$row['RESV_NO']] = $row;
                $result[$row['RESV_NO']]['OPTN_STR'] = $row['OPTN_NM'];
                $result[$row['RESV_NO']]['OPTN_PRCE_STR'] = number_format($temp_price);
                $result[$row['RESV_NO']]['OPTN_QTY_STR'] = $row['QTY'];
                $result[$row['RESV_NO']]['OPTN_TOTAL'] = $temp_toral;

            } else {
                $result[$row['RESV_NO']]['OPTN_STR'] .= '<br>'.$row['OPTN_NM'];
                $result[$row['RESV_NO']]['OPTN_PRCE_STR'] .= '<br>'.number_format($temp_price);
                $result[$row['RESV_NO']]['OPTN_QTY_STR'] .= '<br>'.$row['QTY'];
                $result[$row['RESV_NO']]['OPTN_TOTAL'] += $temp_toral;
            }
        }

        return $result;
    }
}
