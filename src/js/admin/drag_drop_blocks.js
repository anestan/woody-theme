import $ from 'jquery';

var clickDragButton = function(block, row) {
    var clone = block;

    block.addClass('dragging-layout');

    // Make block draggable
    block.draggable({
        disabled: false,
        drag: function(e, ui) {
            var blockPosY = block.offset().top;
            var rowPosY = row.offset().top;
            var maxHeight = row.innerHeight();

            if (blockPosY < rowPosY + 25 || blockPosY + 35 > rowPosY + maxHeight) {
                // Outside
                showDroppablePosition(block, blockPosY, row);
            } else {
                // Inside
            }
        },
        stop: function() {
            var blockPosY = block.offset().top;
            var rowPosY = row.offset().top;
            var maxHeight = row.innerHeight();

            // Remove droppable div
            if (blockPosY < rowPosY + 35 || blockPosY + 35 > rowPosY + maxHeight) {
                // Outside
                updateSection(block, blockPosY, row, clone);

            } else {
                // Inside : remove draggable actions
                $('.dragging-layout').removeClass('dragging-layout');
                block.removeAttr('style');
                row.find('.droppable-position').remove();
            }
        }
    });
};

$('#acf-group_5afd260eeb4ab .acf-field[data-name="section"] .acf-table tbody')
    .first()
    .children('.acf-row:not(.acf-clone)')
    .each(function() {
        var row = $(this);
        row.find('.acf-field-5b043f0525968 .values .layout').each(function() {
            var block = $(this);

            var controls = block.find('.acf-fc-layout-controls');
            controls.prepend('<a class="acf-icon small light dashicons dashicons-move" href="#" data-name="drag-layout"></a>');

            block.find('a[data-name="drag-layout"]').click(function() {
                clickDragButton(block, row);
            });
        });
    });

var updateSection = function(block, blockPosY, original_row, clone) {
    $('#acf-group_5afd260eeb4ab .acf-field[data-name="section"] .acf-table tbody')
        .first()
        .children('.acf-row:not(.acf-clone)')
        .each(function() {
            var row = $(this);

            if (row != original_row) {
                var rowPosY = row.offset().top;
                var maxHeight = row.innerHeight();

                if (blockPosY > rowPosY && blockPosY < (rowPosY + maxHeight)) {
                    block.remove();
                    row.find('.droppable-position').remove();

                    var row_id = row.data('id');
                    var layout_id = row.find('.acf-flexible-content').first().children('.values').children('.layout').last().data('id') + 1;

                    // Trigger add row add block;
                    var block_type = clone.attr('data-layout');
                    $.when(row.find('.acf-actions .acf-button[data-name="add-layout"]').trigger('click')).then(function() {
                        $.when($('.acf-tooltip.acf-fc-popup a[data-layout="' + block_type + '"]').trigger('click')).then(function() {
                            // Replace field values
                            var fields = clone.find('.acf-fields .acf-field');
                            var index = 0;
                            var new_block = row.find('.acf-flexible-content').first().children('.values').children('.layout').last();
                            new_block.find('.acf-fields .acf-field').each(function() {
                                $(this).replaceWith(fields[index]);
                                index++;
                            });

                            // Update block indexes
                            var updated_block = row.find('.acf-flexible-content').first().children('.values').children('.layout').last();
                            updated_block.find('[for]').each(function() {
                                var old = $(this).attr('for');
                                var regex = /acf-field_5afd2c6916ecb-[0-9]+-field_5b043f0525968-[A-Za-z0-9]+/;
                                if (old.match(regex)) {
                                    var new_for = old.replace(regex, 'acf-field_5afd2c6916ecb-' + row_id + '-field_5b043f0525968-' + layout_id);
                                    $(this).attr('for', new_for);
                                }
                            });

                            updated_block.find('[id]').each(function() {
                                var old = $(this).attr('id');
                                var regex = /acf-field_5afd2c6916ecb-[0-9]+-field_5b043f0525968-[A-Za-z0-9]+/;
                                if (old.match(regex)) {
                                    var new_id = old.replace(regex, 'acf-field_5afd2c6916ecb-' + row_id + '-field_5b043f0525968-' + layout_id);
                                    $(this).attr('id', new_id);
                                }
                            });

                            updated_block.find('[name]').each(function() {
                                var old_data_name = $(this).attr('name');
                                var regex = /acf\[field_5afd2c6916ecb\]\[[0-9]+\]\[field_5b043f0525968\]\[[A-Za-z0-9]+\]/;
                                if (old_data_name.match(regex)) {
                                    var new_name = old_data_name.replace(regex, 'acf[field_5afd2c6916ecb][' + row_id + '][field_5b043f0525968][' + layout_id + ']');
                                    $(this).attr('name', new_name);
                                }
                            });

                            updated_block.data('id', layout_id);

                            // Re-add dragging control
                            var controls = updated_block.find('.acf-fc-layout-controls');
                            controls.prepend('<a class="acf-icon small light dashicons dashicons-move" href="#" data-name="drag-layout"></a>');

                            updated_block.find('a[data-name="drag-layout"]').click(function() {
                                clickDragButton(updated_block, row);
                            });

                            if (original_row.find('.values .layout').length < 1) {
                                original_row.find('.no-value-message').css("display", 'block');
                            }
                        });
                    });
                }
            }
        });
}

var showDroppablePosition = function(block, blockPosY, original_row) {
    $('#acf-group_5afd260eeb4ab .acf-field[data-name="section"] .acf-table tbody')
        .first()
        .children('.acf-row:not(.acf-clone)')
        .each(function() {
            var row = $(this);

            if (row.data('id') != original_row.data('id')) {
                var rowPosY = row.offset().top;
                var maxHeight = row.innerHeight();

                if (blockPosY > rowPosY && blockPosY < (rowPosY + maxHeight)) {
                    if (row.find('.droppable-position').length == 0) {
                        row.find('.acf-flexible-content').first().children('.values').append('<div class="droppable-position">Drop me here !</div>')
                    }
                } else {
                    // REMOVE DROPPABLE BLOCK
                    row.find('.droppable-position').remove();
                }
            }
        });
}
