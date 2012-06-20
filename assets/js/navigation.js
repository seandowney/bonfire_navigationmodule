	$( ".sortable" ).sortable({
		update: update_order,
		placeholder: "ui-state-highlight"
	}).disableSelection();

function update_order(event, ui) {
	order = new Array();
	$('tr', this).each(function(){
		order.push( $(this).find('input[name="checked[]"]').val() );
	});
	order = order.join(',');

	$.post('/admin/content/navigation/ajax_update_positions', { order: order }, function() {
		$('tr').removeClass('alt');
		$('tr:even').addClass('alt');
	});
}