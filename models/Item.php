<?php
namespace minerva_gallery\models;

class Item extends \lithium\data\Model {
	
	protected $_meta = array(
		'source' => 'gallery_items',
		'locked' => true
	);
	
	protected $_schema = array(
        '_id' => array('type' => 'id', 'form' => array('type' => 'hidden', 'label' => false)),
		'title' => array('type' => 'string'),
		'description' => array('type' => 'string'),
		'tags' => array('type' => 'array'),
		'location' => array('type' => 'array'),
		// TODO: the exif data can be pulled out with Agile Uploader...It just needs a ltitle revision
		// the class to read exif data should already be in Agile Uploader... 
		// So exif data can be stored upon upload.
		'exif' => array('type' => 'array'),
		// where the asset is located... could be in grid.fs or Amazon S3 or on disk somewhere
		'service' => array('type' => 'string'),
		// the source... could be a MongoId, could be a URL for S3, etc. or a path for a file on disk
		'source' => array('type' => 'string'),
		// the gallery id's this item belongs to
		'_galleries' => array('type' => 'array'),
        'created' => array('type' => 'date', 'form' => array('type' => 'hidden', 'label' => false)),
		'modified' => array('type' => 'date', 'form' => array('type' => 'hidden', 'label' => false)),
		'published' => array('type' => 'string')
    );
	
	public $url_separator = '-';
	
	public $search_schema = array(
		'title' => array(
			'weight' => 1
		)
	);
	
	/**
     * Returns the search schema for the model.
     * 
     * @param array Optional new search schema values
     * @return array
    */
    public static function searchSchema($schema=array()) {
		$class =  __CLASS__;
		$self = $class::_object();
		if(!empty($schema)) {
            $class::_object()->search_schema = $schema;
        }
		return $self->search_schema;
    }
}
?>