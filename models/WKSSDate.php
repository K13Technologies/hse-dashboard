<?php
class WKSSDate{
    
    const FORMAT_LIST = "M d, Y ";
    const FORMAT_DETAILS = "Y-m-d H:i:s";
    const FORMAT_WORDS = 'l, g:i A, M d, Y'; //new and improved readable format
    
    // Converts the stored time on the server to local time 
    // If the user has a timezone offset from UTC, it will be calculated when displaying the date
    public static function display($ts, $created_at, $format = self::FORMAT_WORDS){
        if ($ts && Session::has('tz_offset')){
            $adminTzOffset = Session::get('tz_offset');
            $final = (int)$ts - (int)$adminTzOffset;
            return date($format, $final);
        }else{
            return $created_at;
        }
    }

    public static function getCurrentUserLocalTimestampStringWithFormat($format){
        // Convert current date timestamp to UNIX timestamp
        $ts = strtotime(date('Y-m-d H:i:s'));

        if ($ts && Session::has('tz_offset')){
            $adminTzOffset = Session::get('tz_offset');
            $final = (int)$ts - (int)$adminTzOffset;
            return date($format, $final);
        }else{
            return date($format);
        }
    }

    public static function timestampToStringWithCustomFormat($timestamp, $format) {
        $timestamp = new DateTime($timestamp);
        return $timestamp->format($format);
    }

    public static function timestampToStringWithCustomFormatAndLocalTime($timestamp, $format){
        if ($timestamp && Session::has('tz_offset')){
            $adminTzOffset = Session::get('tz_offset');
            $final = (int)$timestamp - (int)$adminTzOffset;
            return date($format, $final);
        }
        else{
            return $timestamp;
        }
    }
    
    // Get timestamp from date with timezone
    public static function getTsFromDateWithTz($date){
        $ts = strtotime($date);
        return $ts;
    }
    
    public static function getTsStart(&$refDate, $timeframe){
        $refDate = strtotime($refDate); 
        switch ($timeframe){
            case "weekly":
                return strtotime('-1 week',$refDate);
            case "monthly":
                return strtotime('-1 month',$refDate);
            case "yearly":
                return strtotime('-1 year',$refDate);
            case "forever":
                return null;
            default :
                return null;
        }
    }
}