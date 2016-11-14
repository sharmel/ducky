<?php
class Langs_m
{
    private static final $LANG_PATH = 'application/languages/';

    public function getStrings($lang)
    {
        global $settings;
        if (! preg_match('^[a-z]{2}$')) $lang = $settings['DEFAULT_LANG'];

        include(APPLICATION_PATH . 'languages/lang.' . $lang . '.php');
        return getTranslation();
    }
    
    public function getLangs()
    {
        return array(
            array(
                    'id'   => 0,
                    'name' => 'English',
                    'flag' => 'en',
                ),
            );
    }
    
}
