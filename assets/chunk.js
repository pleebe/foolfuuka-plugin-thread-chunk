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
                        $(".pagecontainer").append('<article class="thread"><aside class="posts">' + $(data).find('aside.posts').html() + '</aside></article>');
                        $(".threadchunk").html($(data).find('.threadchunk').html());
                        $(".paginate").html($(data).find('.paginate').html());
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