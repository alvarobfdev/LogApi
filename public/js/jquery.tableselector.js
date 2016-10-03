/**
 * Created by alvarobanofos on 6/8/16.
 */


(function ( $ ) {

    var selector = null;
    var maxRows = 0;
    var selectedRow = 0;
    var activated = false;
    var keyDownEvent = null;
    var table = null;
    var timeout = null;
    var settings = null;

    $.fn.tableSelector = function(options) {

        this.tableSelector.destroy();
        table = this;
        settings = $.extend({
            // These are the defaults.
            backgroundColor: "white",
            accessRowFunction:function() {}
        }, options );

        rebuildTable();
    };


    $.fn.tableSelector.destroy = function() {
        $('.cellSelectorIcon').remove();
        if(table!=null) {
            $('body').off('keydown', keyDownHandler);
            clearTimeout(timeout);
        }
    };

    function rebuildTable() {
        var cssProperties = {
            border:"none",
            "background-color":settings.backgroundColor,
            width: "25px"
        };

        maxRows = table.find('tbody tr').size();
        table.find("tr").prepend(function() {
            var cell = $('<td class="cellSelectorIcon"></td>');
            cell.css(cssProperties);
            return cell;
        });
        selector = table.find("tbody tr:first td:first");
        selector.html('&#9658');
        selectedRow = 0;
        parpadeo();
        $('body').on('keydown', keyDownHandler);
    }

    function parpadeo() {
        if(selector.html() == '')
            selector.html('&#9658');
        else selector.html('');
        timeout = setTimeout(parpadeo, 500);

    }

    function keyDownHandler(e){
        var lastSelector = selector;

        if(e.keyCode == 40) { //down arrow
            e.preventDefault();
            if(selectedRow+1 < maxRows) {
                selectedRow++;
                selector = table.find("tbody tr:eq("+selectedRow+") td:first");
                lastSelector.html('');
            }
        }
        if(e.keyCode == 38) { //down arrow
            e.preventDefault();
            if(selectedRow-1 >= 0) {
                selectedRow--;
                selector = table.find("tbody tr:eq("+selectedRow+") td:first");
                lastSelector.html('');
            }
        }

        if(e.keyCode == 13) { //Enter key
            e.preventDefault();
            var row = table.find('tbody tr:eq('+selectedRow+')');
            settings.accessRowFunction(row);
        }
     }


}( jQuery ));