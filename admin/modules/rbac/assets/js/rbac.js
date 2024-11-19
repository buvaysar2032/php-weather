/* eslint-disable no-undef */
/* eslint-disable jquery/no-html */
/* eslint-disable jquery/no-val */
/* eslint-disable jquery/no-each */
/* eslint-disable jquery/no-text */
/* eslint-disable jquery/no-ready */
/* eslint-disable jquery/no-data */
/* eslint-disable jquery/no-attr */
/* eslint-disable jquery/no-ajax */

function updateItems(r) {
    _opts.items.available = r.available;
    _opts.items.assigned = r.assigned;
    search('available');
    search('assigned');
}

function search(target) {
    const $list = $('select.list[data-target="' + target + '"]');
    $list.html('');
    const q = $('.search[data-target="' + target + '"]').val();

    const groups = {
        role: [$('<optgroup label="Roles">'), false],
        permission: [$('<optgroup label="Permission">'), false],
        route: [$('<optgroup label="Routes">'), false],
    };
    $.each(_opts.items[target], function (name, group) {
        if (name.indexOf(q) >= 0) {
            $('<option>').text(name).val(name).appendTo(groups[group][0]);
            groups[group][1] = true;
        }
    });
    $.each(groups, function () {
        if (this[1]) {
            $list.append(this[0]);
        }
    });
}

$(function () {
    $('.btn-assign').on('click', function (event) {
        event.stopPropagation();
        const $this = $(this);
        const target = $this.data('target');
        const items = $('select.list[data-target="' + target + '"]').val();

        if (items && items.length) {
            $.post($this.attr('href'), { items: items }, function (r) {
                updateItems(r);
            });
        }
        return false;
    });

    $('.search[data-target]').on('keyup', function () {
        search($(this).data('target'));
    });
    // initial
    search('available');
    search('assigned');
})