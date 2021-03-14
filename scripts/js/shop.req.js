<?php 
if ($user->user_id) {
	print jquery_post("order", 0, "shopping-cart", "Submit", "Submitting", "Your order has been submitted.", "location.href = '/shop';");
} 
?>

$(".cancel_order_button").click(function() {
	id = $(this).attr('data-id');
	$.ajax({
		url: "/ajax/actions.ajax.php?function=cancel_order&id="+id,
		success: function (data) {
			$("#message_container").html(data).show().delay(3000).fadeOut();
			location.reload();
		},
		cache: false,
		contentType: false,
		processData: false
	});	

	event.preventDefault();
});