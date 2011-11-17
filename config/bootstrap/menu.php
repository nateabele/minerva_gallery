<?php

use minerva\models\Menu;

// Apply filters to Menu::static_menu() in order to alter and create menus
Menu::applyFilter('static_menu',  function($self, $params, $chain) {
    if($params['name'] == 'admin') {
        $self::$static_menus['admin']['m8_plugins'] = array(
            'title' => 'Plugins',
            'url' => '/minerva/admin',
            'sub_items' => array(
                array(
                    'title' => 'Galleries',
					'url' => array('admin' => 'admin', 'library' => 'minerva_gallery', 'controller' => 'items', 'action' => 'manage')
                )
            )
        );
    }
    
    return $chain->next($self, $params, $chain);
});
?>