/*
slidesjs_callback.push(function () {
    $("#id").slidesjs({
        width: 940,
        height: 400
    });
});
*/

$(document).ready(function () {
    slidesjs_callback.forEach(function (e) {
        e();
    });

    $('.slidesjs-previous').empty().html('<i class="fa fa-arrow-left fa-2x" aria-hidden="true"></i>');
    $('.slidesjs-next').empty().html('<i class="fa fa-arrow-right fa-2x" aria-hidden="true"></i>');

    $('.slidesjs-pagination>li>a').each(function () {
        $(this).click(function(e) {
            e.preventDefault();
            target = $(e.target).parent().parent().parent().parent();
            $(target).attr("auto", false);
            SlectActiveSlide(target);
        });
    });

    $('.slides').each(function() {
        SlectActiveSlide(this);
        $(this).attr("auto", true);
    });

    ClickForSlidesjs('.slidesjs-previous');
    ClickForSlidesjs('.slidesjs-next');

    setInterval(function() {
        $('.slides').each(function () {
            if ($(this).attr("auto") == "true") {
                $(this).find('.slidesjs-next').click();
            }
        });
    }, 5000);
});

function ClickForSlidesjs(name) {
    $($(name)).click(function (e) {
        e.preventDefault();

        target = $(e.target).parent().parent();

        if (e.originalEvent !== undefined) {
            $(target).attr("auto", false);
        }

        SlectActiveSlide(target);
    });
}

function SlectActiveSlide(conteren) {
    $(conteren).find('.slidesjs-pagination>li>a').each(function (e) {
        if ($(this).hasClass('active')) {
            $(this).empty().html('<i class="fa fa-circle" aria-hidden="true"></i>');
            var t = $(this).parent().parent().parent();

            var index = $(this).attr('data-slidesjs-item');
            var taget_img = $(t).find('.slidesjs-container>.slidesjs-control>img[slidesjs-index=' + index + ']');

            var title = taget_img.attr('title');
            var text = taget_img.attr('text');

            if (!title) {
                title = '';
            }

            if (!text) {
                text = '';
            }

            t.find('.slidesjs-info').remove();
            t.prepend('<div class="slidesjs-info"><h3>' + title + '</h3></div>')
            t.append('<div class="slidesjs-info"><p>' + text + '</p></div>');
        } else {
            $(this).empty().html('<i class="fa fa-circle-o" aria-hidden="true"></i>');
        }
    });
}