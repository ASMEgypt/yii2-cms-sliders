$(function() {

    // Fancybox
    $('.fancybox').fancybox();

    var growlTemplate =
        '<div id="w0" class="alert col-xs-10 col-sm-10 col-md-3"><button type="button" class="close" data-growl="dismiss"><span aria-hidden="true">&times;</span></button>' +
        '<span data-growl="icon"></span>' +
        '<span data-growl="title"></span>' +
        '<span data-growl="message"></span>' +
        '<a href="#" data-growl="url"></a></div>';

    // Set eventhandlers
    $(document)
        .on('click', '.select-on-check-all', toggleCheckboxes)
        .on('click', '#gridview-container .kv-row-select input', toggleSelectAll);

    $(document).on('click', '#batch-delete', function (event) {
        event.preventDefault();

        //var ids = $('#gridview-container').yiiGridView('getSelectedRows');
        var ids = [];

        $('#gridview-container').find("input[name='selection[]']:checked").each(function () {
            ids.push($(this).parent().closest('tr').data('key'));
        });

        // @todo Remove first ajax request and translate in javascript (available in version 2.1)
        $.ajax({
            url: 'multiple-delete-confirm-message',
            type: 'POST',
            data: {
                'ids': ids.length
            },
            success: function (message) {

                bootbox.confirm(message, function (confirmed) {
                    if (confirmed) {

                        $.ajax({
                            url: 'multiple-delete',
                            type: 'POST',
                            data: {
                                'ids': ids
                            },
                            success: function (data) {

                                if (data.status == 1) {
                                    // Disable delete button
                                    $('#batch-delete').attr('disabled', true);

                                    // Success
                                    $.pjax.reload({container: '#grid-pjax'});

                                    // @todo Update code
                                    $.growl({
                                        message: ' ' + data.message,
                                        icon: 'glyphicon glyphicon-ok-sign'
                                    }, {
                                        type: 'success',
                                        class: 'alert col-xs-10 col-sm-10 col-md-3',
                                        template: growlTemplate

                                    });

                                } else {
                                    // @todo Do somehting

                                    // Fail
                                    console.log('fail');
                                }
                            }
                        });
                    }
                });
            }
        });
    });

    $("#sortable").sortable({
        placeholder: "sortable-container col-xs-3 col-sm-2 ui-state-highlight",
        forcePlaceholderSize: true,
        delay: 150,
        distance: 5,
        //grid: [ 270, 270 ],
        //handle: ".handle",
        helper: "clone",
        items: '.handle',
        opacity: 0.5,
        sort: false,
        tolerance: "pointer",
        cursor: "move",
        containment: '#sortable',
        update: function (event, ui) {
            //var icon = '<img src="../../admin/images/icons/loading.gif" alt="Loading" title="Loading" />';
            //$('#icon').html(icon);

            var ids = $('#sortable').sortable('toArray');

            $.ajax({
                url: 'sort-pictures',
                type: 'POST',
                data: {ids: ids},
                dataType: 'json',
                success: function (data) {
                    if (data.status == 1) {

                        // @todo Update code
                        $.growl({
                            message: ' ' + data.message,
                            icon: 'glyphicon glyphicon-ok-sign'
                        }, {
                            type: 'success',
                            class: 'alert col-xs-10 col-sm-10 col-md-3',
                            template: growlTemplate

                        });

                        //var icon = '<img src="../../admin/images/icons/tick.png" alt="Saved" title="Saved" />';
                        //$('#icon').html(icon);
                    }
                }
            });
        }
    }).disableSelection();
});

function toggleCheckboxes(e) {
    // Check / uncheck all checkboxes
    $('#gridview-container .kv-row-select input').prop('checked', ($(this).is(':checked')) ? true : false);
    
    toggleDeleteBtn();    
}

function toggleSelectAll(e) {
    // If one checkbox is not checked, the "select-all" checkbox should also be no longer checked
    if (!$(this).is(':checked'))
        $('.select-on-check-all').prop('checked', false);
        
    toggleDeleteBtn();        
}

function toggleDeleteBtn() {

    // If at least one checkbox is checked the delete button has to be shown
    if ($('#gridview-container .kv-row-select input:checked').length || $('.select-on-check-all:checked').length) {
        $('#batch-delete').show();    
    } else {
        $('#batch-delete').hide();
    }     
}