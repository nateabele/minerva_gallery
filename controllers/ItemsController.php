<?php
namespace minerva_gallery\controllers;

use \lithium\core\Libraries;
use \lithium\storage\Session;
use li3_access\security\Access;
use \lithium\security\Auth;
use \lithium\security\Password;
use minerva\extensions\util\Util;
use \lithium\util\Set;
use \lithium\util\String;
use \lithium\util\Inflector;
use minerva\models\User;
use li3_flash_message\extensions\storage\FlashMessage;

use minerva_gallery\models\Item;
use minerva_gallery\minerva\models\Page;
use \MongoDate;
use \MongoId;

use lithium\analysis\Logger;

class ItemsController extends \lithium\action\Controller {
	
	public function index() {
		// TODO: Use Minerva's access system (li3_access) 
		// Bigger todo: update li3_acess (Nate's changes) and redo Minerva's access system completely.
		$user = Auth::check('minerva_user');
		if($user['role'] != 'administrator' && $user['role'] != 'content_editor') {
			$this->redirect('/');
			return;
		}
		
		// Default options for pagination, merge with URL parameters
        $defaults = array('page' => 1, 'limit' => 10, 'order' => 'created.desc');
        $params = Set::merge($defaults, $this->request->params);
        
        if((isset($params['page'])) && ($params['page'] == 0)) {
            $params['page'] = 1;
        }
        list($limit, $page, $order) = array($params['limit'], $params['page'], $params['order']);
        
        // never allow a limit of 0
        $limit = ($limit < 0) ? 1:$limit;
        
        $conditions = array();
        
        // Get the documents and the total
        $documents = Item::find('all', array(
            'conditions' => $conditions,
            'limit' => (int)$limit,
            'offset' => ((int)$page - 1) * (int)$limit,
            'order' => $params['order']
        ));
		
		$total = Item::find('count', array(
            'conditions' => $conditions
        ));
        
        $page_number = (int)$page;
        $total_pages = ((int)$limit > 0) ? ceil($total / $limit):0;
        
        // Set data for the view template
        $this->set(compact('documents', 'limit', 'page_number', 'total_pages', 'total'));
	}
	
	public function create() { 
		// TODO: Use Minerva's access system (li3_access) 
		// Bigger todo: update li3_acess (Nate's changes) and redo Minerva's access system completely.
		$user = Auth::check('minerva_user');
		if($user['role'] != 'administrator' && $user['role'] != 'content_editor') {
			$this->redirect('/');
			return;
		}
		
		if(!empty($this->request->data['Filedata'])) {
			
			//Logger::debug(json_encode($this->request->data));
			
			
			// IMPORTANT: Use MongoDate() when inside an array/object because $_schema isn't deep
			$now = new MongoDate();
			$data = array();
			
			// IMPORTANT: The current/target gallery id must be passed in order to associate the item.
			// Otherwise, it'd be stored loose in the system.
			$gallery_id = $this->request->data['gallery_id'];
			
			// If there was only one file uploaded, stick it into a multi-dimensional array.
			// It's just easier to always run the foreach() and code the processing stuff once and here.
			// For now...while we're saving to disk.
			if(!isset($this->request->data['Filedata'][0]['error'])) {
				$this->request->data['Filedata'] = array($this->request->data['Filedata']);
			}
					
			foreach($this->request->data['Filedata'] as $file) {
				
				// TODO: change. possibly adaptable class
				$source_base = '/minerva_gallery/img/gallery_items/';
				$service = 'file';
				
				// Create an Item object
				$id = new MongoId();
				$document = Item::create(array(
					'_id' => $id,
					'created' => $now,
					'modified' => $now,
					'service' => $service,
					'source' => $source_base . $id . '.jpg',
					'title' => $file['name'],
					'_galleries' => array($gallery_id),
					'published' => true
				)); 
				
				// Save file  to disk
				// TODO: Again, change this...maybe use an adaptable class for storage
				if ($file['error'] == UPLOAD_ERR_OK) {
					$upload_dir = LITHIUM_APP_PATH . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'minerva_gallery' . DIRECTORY_SEPARATOR . 'webroot' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'gallery_items' . DIRECTORY_SEPARATOR;
					
					$ext = substr(strrchr($file['name'], '.'), 1);
					switch(strtolower($ext)) {
						case 'jpg':	
						case 'jpeg':
						case 'png':
						case 'gif':
						case 'png':
						case 'doc':
						case 'txt':
							if(move_uploaded_file($file['tmp_name'], $upload_dir . $id . '.jpg')) {
								Logger::debug('saved file to disk successfully.');
							}
						break;
						default:
							//exit();
						break;
					}
				}
				//Logger::debug('document _id: ' . (string)$document->_id);
				
				
				if($document->save($data)) {
					Logger::debug('saved mongo document successfully.');
				}
			}
			
        }
        
        //$this->set(compact('document'));
		
	}
	
	/**
	 * The manage method serves as both an index listing of all galleries as well
	 * as a management tool for items within the gallery.
	 * 
	 * If no $id is passed, then an indexed listing of galleries (with links) will appear.
	 * Clicking on one of these listed galleries will then return to this method with
	 * and $id value present.
	 * 
	 * When an $id is present, the user will be able to add existing gallery items to 
	 * the gallery as well as upload new gallery items to be associated with the gallery.
	 * 
	 * @param string $id The Page id for the gallery
	 * @return
	 */
	public function manage($id=null) {
		// TODO: Use Minerva's access system (li3_access) 
		// Bigger todo: update li3_acess (Nate's changes) and redo Minerva's access system completely.
		$user = Auth::check('minerva_user');
		if($user['role'] != 'administrator' && $user['role'] != 'content_editor') {
			$this->redirect('/');
			return;
		}
		
		if(empty($id)) {
			// Default options for pagination, merge with URL parameters
			$defaults = array('page' => 1, 'limit' => 10, 'order' => 'created.desc');
			$params = Set::merge($defaults, $this->request->params);

			if((isset($params['page'])) && ($params['page'] == 0)) {
				$params['page'] = 1;
			}
			list($limit, $page, $order) = array($params['limit'], $params['page'], $params['order']);

			// Never allow a limit of 0
			$limit = ($limit < 0) ? 1:$limit;
			
			// Only pull back minerva_gallery pages
			$conditions = array('document_type' => 'minerva_gallery');
			
			// For search queries
			if((isset($this->request->query['q'])) && (!empty($this->request->query['q']))) {
				$search_schema = Page::searchSchema();
				$search_conditions = array();
				// For each searchable field, adjust the conditions to include a regex
				foreach($search_schema as $k => $v) {
					$search_regex = new \MongoRegex('/' . $this->request->query['q'] . '/i');
					$conditions['$or'][] = array($k => $search_regex);
				}
			}

			// Get the documents and the total
			$documents = Page::find('all', array(
				'conditions' => $conditions,
				'limit' => (int)$limit,
				'offset' => ((int)$page - 1) * (int)$limit,
				'order' => $params['order']
			));

			$total = Page::find('count', array(
				'conditions' => $conditions
			));

			$page_number = (int)$page;
			$total_pages = ((int)$limit > 0) ? ceil($total / $limit):0;
			
			// Use the manage_index template in this case
			$this->_render['template'] = 'manage_index';
			
			// Set data for the view template
			$this->set(compact('documents', 'limit', 'page_number', 'total_pages', 'total'));
		} else {
			// Only pull the latest 30 gallery items from the entire system...
			// Because it's reasonable. There could be thousands of items and paging 
			// through is an option, but not practical for the design and user experience.
			// 30 of the latest is enough and the user can make a search to find what 
			// they are after. The point of this listing of items is to allow the user to
			// associate an existing item in the system with the current gallery. 
			// It's not going to be as common as adding brand new items instead.
			// Well, unless the user really goes back to share items across multiple 
			// galleries on a regular basis...I don't think it's common, but possible.
			// So showing 30 plus a search is plenty.
			$conditions = array('published' => true, '_galleries' => array('$nin' => array($id)));
			
			// For search queries for items
			if((isset($this->request->query['q'])) && (!empty($this->request->query['q']))) {
				$search_schema = Item::searchSchema();
				$search_conditions = array();
				// For each searchable field, adjust the conditions to include a regex
				foreach($search_schema as $k => $v) {
					$search_regex = new \MongoRegex('/' . $this->request->query['q'] . '/i');
					$conditions['$or'][] = array($k => $search_regex);
				}
			}
			// Find the unassociated gallery items
			$items = Item::find('all', array(
				'conditions' => $conditions,
				'limit' => 30,
				'order' => array('created' => 'desc')
			));
			
			// Find all items for the current gallery
			$gallery_items = Item::find('all', array('conditions' => array('_galleries' => $id)));
			
			// Find the gallery document itself
			$document = Page::find('first', array('conditions' => array('_id' => $id)));
			
			// Order those gallery items based on the gallery document's gallery_item_order field (if set)
			if(isset($document->gallery_item_order) && !empty($document->gallery_item_order)) {
				// This sort() method is the awesome.
				$ordering = $document->gallery_item_order->data();
				// data() must be called so that the iterator loads up all the documents...
				// Something that has to be fixed I guess. Then data() doesn't need to be called.
				$gallery_items->data();
				$gallery_items->sort(function($a, $b) use ($ordering) {
					if($a['_id'] == $b['_id']) {
					  return strcmp($a['_id'], $b['_id']);
					}
					$cmpa = array_search($a['_id'], $ordering);
					$cmpb = array_search($b['_id'], $ordering);
					return ($cmpa > $cmpb) ? 1 : -1;
				});
				
			}
			
			$this->set(compact('document', 'items', 'gallery_items'));
		}
	}	
	
	/**
	 * Gets/updates the meta data for an Item.
	 * This would include: title, description, tags, geo, etc.
	 * 
	 * This method is meant to be called via AJAX.
	 * 
	 * @param string $id The item MongoId
	 * @return JSON Resposne
	*/
	public function meta($id=null) {
		// TODO: Use Minerva's access system (li3_access) 
		// Bigger todo: update li3_acess (Nate's changes) and redo Minerva's access system completely.
		$user = Auth::check('minerva_user');
		if($user['role'] != 'administrator' && $user['role'] != 'content_editor') {
			$this->redirect('/');
			return;
		}
		
		// Set the response to return
		$response = array('success' => true);
		
		// If there was no item id provided
		if(empty($id)) {
			$response['success'] = false;
		}
		
		// Check to ensure that JSON was used to make the POST request
		if(!$this->request->is('json')) {
			$response['success'] = false;
		}
		
		// If we're ok so far (meaning we have an $id, the user is authorized, the and its a JSON request)...
		if($response['success'] === true) {	
			// If update...
			if(!empty($this->request->data)) {
				$now = new MongoDate();
				$data = array(
					'modified' => $now,
					'title' => $this->request->data['title'],
					'description' => $this->request->data['description'],
					'tags' => $this->request->data['tags'],
					// 'location' => $this->request->data['geo']
				);

				// Remove anything that's empty so we don't update the document with empty data since
				// different AJAX calls update some fields and not others. It's not just one update call.
				$data = array_filter($data);
				
				// Update
				$response['success'] = Item::update(
					array(
						'$set' => $data
					),
					array('_id' => $id),
					array('atomic' => false)
				);
			
			} 
		}
		
		// Always set the latest data regardless of why this method was called.
		$document = Item::find('first', array('conditions' => array('_id' => $id)));
		$response['data'] = $document->data();
		
		$this->render(array('json' => $response));
	}
	
	/**
	 * Removes/Adds the item from/to a gallery.
	 * 
	 * This method is meant to be called via AJAX.
	 * 
	 * @param string $action "remove" or "add" ("remove" "0" and "false" will all remove an item)
	 * @param string $id The item MongoId
	 * @param string $gallery_id The gallery MongoId
	 * @return JSON Resposne
	*/
	public function association($action='remove', $id=null, $gallery_id=null) {
		// TODO: Use Minerva's access system (li3_access) 
		// Bigger todo: update li3_acess (Nate's changes) and redo Minerva's access system completely.
		$user = Auth::check('minerva_user');
		if($user['role'] != 'administrator' && $user['role'] != 'content_editor') {
			$this->redirect('/');
			return;
		}
		
		// Set the response to return
		$response = array('success' => true);
		
		// If there was no item or gallery id provided
		if(empty($id) || empty($gallery_id)) {
			$response['success'] = false;
		}
		
		// If $action is anything other than remove, 0, or false, the association will be added.
		$remove = ($action == 'remove' || $action == '0' || $action == 'false') ? true:false;
		
		// Check to ensure that JSON was used to make the POST request
		if(!$this->request->is('json')) {
			$response['success'] = false;
		}
		
		// If we have what we need, update the item
		if($response['success'] === true) {
			if($remove) {
				$item_update_query = array('$pull' => array('_galleries' => $gallery_id));
			} else {
				$item_update_query = array('$addToSet' => array('_galleries' => $gallery_id));
			}
			
			$response['success'] = Item::update(
				$item_update_query,
				array('_id' => $id),
				array('atomic' => false)
			);
			
			// Also $pull the item id from the gallery's document ordering field
			// The success of this is less important because if for some reason it isn't updated,
			// it should straighten out later when items are re-ordered and it doesn't even matter
			// if it's dirty. This is because it's not an association, it's just an ordering and 
			// if an item doesn't exist in the order it will simply be ignored.
			if($remove) {
				$page_update_query = array('$pull' => array('gallery_item_order' => $id));
			} else {
				// If new association, the item will be added at the end of the order
				$page_update_query = array('$addToSet' => array('gallery_item_order' => $id));
			}
			
			Page::update(
				$page_update_query,
				array('_id' => $gallery_id),
				array('atomic' => false)
			);
			
		}
		
		$this->render(array('json' => $response));
	}
	
	/**
	 * Sets item order for a given gallery.
	 * 
	 * This method is meant to be called via AJAX.
	 * 
	 * @param string $gallery_id The gallery MongoId
	 * @return JSON Resposne
	*/
	public function order($gallery_id=null) {
		// TODO: Use Minerva's access system (li3_access) 
		// Bigger todo: update li3_acess (Nate's changes) and redo Minerva's access system completely.
		$user = Auth::check('minerva_user');
		if($user['role'] != 'administrator' && $user['role'] != 'content_editor') {
			$this->redirect('/');
			return;
		}
		
		// Set the response to return
		$response = array('success' => true);
		
		// If there was no gallery id provided
		if(empty($gallery_id)) {
			$response['success'] = false;
		}
		
		// Check to ensure that JSON was used to make the POST request
		if(!$this->request->is('json')) {
			$response['success'] = false;
		}
		
		// If we have what we need, update the item
		if($response['success'] === true) {
			if(isset($this->request->data['order']) && is_array($this->request->data['order'])) {
				$response['success'] = Page::update(
					array('$set' => array('gallery_item_order' => $this->request->data['order'])),
					array('_id' => $gallery_id),
					array('atomic' => false)
				);
			} else {
				$response['success'] = false;
			}
		}
		
		$this->render(array('json' => $response));
	}
	
	/**
	 * Returns a JSON feed of gallery items.
	 * 
	 * @param string $id The gallery id or URL
	 * @return string The gallery in JSON format
	*/
	public function feed($id=null) {
		$response = array('success' => true);
				
		if(empty($id)) {
			$response['success'] = false;
		}
		
		// Check to ensure that JSON was used to make the POST request
		if(!$this->request->is('json')) {
			$response['success'] = false;
		}
		
		if(preg_match('/[0-9a-f]{24}/', $id)) {
			$field = '_id';
		} else {
			$field = 'url';
		}
		
		if($response['success'] === true) {
			// Find the gallery document itself (by _id or url)
			$document = Page::find('first', array('conditions' => array($field => $id, 'published' => true)));
			if(empty($document)) {
				$response['success'] = false;
			}
			
			if($response['success'] === true) {
				// Find all items for the current gallery
				$gallery_items = Item::find('all', array('conditions' => array('_galleries' => (string)$document->_id)));

				// Order those gallery items based on the gallery document's gallery_item_order field (if set)
				if(isset($document->gallery_item_order) && !empty($document->gallery_item_order)) {
					// This sort() method is the awesome.
					$ordering = $document->gallery_item_order->data();
					// data() must be called so that the iterator loads up all the documents...
					// Something that has to be fixed I guess. Then data() doesn't need to be called.
					$gallery_items->data();
					$gallery_items->sort(function($a, $b) use ($ordering) {
						if($a['_id'] == $b['_id']) {
						  return strcmp($a['_id'], $b['_id']);
						}
						$cmpa = array_search($a['_id'], $ordering);
						$cmpb = array_search($b['_id'], $ordering);
						return ($cmpa > $cmpb) ? 1 : -1;
					});

				}
				$response['gallery'] = $document->data();
				$response['items'] = $gallery_items->data();
			}
		}
		$this->render(array('json' => $response));
	}
	
}
?>