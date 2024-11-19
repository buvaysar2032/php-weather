/* eslint-disable jquery/no-val */
/* eslint-disable jquery/no-html */
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
    $.each(_opts.items[target], function () {
        var r = this;
        if (r.indexOf(q) >= 0) {
            $('<option>').text(r).val(r).appendTo($list);
        }
    });
}

$(function () {
    $('.btn-assign').on('click', function () {
        const $this = $(this);
        const target = $this.data('target');
        const routes = $('select.list[data-target="' + target + '"]').val();

        if (routes && routes.length) {
            $.post($this.attr('href'), { routes: routes }, function (r) {
                updateItems(r);
            });
        }
        return false;
    });

    $('#btn-refresh').on('click', function () {
        const $btn = $(this);
        $btn.attr("disabled", "disabled");

        $.post($(this).attr('href'), function (r) {
            updateItems(r);
        }).always(function () {
            $btn.removeAttr("disabled");
        });

        return false;
    });

    $('.search[data-target]').keyup(function () {
        search($(this).data('target'));
    });

    // initial
    search('available');
    search('assigned');
});
