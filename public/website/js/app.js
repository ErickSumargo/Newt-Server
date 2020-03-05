$(document).ready(function() {
    $(window).load(function(){
        $('.loader').fadeOut();
    });
    
    var duration = 2500;
    var current = 1;
    
    var container = $(".problem-container");
    var height = container.height();
    var number = container.children().length;
    var first = container.children().first();

    setInterval(function() {
        var interv = current * -1 * height;
        first.css("margin-top", interv + "px");
        if (current == number) {
          first.css("margin-top", "0px");
          current = 1;
        } else {
          current++;
        }
    }, duration);
});