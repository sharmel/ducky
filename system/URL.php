<?php
class URL
{
    // returns the current controller, based on the URI
    public static function getCurrentPath()
    {
        $path =  substr($_SERVER['REQUEST_URI'], strlen(dirname($_SERVER['SCRIPT_NAME'])) );
        $path = preg_replace('#(' . preg_quote('?lang=') . '.{2})#', '', $path);
        $path = trim($path, '/');

        // if no scriptfile in de request_uri, we assume index.php is called
        $path = ($path) ? $path : 'index.php';

        return strtolower($path);
    }

    public static function getCrumbs()
    {
        return explode('/', strtolower(URL::getCurrentPath()));
    }

    public static function base_uri($uri = '')
    {
	global $settings;
        return $settings['CONTEXT_URL'] . str_replace('index.php', '', $_SERVER['SCRIPT_NAME']) . $uri;
    }

    /**
     * truncate a string provided by the maximum limit without breaking a word
     * @param string $str
     * @param integer $maxlen
     * @return string
     */
    public static function truncateStringWords($str, $maxlen)
    {
        if (strlen($str) <= $maxlen) return $str;

        $newstr = substr($str, 0, $maxlen);
        if (substr($newstr, -1, 1) != ' ') $newstr = substr($newstr, 0, strrpos($newstr, " "));

        return $newstr;
    }
}
