(function(window, document, $) {
    'use strict';
    $(document).ready(function(){

        var body = $('body');

        // --- RESTORE STATE FROM STORAGE ---
        var savedBgColor = localStorage.getItem('sidebarBgColor');
        var savedBgImage = localStorage.getItem('sidebarBgImage');
        var savedBgDisplay = localStorage.getItem('sidebarBgDisplay');
        var savedCompact = localStorage.getItem('sidebarCompact');
        var savedSidebarWidth = localStorage.getItem('sidebarWidth');

        if (savedBgColor) {
            $('.app-sidebar').attr('data-background-color', savedBgColor);
            $('.cz-bg-color span[data-bg-color="'+savedBgColor+'"]').addClass('selected');
            if(savedBgColor === 'white'){
                $('.logo-img img').attr('src','../app-assets/img/logo-dark.png');
            }
        }

        if (savedBgImage) {
            $('.sidebar-background').css('background-image', 'url(' + savedBgImage + ')');
            $('.cz-bg-image img[src$="'+savedBgImage+'"]').addClass('selected');
        }

        if (savedBgDisplay) {
            var show = savedBgDisplay === 'true';
            $('.cz-bg-image-display').prop('checked', show);
            $('.sidebar-background').css('display', show ? 'block' : 'none');
        }

        if (savedCompact) {
            var compact = savedCompact === 'true';
            $('.cz-compact-menu').prop('checked', compact);
            if (compact) {
                $('.app-sidebar').trigger('mouseleave');
                $('.user-settings-wrap').addClass('d-none');
            } else {
                $('.user-settings-wrap').removeClass('d-none');
            }
        }
        
        if (savedSidebarWidth) {
            $('.cz-sidebar-width').val(savedSidebarWidth);
            var wrapper = $('.wrapper');
            if (savedSidebarWidth === 'small') {
                wrapper.removeClass('sidebar-lg').addClass('sidebar-sm');
            } else if (savedSidebarWidth === 'large') {
                wrapper.removeClass('sidebar-sm').addClass('sidebar-lg');
            } else {
                wrapper.removeClass('sidebar-sm sidebar-lg');
            }
        }


        // --- UI HANDLERS ---

        $('.customizer-toggle').on('click',function(){
            $('.customizer').toggleClass('open');
        });
        $('.customizer-close').on('click',function(){
            $('.customizer').removeClass('open');
        });
        if($('.customizer-content').length > 0){
            $('.customizer-content').perfectScrollbar({ theme:"dark" });
        }

        // Change Sidebar Background Color
        $('.cz-bg-color span').on('click',function(){
            var $this = $(this),
                bgColor = $this.attr('data-bg-color');

            $this.closest('.cz-bg-color').find('span.selected').removeClass('selected');
            $this.addClass('selected');

            $('.app-sidebar').attr('data-background-color', bgColor);
            if(bgColor == 'white'){
                $('.logo-img img').attr('src','../app-assets/img/logo-dark.png');
            } else {
                if($('.logo-img img').attr('src') == '../app-assets/img/logo-dark.png'){
                    $('.logo-img img').attr('src','../app-assets/img/logo.png');
                }
            }
            localStorage.setItem('sidebarBgColor', bgColor);
        });

        // Change Background Image
        $('.cz-bg-image img').on('click',function(){
            var $this = $(this),
                src = $this.attr('src');

            $('.sidebar-background').css('background-image', 'url(' + src + ')');
            $this.closest('.cz-bg-image').find('.selected').removeClass('selected');
            $this.addClass('selected');

            localStorage.setItem('sidebarBgImage', src);
        });

        // Toggle background image display
        $('.cz-bg-image-display').on('click',function(){
            var checked = $(this).prop('checked');
            $('.sidebar-background').css('display', checked ? 'block' : 'none');
            localStorage.setItem('sidebarBgDisplay', checked);
        });

        // Compact menu toggle
        $('.cz-compact-menu').on('click',function(){
            $('.nav-toggle').trigger('click');
            var checked = $(this).prop('checked');
            if(checked){
                $('.app-sidebar').trigger('mouseleave');
                $('.user-settings-wrap').addClass('d-none');
            } else {
                $('.user-settings-wrap').removeClass('d-none');
            }
            localStorage.setItem('sidebarCompact', checked);
        });

        // Sidebar width
        $('.cz-sidebar-width').on('change',function(){
            var width_val = this.value,
                wrapper = $('.wrapper');

            if(width_val === 'small'){
                $(wrapper).removeClass('sidebar-lg').addClass('sidebar-sm');
            }
            else if(width_val === 'large'){
                $(wrapper).removeClass('sidebar-sm').addClass('sidebar-lg');
            }
            else{
                $(wrapper).removeClass('sidebar-sm sidebar-lg');
            }
            localStorage.setItem('sidebarWidth', width_val);
        });

    });
})(window, document, jQuery);
