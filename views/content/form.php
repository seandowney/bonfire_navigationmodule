<?php if (validation_errors()) : ?>
<div class="notification error">
	<?php echo validation_errors(); ?>
</div>
<?php endif; ?>

<div class="admin-box">

    <h3><?php echo lang('navigation_heading'); ?></h3>

    <?php echo form_open($this->uri->uri_string(), 'class="form-horizontal"'); ?>

    <fieldset>

		<div class="control-group <?php echo form_error('title') ? 'error' : '' ?>">
			<label class="control-label"><?php echo lang('navigation_title_label') ?></label>
			<div class="controls">
				<input id="title" type="text" name="title" maxlength="30" value="<?php echo set_value('title', isset($navigation->title) ? $navigation->title : ''); ?>"  />
				<span class="help-inline"><?php if (form_error('title')) echo form_error('title'); else echo lang('navigation_title_info'); ?></span>
			</div>
		</div>

		<div class="control-group <?php echo form_error('url') ? 'error' : '' ?>">
			<label class="control-label"><?php echo lang('navigation_url_label') ?></label>
			<div class="controls">
				<input id="url" type="text" name="url" maxlength="150" value="<?php echo set_value('url', isset($navigation->url) ? $navigation->url : ''); ?>"  />
				<span class="help-inline"><?php if (form_error('url')) echo form_error('url'); else echo lang('navigation_url_info'); ?></span>
			</div>
		</div>

		<?php echo form_dropdown("nav_group_id", $groups, set_value('nav_group_id', isset($navigation->nav_group_id) ? $navigation->nav_group_id : ''), lang('navigation_group_label'), array("id" => "nav_group_id"));?>

		<?php echo form_dropdown("parent_id", $parents, set_value('parent_id', isset($navigation->parent_id) ? $navigation->parent_id : ''), lang('navigation_parent_label'), array("id" => "parent_id"));?>

	</fieldset>
	
	<div class="form-actions">
		<input type="submit" name="submit" class="btn btn-primary" value="<?php echo lang('bf_action_save'); ?>" />
	</div>
	
	<?php echo form_close(); ?>
</div>