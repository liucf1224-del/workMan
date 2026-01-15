<?php

use Carbon\Carbon;

/**
 * Here is your custom functions.
 */


function error($msg = '失败', $data = [], $code = 400)
{
    $result = [
        'code' => $code,
        'msg' => $msg,
        'time' => time(),
        'data' => $data,
    ];
    return json($result);
}


function success($data = [], $msg = '成功', $code = 200)
{
    $result = [
        'code' => $code,
        'msg' => $msg,
        'time' => time(),
        'data' => $data,
    ];
    return json($result);
}



function timeHandle($mark): array
{
    switch ($mark) {
        case 1: // 近七日
            $start = \Carbon\Carbon::now()->subDays(7)->format('Y-m-d').' 00:00:00';
            $end = Carbon::yesterday()->format('Y-m-d').' 23:59:59';
            break;
        case 2: // 近30日
            $start = Carbon::now()->subDays(30)->format('Y-m-d').' 00:00:00';
            $end = Carbon::yesterday()->format('Y-m-d').' 23:59:59';
            break;
        case 3: // 今年至昨日
            $start = Carbon::now()->startOfYear()->format('Y-m-d').' 00:00:00';
            $end = Carbon::yesterday()->format('Y-m-d').' 23:59:59';
            break;
        case 4: // 近60日
            $start = Carbon::now()->subDays(60)->format('Y-m-d').' 00:00:00';
            $end = Carbon::yesterday()->format('Y-m-d').' 23:59:59';
            break;
        case 5: // 近90日
            $start = Carbon::now()->subDays(90)->format('Y-m-d').' 00:00:00';
            $end = Carbon::yesterday()->format('Y-m-d').' 23:59:59';
            break;
        default:
            $start = null;
            $end = null;
//            $start = '2025-10-24 00:00:00';
//            $end = '2025-10-30 23:59:59';
    }
    // 计算天数差
    $days = null;
    if ($start && $end) {
        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);
        $days = $startDate->diffInDays($endDate);
    }
    return [
        'start' => $start,
        'end' => $end,
        'days' => $days,
    ];
}