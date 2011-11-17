<?php $this->html->style(array('/minerva_gallery/css/galleries_admin.css', '/minerva_gallery/css/agile-uploader.css'), array('inline' => false)); ?>
<?php echo $this->html->script(array('/minerva_gallery/js/date.js', '/minerva_gallery/js/jquery/jquery.flash.min.js', '/minerva_gallery/js/jquery/agile-uploader-3.0.js', '/minerva_gallery/js/gallery_management.js'), array('inline' => true)); ?>

<div class="grid_16">
	<h2 id="page-heading">Manage <?=$document->title; ?></h2>
</div>

<div class="clear"></div>

<div class="grid_10 gallery_items" id="gallery_<?=$document->_id; ?>">
	<?php 
	if(empty($gallery_items)) {
		echo '<p>This gallery has no images.</p>';
	}
	foreach($gallery_items as $item) { 
	?>
		<div class="gallery_item" id="gallery_item_<?=$item->_id; ?>">
			<div class="gallery_item_image_wrapper">
				<div class="gallery_item_image">
					<?php // $this->html->image($item->source, array('alt' => $item->title)); ?>
					<?php echo $this->Preview->image($item->source, array('size' => array(175,175), 'crop' => true), array('alt' => $item->title, 'id' => 'image_for_' . $item->_id)); ?>
				</div>
			</div>
			<div class="gallery_item_info">
				<?php $title = (strlen($item->title) > 27) ? substr($item->title, 0, 27) . '...':$item->title; ?>
				<h3 id="title_for_<?=$item->_id; ?>"><?=$title; ?></h3>
			</div>
			<div class="gallery_item_actions">
				<?=$this->html->link('Remove', '#', array('class' => 'remove', 'rel' => (string) $item->_id,  'title' => 'Remove from this gallery')); ?>
				<?=$this->html->link('Edit', '#', array('class' => 'edit', 'rel' => (string) $item->_id, 'title' => 'Edit item information')); ?>
				<?php 
				/* This is GLOBAL visibility... So may want to re-think how "publish" works... 
				 * What if this item was in multiple galleries? But the user only want it temporarily not displayed on the current?
				<?php $publish_status = ($item->published) ? 'published':'unpublished'; ?>
				<?=$this->html->link($publish_status, '#', array('class' => 'publish_status ' . $publish_status, 'title' => 'Change visibility')); ?>
				 * 
				 * Also disable keyword tagging and geo tagging links for now...we can add that later
				<?=$this->html->link('Tags', '#', array('class' => 'tags', 'rel' => (string) $item->_id, 'title' => 'Tags for this item')); ?>
				<?=$this->html->link('Geo', '#', array('class' => 'location', 'rel' => (string) $item->_id, 'title' => 'Plot this item on a map')); ?>
				*/
				?>
			</div>
		</div>
	<?php 
	} 
	?>
</div>

<div class="grid_6">
	<div class="box">
		<h2>Gallery Information</h2>
		<div class="block">
			<!--
			<p><strong>Created</strong><br /><?=$this->minervaTime->to('nice', $document->created); ?></p>
            <p><strong>Modified</strong><br /><?=$this->minervaTime->to('nice', $document->modified); ?></p>
            <p><strong>Published</strong><br /><?=($document->published) ? 'Yes':'No'; ?></p>
			-->
			<p><?=$this->html->link('View gallery JSON feed.', array('library' => 'minerva_gallery', 'controller' => 'items', 'action' => 'feed', 'type' => 'json', 'args' => array($document->url)), array('target' => '_blank')); ?></p>
			<p><?=$this->html->link('Edit overall gallery information.', array('admin' => $this->minervaHtml->admin_prefix, 'plugin' => 'minerva_gallery', 'library' => 'minerva', 'controller' => 'pages', 'action' => 'update', 'args' => array($document->url))); ?></p>
		</div>
	</div>
	
	<div class="box">
		<h2>Add New Media</h2>
		<div class="block">
			<p>You can upload images and video files to be used in this gallery.</p>
			<form id="upload-new-items" enctype="multipart/form-data">
				<input type="hidden" name="gallery_id" value="<?=$document->_id; ?>" />
			</form>
			<div id="agile_uploader"></div>
			<button class="upload_files" onClick="document.getElementById('agileUploaderSWF').submit();">Upload</button>
			<div class="clear"></div>
		</div>
	</div>

	<div class="box">
		<h2>Add Existing Media</h2>
		<div class="block">
			<p>
				You can also add other items in the system, possibly from another gallery, to this gallery as well. You can search too.
				<?=$this->minervaHtml->query_form(array('label' => false)); ?>
			</p>
			<div class="unassociated_gallery_items">
				<?php 
				$i=1;
				foreach($items as $item) { 
					$alt_class = ($i % 2 == 0) ? ' alt':'';
				?>
					<div class="unassociated_gallery_item<?=$alt_class; ?>" id="gallery_item_<?=$item->_id; ?>">
							<div class="unassociated_gallery_item_image">
								<?php echo $this->Preview->image($item->source, array('size' => array(50,50), 'crop' => true), array('alt' => $item->title, 'id' => 'image_for_' . $item->_id)); ?>
							</div>
							<div class="unassociated_gallery_info_wrapper">
								<div class="unassociated_gallery_item_info">
									<?php $title = (strlen($item->title) > 37) ? substr($item->title, 0, 37) . '...':$item->title; ?>
									<h3 id="title_for_<?=$item->_id; ?>"><?=$title; ?></h3>
								</div>
							</div>
							<?=$this->html->link('+', '#', array('class' => 'add_unassociated_item', 'rel' => (string) $item->_id,  'title' => 'Add item to this gallery')); ?>
					</div>
				<?php 
				$i++;
				} 
				?>
			</div>
			<div class="clear"></div>
		</div>
	</div>
</div>

<div class="clear"></div>

<div id="edit_gallery_item_modal" class="modal" title="Edit Item">
	<div id="edit_gallery_item_thumbnail"></div>
	<?=$this->form->create(null, array('action' => '#', 'id' => 'edit_item_form')); ?>
		<?=$this->form->field('item_id', array('wrap' => array('class' => 'modal_input'), 'type' => 'hidden', 'label' => false, 'id' => 'edit_item_id')); ?>
		<?=$this->form->field('title', array('wrap' => array('class' => 'modal_input'))); ?>
		<?=$this->form->field('description', array('wrap' => array('class' => 'modal_input'), 'type' => 'textarea')); ?>
		
		<div class="last_update">
			<p>Last updated: <span id="edit_gallery_item_last_update"></span></p>
		</div>
		<div class="submit_edit">
			<?=$this->html->link('cancel', '#', array('onClick' => '$("#edit_gallery_item_modal").dialog("close"); return false;')); ?>
			<?=$this->form->submit('Save'); ?>
		</div>
	<?=$this->form->end(); ?>
</div>

<script type="text/javascript">
$(document).ready(function() {
	// Agile Uploader
	enableAgileUploader('<?=$document->_id; ?>');
	
	// Enable sorting
	enableSorting();
	
	// Helpful tooltips
	$('a').tipsy({gravity: 's'});
	
	// Add an Unassociated Item to Gallery
	$('a.add_unassociated_item').click(function() { 
		var item_id = $(this).attr('rel');
		addUnassociatedItem(item_id, '<?=$document->_id; ?>');
		return false;
	});
	
	// Remove Item From Gallery
	$('a.remove').click(function() {
		var item_id = $(this).attr('rel');
		removeItemFromGallery(item_id, '<?=$document->_id; ?>');
		return false;
	});
	
	// Edit Gallery Item
	$('a.edit').click(function() {
		var item_id = $(this).attr('rel');
		editGalleryItem(item_id);
		return false;
	});
	
});
</script>
<style type="text/css">
.ui-corner-all { border-radius: 0px; -moz-border-radius-bottomright: 0px; -moz-border-radius-bottomleft: 0px; -moz-border-radius-topright: 0px; -moz-border-radius-topleft: 0px;}
</style>