
<div class="view split-view">
	
	<!-- Role List -->
	<div class="view">
	
	<?php if (isset($records) && is_array($records) && count($records)) : ?>
		<div class="scrollable">
			<div class="list-view" id="role-list">
				<?php foreach ($records as $record) : ?>
					<?php $record = (array)$record;?>
					<div class="list-item" data-id="<?php echo $record['nav_id']; ?>">
						<p>
							<b><?php echo $record['title']; ?></b><br/>
							<span class="small">URL:<?php echo $record['url']; ?><br />
							GROUP:
							<?php if (sizeof($record['groups'])==1): echo $groups[$record['groups'][0]]->title; ?>
							<?php else: echo "Multiple"; endif;?></span>
						</p>
					</div>
				<?php endforeach; ?>
			</div>	<!-- /list-view -->
		</div>
	
	<?php else: ?>
	
	<div class="notification attention">
		<p><?php echo lang('navigation_no_records'); ?> <?php echo anchor(SITE_AREA.'/content/navigation/create', lang('navigation_create_new'), array("class" => "ajaxify")) ?></p>
	</div>
	
	<?php endif; ?>
	</div>
	<!-- Role Editor -->
	<div id="content" class="view">
		<div class="scrollable" id="ajax-content">
				
			<div class="box create rounded">
				<a class="button good ajaxify" href="<?php echo site_url(SITE_AREA.'/content/navigation/create')?>"><?php echo lang('navigation_create_new_button');?></a>

				<h3><?php echo lang('navigation_create_new');?></h3>

				<p><?php echo lang('navigation_edit_text'); ?></p>
			</div>
			<br />
<?php if (isset($records) && is_array($records) && count($records)) : ?>
			<h2>Navigation</h2>
			
			<?php if (isset($groups_have_records) && is_array($groups_have_records) && count($groups_have_records)) :?>
				<?php foreach($groups_have_records as $group): ?>
				
			<div>
				<h3><?php echo $groups[$group]->title;?></h3>
				<table>
					<thead>
					<th>Title</th>
					<th>URL</th>
					<th>Parent</th>
					</thead>
					<tbody class="sortable">
					
					<?php foreach($records as $record): ?>
						<?php if(in_array($group, $record->groups)):?>
						
						<tr>
							<td><?php echo form_hidden('action_to[]', $record->nav_id); ?><?php echo anchor(SITE_AREA.'/content/navigation/edit/'. $record->nav_id, $record->title, 'class="ajaxify"') ?></td>
							<td><?php echo $record->url;?></td>
							<td><?php echo $record->parent_id != 0 && isset($records[$record->parent_id]->title) ? $records[$record->parent_id]->title : '';?></td>
						</tr>
						
						<?php endif;?>
					<?php endforeach; ?>
					
					</tbody>
				</table>
				</div>
				
				<?php endforeach; ?>
			<?php endif;?>
<?php endif; ?>
				
		</div>	<!-- /ajax-content -->
	</div>	<!-- /content -->
</div>
