/**
 * Created by alvarobanofos on 5/8/16.
 */

var node = function()
{
    var data;
    var next = null;
};

var stack = function()
{
    this.top = null;

    this.push = function(data) {
        if (this.top == null) {
            this.top = new node();
            this.top.data = data;
        } else {
            var temp = new node();
            temp.data = data;
            temp.next = this.top;
            this.top = temp;
        }
    };

    this.pop = function() {
        var temp = this.top;
        if(temp == null) {
            return null;
        }
        var data = this.top.data;
        this.top = this.top.next;
        temp = null;
        return data;
    };

    this.print = function() {
        var node = this.top;
        while (node != null) {
            console.log(node.data);
            node = node.next;
        }
    };
};