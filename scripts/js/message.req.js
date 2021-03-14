<?php if ($user->user_id) { ?>

$(".emoji-toggle").click(function(){
	$(this).parent().parent().next(".emojis").toggle();
});

$('.btn-spoiler').click(function(evt){
    evt.preventDefault();
    $(this).next('.spoiler').toggle();
});

<?= display_js_posting() ?>

<?= jquery_post('msg_reply', $id, 'comment', 'Reply', 'Replying', 'You have replied to this conversation.', 'location.reload();'); ?>

    var total_msgs = $('tr.post').length;
    var page = 1;
    $('#msg_more_button').click(function() {

        $(this).attr('disabled', 'disabled');

        var formData = new FormData();
        formData.append('id', $(this).attr('data-thread-id'));
        formData.append('page', ++page);

        var self = this;

        $.ajax({
            url: '/ajax/actions.ajax.php?function=msg_thread',
            type: 'POST',
            data: formData,
            cache: false,
            headers: {'cache-control': 'no-cache'},
            contentType: false,
            processData: false,
            async: true,
            dataType: "json",
            success: function (data) {
                $(self).removeAttr('disabled');
                if (data.status == 'success' && data.count > 0) {
                    $('tbody#msg_container').prepend(data.data);
                    total_msgs = total_msgs + data.count;
                } else if (data.status == 'fail') {
                    $('#message_container').html(data.message).show().delay(3000).fadeOut();
                }
                if (total_msgs >= data.total) {
                    // Remove load more button
                    $('#msg_more_container').remove();
                }
            }
        });

    });

<?php } ?>