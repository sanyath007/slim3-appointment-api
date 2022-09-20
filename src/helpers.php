<?php

function paginate($model, $recordPerPage, $currenPage, $request)
{        
    $count = $model->count();
    
    $perPage = $recordPerPage;
    $page = ($currenPage == 0 ? 1 : $currenPage);
    $offset = ($page - 1) * $perPage;
    $lastPage = ceil($count / $perPage);
    $prev = ($page != $offset + 1) ? $page - 1 : null;
    $next = ($page != $lastPage) ? $page + 1 : null;
    $lastRecordPerPage = ($page != $lastPage) ? ($page * $perPage) : ($count - $offset) + $offset;

    $items = $model->skip($offset)
                ->take($perPage)
                ->get();

    $link = getUrlWithQueryStr($request);

    return [
        'items' => $items,
        'pager' => [
            'total' => $count,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $lastPage,
            'from' => $offset + 1,
            'to' => $lastRecordPerPage,
            'path'  => strrpos($link, '&') ? substr($link, 0, strrpos($link, '&')) : substr($link, 0, strrpos($link, '?')),
            'first_page_url' => $link. 'page=1',
            'prev_page_url' => (!$prev) ? $prev : $link. 'page=' .$prev,
            'next_page_url' => (!$next) ? $next : $link. 'page=' .$next,
            'last_page_url' => $link. 'page=' .$lastPage
        ]
    ];
}

function getUrlWithQueryStr($request)
{
    if($request->getServerParam('QUERY_STRING') === "") { // if querystring in empty
        $qs = '?';
    } else {
        // if found "page=" phrase have to slice out or if not found append querystring with '&'
        if(strrpos($request->getServerParam('QUERY_STRING'), 'page=') === false) {
            $qs = '?'.$request->getServerParam('QUERY_STRING').'&';
        } else {
            if(strrpos($request->getServerParam('QUERY_STRING'), 'page=') > 0) {
                $qs = '?'.substr($request->getServerParam('QUERY_STRING'), 0, 
                        strrpos($request->getServerParam('QUERY_STRING'), 'page='));
            } else {
                $qs = '?';
            }
        }
    }

    return 'http://'.$request->getServerParam('HTTP_HOST'). $request->getServerParam('REDIRECT_URL').$qs;
}

function uploadImage($img, $img_url)
{
    $regx = "/^data:image\/(?<extension>(?:png|gif|jpg|jpeg));base64,(?<image>.+)$/";

    if(preg_match($regx, $img, $matchings)) {
        $img_data = file_get_contents($img);
        $extension = $matchings['extension'];
        $img_name = uniqid().'.'.$extension;
        $img_full_url = str_replace('/index.php', '/assets/uploads/'.$img_name, $img_url);
        $file_to_upload = 'assets/uploads/'.$img_name;

        if(file_put_contents($file_to_upload, $img_data)) {
            return $img_full_url;
        }
    }

    return '';
}

function thdateToDbdate($str)
{
    if (empty($str) || !isset($str)) return null;

    list($day, $month, $year)  = explode('/', $str);

    return ((int)$year - 543). '-' .$month. '-' .$day;
}

function dbDateToThDate($dbDate)
{
    if(empty($dbDate)) return '';

    $arrDate = explode('-', $dbDate);

    return $arrDate[2]. '/' .$arrDate[1]. '/' .((int)$arrDate[0] + 543);
}

const MONTH_LONG_NAMES = [
    '01' => 'มกราคม',
    '02' => 'กุมภาพันธ์',
    '03' => 'มีนาคม',
    '04' => 'เมษายน',
    '05' => 'พฤษภาคม',
    '06' => 'มิถุนายน',
    '07' => 'กรกฎาคม',
    '08' => 'สิงหาคม',
    '09' => 'กันยายน',
    '10' => 'ตุลาคม',
    '11' => 'พฤศจิกายน',
    '12' => 'ธันวาคม',
];

const MONTH_SHORT_NAMES = [
    '01' => 'ม.ค.',
    '02' => 'ก.พ.',
    '03' => 'มี.ค.',
    '04' => 'เม.ย',
    '05' => 'พ.ค.',
    '06' => 'มิ.ย.',
    '07' => 'ก.ค.',
    '08' => 'ส.ค.',
    '09' => 'ก.ย.',
    '10' => 'ต.ค.',
    '11' => 'พ.ย.',
    '12' => 'ธ.ค.',
];

function getShortMonth($monthDigits)
{
    return MONTH_SHORT_NAMES[$monthDigits];
}

function convDbDateToLongThDate($dbDate)
{
    if(empty($dbDate)) return '';

    $arrDate = explode('-', $dbDate);

    return (int)$arrDate[2]. ' ' .MONTH_LONG_NAMES[$arrDate[1]]. ' ' .((int)$arrDate[0] + 543);
}

function convDbDateToLongThMonth($dbDate)
{
    if(empty($dbDate)) return '';

    $arrDate = explode('-', $dbDate);

    return MONTH_LONG_NAMES[$arrDate[1]]. ' ' .((int)$arrDate[0] + 543);
}