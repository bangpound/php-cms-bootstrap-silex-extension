<?php

namespace Bangpound\Process;

use Symfony\Component\HttpFoundation\Request;

class InjectRequestGlobals {

    /**
     * Returns an array of PHP global variables according to this request instance.
     *
     * It behaves simliar to Request->overrideGlobals() except that it does not
     * override $_GET, $_POST, $globalz['_REQUEST'], $_SERVER, $_COOKIE, it simply returns
     * an array that can be extract() in a different context to create new globals.
     *
     * @see \Symfony\Component\HttpFoundation\Request::overrideGlobals()
     *
     * @api
     */
    static public function fromRequest(Request $httpRequest)
    {
        $globalz = array(
            '_GET' => $httpRequest->query->all(),
            '_POST' => $httpRequest->request->all(),
            '_SERVER' => $httpRequest->server->all(),
            '_COOKIE' => $httpRequest->cookies->all(),
        );

        foreach ($httpRequest->headers->all() as $key => $value) {
            $key = strtoupper(str_replace('-', '_', $key));
            if (in_array($key, array('CONTENT_TYPE', 'CONTENT_LENGTH'))) {
                $globalz['_SERVER'][$key] = implode(', ', $value);
            } else {
                $globalz['_SERVER']['HTTP_'.$key] = implode(', ', $value);
            }
        }

        $request = array('g' => $_GET, 'p' => $_POST, 'c' => $_COOKIE);

        $requestOrder = ini_get('request_order') ?: ini_get('variable_order');
        $requestOrder = preg_replace('#[^cgp]#', '', strtolower($requestOrder)) ?: 'gp';

        $globalz['_REQUEST'] = array();
        foreach (str_split($requestOrder) as $order) {
            $globalz['_REQUEST'] = array_merge($globalz['_REQUEST'], $request[$order]);
        }
        return $globalz;
    }

    /**
     * Returns the script to execute when the request must be insulated.
     *
     * @param Request $request A Request instance
     *
     * @return string
     */
    static function toSubprocessGlobals(Request $request)
    {
        $globalz = str_replace("'", "\\'", serialize(InjectRequestGlobals::fromRequest($request)));

        return <<<EOF
call_user_func(function () { foreach (unserialize('$globalz') as \$key => \$value) { \$GLOBALS[\$key] = \$value; } });
EOF;
    }
}