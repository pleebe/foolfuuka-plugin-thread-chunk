function doit() {
    $(document).ready(function () {
        $('.chunknext').click(function (e) {
            var el = $('.chunknext');
            var htmlclass = $('body').attr('class');
            if (htmlclass == null) {
                // jquery has issues with fuuka theme so we just redirect instead
                window.location.replace(el.attr('href'));
            } else {
                e.preventDefault();
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
            }
        })
    });
}
setTimeout(function() {
    doit();
}, 1000);