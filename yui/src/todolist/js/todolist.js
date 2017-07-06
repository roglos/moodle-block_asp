var TODOLISTNAME = 'blocks_asp_todolist',
    AJAXURL = '/blocks/asp/ajax.php',
    STATEID = 'stateid',
    CSS = {
        BLOCKASP : 'block_asp',
        BLOCKTODOLIST : 'block_asp_todolist',
        BLOCKTODOTASK : 'block-asp-todotask',
        BLOCKTODOID : 'block-asp-todoid'
    };

var TODOLIST = function() {
    TODOLIST.superclass.constructor.apply(this, arguments);
};

Y.extend(TODOLIST, Y.Base, {

    initializer : function() {
        // Take each of the asp tasks, remove the anchor, and change it to
        // call our update function
        Y.all('a.' + CSS.BLOCKTODOTASK).each(function(node) {
            node.ancestor('li').on('click', this.toggle, this, node);
            node.setAttribute('href', '#');
        }, this);
    },

    toggle : function(e, node) {
        e.halt();

        // expression to fetch ID
        var reg = new RegExp(CSS.BLOCKTODOID + "-(\\d{1,})");
        // Build the data for submission
        var data = {
            sesskey : M.cfg.sesskey,
            action  : 'toggletaskdone',
            stateid : this.get(STATEID),
            todoid  : node.getAttribute('id').match(reg)[1]
        };

        // Send the query
        Y.io(M.cfg.wwwroot + AJAXURL, {
            method  : 'POST',
            data    : build_querystring(data),
            on : {
                complete : function(tid, outcome) {
                    var result;
                    try {
                        result = Y.JSON.parse(outcome.responseText);
                        if (result.error) {
                            return new M.core.ajaxException(result);
                        }
                    } catch (e) {
                        new M.core.exception(e);
                    }
                    if (result.response.iscompleted) {
                        node.get('parentNode').addClass('completed');
                    } else {
                        node.get('parentNode').removeClass('completed');
                    }
                }
            }
        });
    }

}, {
    NAME : TODOLISTNAME,
    ATTRS : {
        stateid: {
            value: null
        }
    }
});

M.blocks_asp = M.blocks_asp || {};
M.blocks_asp.init_todolist = function(params) {
    return new TODOLIST(params);
};
