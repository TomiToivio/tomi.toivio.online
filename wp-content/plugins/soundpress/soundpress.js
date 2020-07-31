jQuery(function($) {
    $(document).ready(function(){
        $('#finish-soundpress').click(embed_soundcloud);
        $('.soundpressmodal-close').click(close_soundpress_window);
        
        $('#insert-soundpress').click(open_soundpress_window);
    });

    function open_soundpress_window() {
        $('#soundpress-form').show();
    }
    
    function close_soundpress_window(){
        $('#soundpress-form').hide();
    }

    function validateUrl(value) {
        return /^(?:(?:(?:https?|ftp):)?\/\/)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:[/?#]\S*)?$/i.test(value);
    }

    function validateHeight(value) {
        return value === 'auto' || /^\d*$/.test(value);
    }
    
    function embed_soundcloud(){
        var url = $.trim($('#soundcloud_url_txt').val());
        if (!validateUrl(url)) {
            alert('Invalid input URL');
            return;
        }
        var height = $.trim($('#sc_height_txt').val());
        if (!validateHeight(height)) {
            alert('Invalid input height');
            return;
        }
        var autoplay= document.getElementById('sc_autoplay_txt').checked;
        var showuser= document.getElementById('sc_showusername_txt').checked;
        var showart= document.getElementById('sc_showart_txt').checked;
        var soundframe = '';
        
        if(url != ''){
            if(height == ''){
                height = 'auto';
            }
            
            url = url.replace(':','%3A');//encoding for iframe url
            
            soundframe='<iframe width="100%" height="'+height+'" scrolling="no" frameborder="no" src="https://w.soundcloud.com/player/?url='+url+'&amp;auto_play='+autoplay+'&amp;hide_related=false&amp;show_comments=true&amp;show_user='+showuser+'&amp;show_reposts=false&amp;visual='+showart+'"></iframe>';
            
            wp.media.editor.insert(soundframe);
            close_soundpress_window();
        }else{
            alert('No SoundCloud URL specified.');
        }
    }
});