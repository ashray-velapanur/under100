jQuery(function () {
    //settings button
    jQuery(".profile-desc-col-left-notifications").click(function(){
        jQuery('html, body').animate({
            scrollTop: jQuery(".col-main-profile").offset().top -60
        }, 1000);
        jQuery(".profile-part-content").hide();
        jQuery("#profilemenu7").show();
        jQuery(".col-left-profile ul li").removeClass("active");
        jQuery(".col-left-profile ul li .icon-sprite").removeClass("current");
        jQuery('.col-left-profile ul li[name="#profilemenu7"]').addClass("active");
        jQuery('.col-left-profile ul li[name="#profilemenu7"] .icon-sprite').addClass("current");
        jQuery(".profile-list-info ul li").removeClass("active");
        jQuery(".profile-list-info ul li").removeClass("prev-active");
    });

    //tabs control if url #
    if ( document.location.href.indexOf('#lists') > -1 ) {
        jQuery(".profile-part-content").hide();
        jQuery("#profilemenu5").show();
        jQuery(".col-left-profile ul li").removeClass("active");
        jQuery(".col-left-profile ul li .icon-sprite").removeClass("current");
        jQuery('.col-left-profile ul li[name="#profilemenu5"]').addClass("active");
        jQuery('.col-left-profile ul li[name="#profilemenu5"] .icon-sprite').addClass("current");
        jQuery(".profile-list-info ul li").removeClass("active");
        jQuery(".profile-list-info ul li").removeClass("prev-active");
    }
    if ( document.location.href.indexOf('#notifications') > -1 ) {
        jQuery(".profile-part-content").hide();
        jQuery("#profilemenu7").show();
        jQuery(".col-left-profile ul li").removeClass("active");
        jQuery(".col-left-profile ul li .icon-sprite").removeClass("current");
        jQuery('.col-left-profile ul li[name="#profilemenu7"]').addClass("active");
        jQuery('.col-left-profile ul li[name="#profilemenu7"] .icon-sprite').addClass("current");
        jQuery(".profile-list-info ul li").removeClass("active");
        jQuery(".profile-list-info ul li").removeClass("prev-active");
    }
    if ( document.location.href.indexOf('#settings') > -1 ) {
        jQuery(".profile-part-content").hide();
        jQuery("#profilemenu0").show();
        jQuery(".col-left-profile ul li").removeClass("active");
        jQuery(".col-left-profile ul li .icon-sprite").removeClass("current");
        jQuery('.col-left-profile ul li[name="#profilemenu0"]').addClass("active");
        jQuery('.col-left-profile ul li[name="#profilemenu0"] .icon-sprite').addClass("current");
        jQuery(".profile-list-info ul li").removeClass("active");
        jQuery(".profile-list-info ul li").removeClass("prev-active");
    }
    if ( document.location.href.indexOf('#posts') > -1 ) {
        jQuery(".profile-part-content").hide();
        jQuery("#profilemenu2").show();
        jQuery(".col-left-profile ul li").removeClass("active");
        jQuery(".col-left-profile ul li .icon-sprite").removeClass("current");
        jQuery('.col-left-profile ul li[name="#profilemenu2"]').addClass("active");
        jQuery('.col-left-profile ul li[name="#profilemenu2"] .icon-sprite').addClass("current");
        jQuery(".profile-list-info ul li").removeClass("active");
        jQuery(".profile-list-info ul li").removeClass("prev-active");
    }

    jQuery(".header-account-action ul li a").click(function(){
        if ( jQuery(this).is('[href*="#lists"]')) {
            jQuery(".profile-part-content").hide();
            jQuery("#profilemenu5").show();
            jQuery(".col-left-profile ul li").removeClass("active");
            jQuery(".col-left-profile ul li .icon-sprite").removeClass("current");
            jQuery('.col-left-profile ul li[name="#profilemenu5"]').addClass("active");
            jQuery('.col-left-profile ul li[name="#profilemenu5"] .icon-sprite').addClass("current");
            jQuery(".profile-list-info ul li").removeClass("active");
            jQuery(".profile-list-info ul li").removeClass("prev-active");
        }else if (jQuery(this).is('[href*="#notifications"]')) {
            jQuery(".profile-part-content").hide();
            jQuery("#profilemenu7").show();
            jQuery(".col-left-profile ul li").removeClass("active");
            jQuery(".col-left-profile ul li .icon-sprite").removeClass("current");
            jQuery('.col-left-profile ul li[name="#profilemenu7"]').addClass("active");
            jQuery('.col-left-profile ul li[name="#profilemenu7"] .icon-sprite').addClass("current");
            jQuery(".profile-list-info ul li").removeClass("active");
            jQuery(".profile-list-info ul li").removeClass("prev-active");
        }else if (jQuery(this).is('[href*="#settings"]')) {
            jQuery(".profile-part-content").hide();
            jQuery("#profilemenu0").show();
            jQuery(".col-left-profile ul li").removeClass("active");
            jQuery(".col-left-profile ul li .icon-sprite").removeClass("current");
            jQuery('.col-left-profile ul li[name="#profilemenu0"]').addClass("active");
            jQuery('.col-left-profile ul li[name="#profilemenu0"] .icon-sprite').addClass("current");
            jQuery(".profile-list-info ul li").removeClass("active");
            jQuery(".profile-list-info ul li").removeClass("prev-active");
        }


    });
});