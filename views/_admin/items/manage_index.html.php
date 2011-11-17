<?php $this->html->style('/minerva_gallery/css/galleries_admin.css', array('inline' => false)); ?>

<div class="grid_16">
	<h2 id="page-heading">All Galleries</h2>
</div>

<div class="clear"></div>

<div class="grid_12">
<?php 
if(empty($documents)) { 
	echo '<p>There are no galleries. ' . $this->html->link('Click here to create one.', array('admin' => 'admin', 'plugin' => 'minerva_gallery', 'library' => 'minerva', 'controller' => 'pages', 'action' => 'create')) . '</p>';
}

foreach($documents as $document) { ?>
	<?php
	$link_html = '<div class="gallery">';
		$link_html .= '<div class="gallery_thumbnail"></div>';
		$link_html .= '<div class="gallery_title">'. $document->title .'</div>';
	$link_html .= '</div>';
	echo $this->html->link($link_html, array('admin' => 'admin', 'library' => 'minerva_gallery', 'controller' => 'items', 'action' => 'manage', 'args' => array((string)$document->_id)), array('escape' => false)); 
	?>
<?php 
}
?>

	<div class="clear"></div>
	<br />
<?=$this->minervaPaginator->paginate($page_number, $total, $limit); ?>
<br />
<em>Showing page <?=$page_number; ?> of <?=$total_pages; ?>. <?=$total; ?> total record<?php echo ((int) $total > 1 || (int) $total == 0) ? 's':''; ?>.</em>
</div>

<div class="grid_4">
	<div class="box">
		<h2>Create New Gallery</h2>
		<div class="block">
			<p>
				<?=$this->html->link('Click here to create a gallery.', array('admin' => 'admin', 'plugin' => 'minerva_gallery', 'library' => 'minerva', 'controller' => 'pages', 'action' => 'create')); ?><br />
				After creating a new gallery, you will be able to come back here to manage the items displayed within it.
			</p>
		</div>
	</div>
	
	<div class="box">
		<h2>Search for Galleries</h2>
		<div class="block">
			<?=$this->minervaHtml->query_form(array('label' => 'Query ')); ?>
		</div>
	</div>
</div>

<div class="clear"></div>

<script type="text/javascript">
	$(document).ready(function() {
	});
</script>