<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Hopeter1018\AngularjsPostbackValidator;

/**
 * Get appropriate format php value from posted AngularJs model 
 *
 * @version $id$
 * @author peter.ho
 */
class WebRequest
{

    /**
     * shortcut to json_decode(file_get_contents("php://input"));
     * 
     * @return \stdClass
     */
    public static function getRequestParams()
    {
        $request = json_decode(file_get_contents("php://input"));
//        if (\Zms5\Helpers\RequestHeaderHelper::has('zms-form-upload')) {
//            $request = (object) $_POST;
//        }
        if (APP_IS_DEV and $request == null) {
            $request = (object) $_GET;
        }
        return $request;
    }

    /**
     * 
     * @param String $dateString
     * @return \Carbon\Carbon|null
     */
    public static function parseJsDate($dateString, $format = APP_JS_DATE)
    {
        $dateTime = \DateTime::createFromFormat($format, $dateString, new \DateTimeZone(APP_DEFAULT_TIMEZONE));
        return ($dateTime !== null and $dateTime !== false and $dateString === $dateTime->format($format)) ? \Carbon\Carbon::instance($dateTime) : null;
    }

    /**
     * 
     * @param type $daily
     * @return \DateTime
     * @throws \Exception
     */
    public static function toDateTime($daily)
    {
        if (!$daily instanceof \DateTime) {
            if (is_object($daily) and isset($daily->date) and $daily->date != '') {
                $daily = new \DateTime($daily->date);
            } elseif (is_string($daily)) {
                $daily = new \DateTime($daily);
            } else {
                throw new \Exception("Can't transform \$daily to \DateTime");
            }
        }
        return $daily;
    }

}
