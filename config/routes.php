<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

use \lithium\net\http\Router;

//Router::connect('/minerva/plugin/minerva_gallery/admin/{:controller}', array('library' => 'minerva', 'plugin' => 'minerva_gallery', 'controller' => 'items', 'action' => 'index'));

// Admin Plugin Route
// TODO: See why this isn't catching in the Minerva library routes...
// This should already be set...
Router::connect('/minerva_gallery/admin/{:controller}/{:action}/{:args}', array('admin' => 'admin', 'library' => 'minerva_gallery', 'controller' => 'items', 'action' => 'index'));

// JSON Routes
Router::connect('/minerva_gallery/{:controller}/{:action}.json', array('library' => 'minerva_gallery', 'controller' => 'items', 'type' => 'json'));
Router::connect('/minerva_gallery/{:controller}/{:action}/{:args}.json', array('library' => 'minerva_gallery', 'controller' => 'items', 'type' => 'json'));

// Route for reading a gallery (note the "document_type" parameter)
Router::connect('/gallery/read/{:url}', array('library' => 'minerva', 'plugin' => 'minerva_gallery', 'controller' => 'pages', 'action' => 'read'));


// Route for listing all galleries
Router::connect('/gallery/index', array('library' => 'minerva', 'plugin' => 'minerva_gallery', 'controller' => 'pages', 'action' => 'index'));
// Pagination for galleries (default limit is 10)
Router::connect('/gallery/index/page:{:page:[0-9]+}', array('library' => 'minerva', 'plugin' => 'minerva_gallery', 'controller' => 'pages', 'action' => 'index'));
Router::connect('/gallery/index/page:{:page:[0-9]+}/limit:{:limit:[0-9]+}', array('library' => 'minerva', 'plugin' => 'minerva_gallery', 'controller' => 'pages', 'action' => 'index'));

// Yes, you can render "static" pages from the library as well by using the "view" action,
// Templates from: /libraries/minerva_gallery/views/pages/static/template-name.html.php
Router::connect('/gallery', array('library' => 'minerva', 'plugin' => 'minerva_gallery', 'controller' => 'pages', 'action' => 'view', 'home'));
Router::connect('/gallery/view/{:args}', array('library' => 'minerva', 'plugin' => 'minerva_gallery', 'controller' => 'pages', 'action' => 'view', 'home'));

// NOTE: /gallery route could also be reached via the default Minerva route: /minerva/plugin/minerva_gallery
// Also: /minerva/plugin/minerva_gallery/pages/read/document == /gallery/read/{:url}

// TODO? Do galleries also need comments? Should there be a unified comments library now?
// Router::connect('/gallery/comments/{:action}.json', array('library' => 'minerva_gallery', 'controller' => 'comments', 'type' => 'json'));
// Router::connect('/gallery/comments/{:action}/{:args}.json', array('library' => 'minerva_gallery', 'controller' => 'comments', 'type' => 'json'));



// SWEEPSTAKES LIBRARY SPECIFIC ROUTES (Routed to look like it belongs to Minerva. Library added as with 'minerva_plugin' => true, so it will follow the render paths.)

/*
Router::connect("/minerva/gallery/admin/{:controller}/{:action}/{:url}", array('admin' => 'admin', 'library' => 'sweeps', 'controller' => 'sweepstakes', 'action' => 'index'));

Router::connect("/minerva/gallery/{:controller}/{:action}", array('library' => 'sweeps', 'controller' => 'sweepstakes', 'action' => 'index'));
Router::connect("/minerva/gallery/{:controller}/{:action}/{:args}", array('library' => 'sweeps', 'controller' => 'sweepstakes', 'action' => 'index'));

Router::connect("/minerva/gallery/{:controller}/{:action}/{:url}", array('library' => 'sweeps', 'controller' => 'sweepstakes', 'action' => 'index'));
 * */
?>