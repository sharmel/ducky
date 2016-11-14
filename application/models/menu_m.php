<?php

class Menu_m
{

    public function getMenu($role, $protected, $lang)
    {
        if ($role == false || ! $protected)
            return $this->getVisitormenu($lang);

        return $this->getUsermenu($lang);
    }


    public function getVisitormenu($lang)
    {
        return array(
            array(
                'link' => '#info',
                'description' => 'About',
            ),
            array(
                'link' => '#features',
                'description' => 'Info',
            ),
            array(
                'link' => '#pricing',
                'description' => 'Pricing',
            ),
            array(
                'link' => '#footer',
                'description' => 'Contact',
            ),
        );
    }


    public function getUsermenu($lang)
    {
        return array(
            array(
                'link' => 'dashboard/index',
                'description' => 'Dashboard',
            ),
            array(
                'link' => 'profile/index',
                'description' => 'Profile',
            ),
            array(
                'link' => 'profile/payment',
                'description' => 'Payment',
            ),
        );
    }

    public function getAdminmenu(){
        return array(
        array(
            'link' => 'admin/index',
            'description' => 'Overview',
        ),
        array(
            'link' => 'admin/tasks',
            'description' => 'Tasks',
        ),
        array(
            'link' => 'admin/users',
            'description' => 'Users',
        ),
        array(
            'link' => 'admin/logs',
            'description' => 'Logs',
        )
    );
    }


}
