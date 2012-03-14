<?php if (validation_errors()) : ?>
<div class="notification error">
	<?php echo validation_errors(); ?>
</div>
<?php endif; ?>

<?php // Change the css classes to suit your needs    
if( isset($navigation) ) {
	$navigation = (array)$navigation;
}
$id = isset($navigation['nav_id']) ? "/".$navigation['nav_id'] : '';
?>
<div class="admin-box">

    <h3>Navigation</h3>

    <?php echo form_open($this->uri->uri_string(), 'class="form-horizontal"'); ?>

    <fieldset>
        <legend></legend>
        <div class="control-group <?php echo form_error('title') ? 'error' : '' ?>">
            <label><?php echo lang('navigation_title_label') ?></label>
            <div class="controls">
                <input id="title" type="text" name="title" maxlength="30" value="<?php echo set_value('title', isset($navigation['title']) ? $navigation['title'] : ''); ?>"  />
				<span class="help-inline"><?php if (form_error('title')) echo form_error('title'); else echo lang('navigation_title_info'); ?></span>
            </div>
        </div>
	
		<div class="control-group <?php echo form_error('url') ? 'error' : '' ?>">
            <label><?php echo lang('navigation_url_label') ?></label>
            <div class="controls">
                <input id="url" type="text" name="url" maxlength="150" value="<?php echo set_value('url', isset($navigation['url']) ? $navigation['url'] : ''); ?>"  />
				<span class="help-inline"><?php if (form_error('url')) echo form_error('url'); else echo lang('navigation_url_info'); ?></span>
            </div>
        </div>
	
		<div class="control-group <?php echo form_error('nav_group_id') ? 'error' : '' ?>">
            <label><?php echo lang('navigation_group_label') ?></label>
            <div class="controls">
               <?php echo form_dropdown("nav_group_id", $groups, isset($navigation['nav_group_id']) ? $navigation['nav_group_id'] : '', array("id" => "nav_group_id"));?>
				<span class="help-inline"><?php if (form_error('nav_group_id')) echo form_error('nav_group_id'); else echo lang('navigation_group_label'); ?></span>
            </div>
        </div>
	
		<div class="control-group <?php echo form_error('parent_id') ? 'error' : '' ?>">
            <label><?php echo lang('') ?></label>
            <div class="controls">
                <?php echo form_dropdown("parent_id", $parents, isset($navigation['parent_id']) ? $navigation['parent_id'] : '', array("id" => "parent_id"));?>
			<span class="help-inline"><?php if (form_error('parent_id')) echo form_error('parent_id'); else echo lang('navigation_parent_info'); ?></span>
           </div>
        </div>
		
	</fieldset>
	
	<div class="text-right">
			<div class="form-actions">
			<input type="submit" name="submit" class="btn primary" value="<?php echo lang('bf_action_save') ?> " /> <?php echo lang('bf_or') ?> <?php echo anchor(SITE_AREA .'/settings', lang('bf_action_cancel')); ?>
		</div>
	</div>

	<?php echo form_close(); ?>
</div>