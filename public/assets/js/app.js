$(document).on('ready', function(){
    $('form.dangerous').on('submit', function(e){
        e.preventDefault();
        var form = this;

        swal({
            title: 'Danger! Danger!',
            text: 'Are you sure you want to do this?',
            type: 'warning',
            showCancelButton: true
        }, function(){
            form.submit();
        });
    });
    var sounds_enabled_val = null;
    var set_sounds = function(new_val)
    {
        sounds_enabled_val = new_val;
        localStorage['sounds_enabled'] = new_val.toString();
    }


    var sounds_enabled = function()
    {
        if (sounds_enabled_val !== null) {
            return sounds_enabled_val;
        }

        if (typeof(localStorage) === 'undefined') {
            return false;
        }

        return localStorage['sounds_enabled'] !== false.toString();
    }

    $('.toggle-sounds')
        .text((sounds_enabled() ? 'Disable' : 'Enable')+' sounds')
        .click(function(event){
            event.stopPropagation();
            set_sounds(!sounds_enabled());
            $(this).text((sounds_enabled() ? 'Disable' : 'Enable')+' sounds');
            return false;
        });

    window.playSound = function(name)
    {
        if (sounds_enabled()) {
            var sound_obj = $('#sound-'+name);
            if ($('#sound-'+name).length < 1) {
                sound_obj = $('<audio id="sound-'+name+'" src="/assets/mp3/'+name+'.mp3" />');
                $("body").append(sound_obj);
            }
            sound_obj.get(0).play();
        }
    }

    /* Mobile menus */

    $('.menu-icon').on('click', function() {
        var child;

        $('body').toggleClass('background-blur');

        var $menu = $('.menu, .subnav');
        var $layer = $('.menu-layer');

        if ($layer.hasClass('to-arrow')) {
            $layer.removeClass('to-arrow').addClass('from-arrow');
            $menu.removeClass('on').addClass('off');
        } else {
            $layer.removeClass('from-arrow').addClass('to-arrow');
            $menu.removeClass('off').addClass('on');
        }
    });
});