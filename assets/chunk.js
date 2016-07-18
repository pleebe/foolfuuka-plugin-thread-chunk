function doit() {
    $(document).ready(function () {
        //console.log('ready');
        $('.chunknext').click(function (e) {
            e.preventDefault();
            var el = $('.chunknext');
            el.html("Loading...");
            $.ajax({
                url: el.attr('href'),
                type: 'GET',
                success: function (data, textStatus, jqXHR) {
                    var comments = $(data).find('aside.posts').html();
                    var newtext = $(data).find('.threadchunk').html();
                    $(".pagecontainer").append('<article class="thread"><aside class="posts">' + comments + '</aside></article>');
                    $('.threadchunk').html(newtext);
                    doit();
                }
            })
        })
    });
}
//console.log('not ready');
setTimeout(function() {
    doit();
}, 1000);