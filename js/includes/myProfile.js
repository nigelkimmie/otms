(function ($) {
    var d = document.documentElement; d.className = d.className.replace(/no\-js/g,'');  
    
    var ie = document.all && !window.opera;
    $.fn.tabs = function () {
        this.each(function () {
            var tabs = $(this);
            var tabsContents = tabs.find('> .tabsBody');
            var tabsItems = tabsContents.find('> li');

            tabsContents.on('click keyup', '> li > .tabHeader', function (e) {
                e.preventDefault();
                if(e.type=='keyup' && e.which!=13) return;
                var index = tabsItems.index($(this).parents('li:first'));
                changeTabs(index);
            });
            
            function changeTabs(index) {
                tabsItems.removeClass('active').delay(ie ? 1 : 0).eq(index).addClass('active');
            }
        });
        return this;
    };
})(jQuery);

$(document).ready(function() {
    $('div.tabs').tabs();
});