$(document).ready(function() {
	$('a.edittrans').click(function() {
		// Affichage formulaire pour modifier la traduction
		$(this).parents('tr').find('div.customtrans').hide();
		$(this).parents('tr').find('div.formcustomtrans').show();
		$('a.edittrans, a.resettrans').hide();
	});
	
	$('a.resettrans').click(function() {
		// Masquage formulaire pour modifier la traduction
		$(this).parents('tr').find('textarea').val('');
		$(this).parents('tr').find('form').submit();
	});
});
