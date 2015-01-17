jQuery(function(){

    //mailto temporary fix
    jQuery(".product-list-img-share-overlay .pliso-bottom a span.instagram-icon-large").each(function() {
        var _href = jQuery(this).closest('.product-list-img-holder').find('.link-over-block').attr("href");
        var str = jQuery(this).closest('.col').find('.product-list-name a').text().trim();
        var _passURL = encodeURIComponent(str);
        jQuery(this).parent().attr("href", 'mailto:?body=' + _href + '&subject='+ _passURL);
    });

    jQuery(".no-target").click(function(e){
        e.preventDefault();
    });

    if (navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Mac') != -1 && navigator.userAgent.indexOf('Chrome') == -1) {
        jQuery('html').addClass('safari-mac');
    }
    if (navigator.userAgent.indexOf('Chrome') != -1 && navigator.userAgent.indexOf('Mac') != -1) {
        jQuery('html').addClass('chrome-mac');
    }

    var ua = navigator.userAgent.toLowerCase();
    var isAndroid = ua.indexOf("android") > -1; //&& ua.indexOf("mobile");
    if(isAndroid) {
        jQuery('html').addClass('android-mobile');
    }

    // main menu submenu
    jQuery("ul.header-main-menu li ul").addClass("header-sub-menu");
    jQuery("ul.header-main-menu li:has(ul)").addClass("hassub");
    jQuery("ul.header-main-menu ul").css({ display: 'none'});
    jQuery("ul.header-main-menu li").hover(function () {
        jQuery(this).find('ul.header-sub-menu:first').stop(true, true).delay(50).animate({"height": "show"}, 300);
    }, function () {
        jQuery(this).find('ul.header-sub-menu:first').stop(true, true).delay(50).animate({"height": "hide", "opacity": "hide"}, 300);
    });

	// cart dropdown
    jQuery(".header-cart-list-container").css({ display: 'none'});
    jQuery(".header-cart-action").hover(function () {
        jQuery(this).find('.header-cart-list-container').stop(true, true).delay(50).animate({"height": "show"}, 300);
    }, function () {
        jQuery(this).find('.header-cart-list-container').stop(true, true).delay(50).animate({"height": "hide", "opacity": "hide"}, 300);
    });	

    //hide cart link until necessary
    jQuery(".header-account-action .top-link-cart").parent().addClass("topLinksCart");

	// category and item list dropdown
    jQuery(".selectedItem").on('click', function () {
        var $ans = jQuery(this).next("ul.item-category-menu");
        $ans.slideToggle();
        jQuery("ul.item-category-menu").not($ans).slideUp();
    });
    jQuery('ul.item-category-menu li:has(ul)').addClass('hassubcat');
    jQuery("ul.item-category-menu ul").css({ display: 'none'});
    jQuery("ul.item-category-menu li").hover(function () {
        jQuery(this).find('ul.item-category-submenu:first').stop(true, true).delay(50).animate({"height": "show"}, 300);
    }, function () {
        jQuery(this).find('ul.item-category-submenu:first').stop(true, true).delay(50).animate({"height": "hide", "opacity": "hide"}, 300);
    });

    //header position depending on upload dialog open or closed

    jQuery('.open-user-upload').click(function(){
        var headerPosition = jQuery(".header-container").offset().top;
        jQuery('.header-container').css({top: headerPosition, position: "absolute"});
        var cssTop = jQuery(".header-container").css('top');
    });
    jQuery(document).scroll(function() {
        var iCurScrollPos = jQuery(this).scrollTop();
        var headerPositionAlt = jQuery(document).scrollTop();
        var cssTopAlt = jQuery(".header-container").offset().top;
        if (headerPositionAlt < cssTopAlt) {
            jQuery(".header-container").css({
                "position":"fixed",
                "top":"0"
            });
        }else if (jQuery('.header-upload-container').is(':visible')){
            if (iCurScrollPos > iScrollPos) {
                var cssTopSecond = jQuery(".header-container").offset().top;
                jQuery('.header-container').css({top: cssTopSecond, position: "absolute"});
                console.log(cssTopSecond);
            }
        }else{
            jQuery(".header-container").css({
                "position":"fixed",
                "top":"0"
            });
        }
        iScrollPos = iCurScrollPos;
    });

    //upload tabs control
	jQuery(".header-upload-container").css({ display: 'none'});
	jQuery(".open-user-upload").click(function(e){
		e.preventDefault();
		jQuery(this).toggleClass("activeUpload");
		jQuery(this).find("span").toggleClass("hover");
		jQuery(".header-upload-container").slideToggle();
	});
	jQuery('body').click(function(e) {
        if (jQuery(e.target).closest('.open-user-upload').length === 0 && jQuery(e.target).closest('.header-upload-container').length === 0 && jQuery(e.target).closest('.item-upload-slider-lightbox').length === 0) {
            jQuery(".header-upload-container").slideUp();
            jQuery(".open-user-upload").removeClass("activeUpload");
            jQuery(".open-user-upload span").removeClass("hover");
            jQuery("ul.item-category-menu").hide();
            jQuery(".item-upload-slider-lightbox").hide();
        }else if (jQuery(e.target).closest('.selectedItem').length === 0 && jQuery(e.target).closest('ul.item-category-menu').length === 0) {
            jQuery("ul.item-category-menu").slideUp();
        }
    });


	// tabs
	jQuery('div.header-upload-tabs').each(function(){
		var $active, $content, $links = jQuery(this).find('.upload-tab');
		$active = jQuery($links.filter('[name="'+location.hash+'"]')[0] || $links[0]);
		$content = jQuery($active.attr('name'));
		$links.not($active).each(function () {
			jQuery(jQuery(this).attr('name')).hide();
		});
		jQuery(this).on('click', '.upload-tab', function(e){
			jQuery(".item-upload-slider-lightbox").fadeOut("fast");
			if ( jQuery(this).hasClass("active") ) {
				//do nothing - additional options available if needed
			}else{
				$active.removeClass('active');
				$content.hide();
				$active = jQuery(this);
				$content = jQuery(jQuery(this).attr('name'));
				$active.addClass('active');
				$content.show();
			}
		});
	});

    // tabs product
    jQuery('div.product-rev-com-tabs').each(function(){
        var $active, $content, $links = jQuery(this).find('.revcom-tab');
        $active = jQuery($links.filter('[name="'+location.hash+'"]')[0] || $links[0]);
        $content = jQuery($active.attr('name'));
        $links.not($active).each(function () {
            jQuery(jQuery(this).attr('name')).hide();
        });
        jQuery(this).on('click', '.revcom-tab', function(e){
            if ( jQuery(this).hasClass("active") ) {
                //do nothing - additional options available if needed
            }else{
                $active.removeClass('active');
                $content.hide();
                $active = jQuery(this);
                $content = jQuery(jQuery(this).attr('name'));
                $active.addClass('active');
                $content.show();
            }
        });
    });

    // tabs profile
    jQuery('div.col-left-profile ul').each(function(){
        var $active, $content, $links = jQuery(this).find('li');
        $active = jQuery($links.filter('[name="'+location.hash+'"]')[0] || $links[0]);
        $content = jQuery($active.attr('name'));
        $links.not($active).each(function () {
            jQuery(jQuery(this).attr('name')).hide();
        });
        jQuery(this).on('click', 'li', function(e){
            if ( jQuery(this).hasClass("active") ) {
                //do nothing - additional options available if needed
            }else{
                jQuery('div.col-left-profile ul li').removeClass('active');
                jQuery('div.col-left-profile ul li .icon-sprite').removeClass('current');
                jQuery('#profilemenu0').hide();
                jQuery('#profilemenu5').hide();
                jQuery('#profilemenu6').hide();
                jQuery('#profilemenu7').hide();
                $active.removeClass('active');
                $active.find(".icon-sprite").removeClass("current");
                $content.hide();
                $active = jQuery(this);
                $content = jQuery(jQuery(this).attr('name'));
                $active.addClass('active');
                $active.find(".icon-sprite").addClass("current");
                $content.show();
            }
        });
    });

    jQuery('div.profile-list-info ul').each(function(){
        var $active, $content, $links = jQuery(this).find('li');
        $active = jQuery($links.filter('[name="'+location.hash+'"]')[0] || $links[0]);
        $content = jQuery($active.attr('name'));
        $links.not($active).each(function () {
            jQuery(jQuery(this).attr('name')).hide();
        });
        jQuery(this).on('click', 'li', function(e){
            if ( jQuery(this).hasClass("active") ) {
                //do nothing - additional options available if needed
            }else{
                jQuery('div.profile-list-info ul li').removeClass('active');
                jQuery('div.profile-list-info ul li').removeClass('prev-active');
                jQuery('#profilemenu0').hide();
                jQuery('#profilemenu1').hide();
                jQuery('#profilemenu2').hide();
                jQuery('#profilemenu3').hide();
                jQuery('#profilemenu4').hide();
                jQuery('#profilemenu5').hide();
                jQuery('#profilemenu6').hide();
                jQuery('#profilemenu7').hide();
                $active.removeClass('active');
                $content.hide();
                $active = jQuery(this);
                $content = jQuery(jQuery(this).attr('name'));
                $active.addClass('active');
                $active.prev("li").addClass('prev-active');
                $content.show();
            }
        });
    });






    // tabs notifications
    jQuery('div.col-notifications-control').each(function(){
        var $active, $content, $links = jQuery(this).find('.noti-tab');
        $active = jQuery($links.filter('[name="'+location.hash+'"]')[0] || $links[0]);
        $content = jQuery($active.attr('name'));
        $links.not($active).each(function () {
            jQuery(jQuery(this).attr('name')).hide();
        });
        jQuery(this).on('click', '.noti-tab', function(e){
            if ( jQuery(this).hasClass("active") ) {
                //do nothing - additional options available if needed
            }else{
                $active.removeClass('active');
                $content.hide();
                $active = jQuery(this);
                $content = jQuery(jQuery(this).attr('name'));
                $active.addClass('active');
                $content.show();
            }
        });
    });


    //display loader add item
    jQuery(".web-upload-submit input").click(function(){
        if(jQuery('.web-upload-input input').val()){
            jQuery("#utab1").append("<div class='upload-pars-loading'></div>");
        }
    });
    jQuery(".local-files-upload-overlay .upload-btn-submit").click(function(){
        if(jQuery('.local-file-up-display').val()){
            jQuery("#utab2").append("<div class='upload-pars-loading'></div>");
        }
    });


    // load more reviews
    size_li = jQuery(".reviews-list-load li").size();
    x=2;
    if (size_li > 2){
        jQuery('.loadMoreReviews-container').show();
    }
    jQuery('.reviews-list-load li:lt('+x+')').show();
    jQuery('.loadMoreReviews').click(function () {
        x= (x+2 <= size_li) ? x+2 : size_li;
        jQuery('.reviews-list-load li:lt('+x+')').show();
        if (jQuery('.reviews-list-load li:last').is(':visible')) {
            jQuery('.loadMoreReviews-container').hide();
        }
    });

    //claim dialog open
    jQuery(".claim-btn").click(function(){
        jQuery(".claim-lightbox-overlay").show();
        jQuery(".claim-lightbox-dialog").fadeIn(500);
        jQuery('html,body').animate({ scrollTop: 0 }, 1000);
    });
    jQuery(".close-claim").click(function(){
        jQuery(".claim-lightbox-overlay").fadeOut();
        jQuery(".claim-lightbox-dialog").hide();
    });
    jQuery(".claim-lightbox-overlay").click(function(){
        jQuery(".claim-lightbox-overlay").fadeOut();
        jQuery(".claim-lightbox-dialog").hide();
    });
    jQuery(".claim-lightbox-overlay .claim-lightbox-dialog").click(function(e) {
        e.stopPropagation();
    });

    // add to list dialog
    jQuery(".open-add-list").click(function(){
        jQuery(".add-list-overlay").show();
        jQuery(".add-list-dialog").fadeIn(500);
        jQuery('html,body').animate({ scrollTop: 0 }, 500);
    });
    jQuery(".pl-add-to-list").each(function(){
        jQuery(document).on("click",".pl-add-to-list", function(e){
            e.preventDefault();
            var posYB = jQuery(window).scrollTop() + 100;
            jQuery(".add-list-dialog").css("top", posYB);
            jQuery(".add-list-overlay").show();
            jQuery(".add-list-dialog").fadeIn(500);
        });
    });
    jQuery(document).on("click",".list-dialog-close", function(){
        jQuery(".add-list-overlay").fadeOut();
        jQuery(".add-list-dialog").hide();
    });
    jQuery(document).on("click",".add-list-overlay", function(){
        jQuery(".add-list-overlay").fadeOut();
        jQuery(".add-list-dialog").hide();
    });
    jQuery(document).on("click",".add-list-overlay .add-list-dialog", function(e){
        e.stopPropagation();
    });
    jQuery(document).on("click",".add-list-content .create-new-focus", function(){
        jQuery(".add-list-content .add-list-text-input input").focus();
    });

    //cart coupon display
    jQuery(".coupon-input-show").click(function(){
        jQuery(".coupon-input-container").slideDown();
        jQuery(this).addClass("coupon-input-show-active");
        jQuery(this).slideUp();
    });
    jQuery(".coupon-input-close").click(function(){
        jQuery(".coupon-input-container").slideUp();
        jQuery(".coupon-input-show").removeClass("coupon-input-show-active");
        jQuery(".coupon-input-show").slideDown();
    });



    //hover header image checkbox
    jQuery(".img-upload-chckbox").hover(function () {
        jQuery(this).parent().toggleClass("img-checbx-hover");
    });

	//open uploaded images lightbox
	jQuery(".header-uploaded-images li img").click(function(){
		jQuery(".item-upload-slider-lightbox").fadeIn();
	});
	jQuery(".iusl-close").click(function(){
		jQuery(".item-upload-slider-lightbox").fadeOut();
	});

    // shop slider
    var win = jQuery(document).width(); //this = window
    if (win < 695) {
        var mySwiperShop = new Swiper('.swiper-container-shop',{
            loop:true,
            grabCursor: true,
            autoResize: false,
            slidesPerView: 1,
            slidesPerGroup: 1,
            createPagination:true,
            pagination: '.shop-pagination',
            paginationClickable:true
        });
        jQuery('.arrow-left-shop').on('click', function(e){
            e.preventDefault()
            mySwiperShop.swipePrev()
        });
        jQuery('.arrow-right-shop').on('click', function(e){
            e.preventDefault()
            mySwiperShop.swipeNext()
        });
    }else if (win <= 1200){
        var mySwiperShop = new Swiper('.swiper-container-shop',{
            loop:true,
            grabCursor: true,
            autoResize: false,
            slidesPerView: 2,
            slidesPerGroup: 1,
            createPagination:true,
            pagination: '.shop-pagination',
            paginationClickable:true
        });
        jQuery('.arrow-left-shop').on('click', function(e){
            e.preventDefault()
            mySwiperShop.swipePrev()
        });
        jQuery('.arrow-right-shop').on('click', function(e){
            e.preventDefault()
            mySwiperShop.swipeNext()
        });
    }else if (win > 1200){
        var mySwiperShop = new Swiper('.swiper-container-shop',{
            loop:true,
            grabCursor: true,
            autoResize: false,
            slidesPerView: 3,
            slidesPerGroup: 1,
            createPagination:true,
            pagination: '.shop-pagination',
            paginationClickable:true
        });
        jQuery('.arrow-left-shop').on('click', function(e){
            e.preventDefault()
            mySwiperShop.swipePrev()
        });
        jQuery('.arrow-right-shop').on('click', function(e){
            e.preventDefault()
            mySwiperShop.swipeNext()
        });
    }

	//slim scroll added images
	jQuery(".open-user-upload").click(function(){
		if(jQuery('.item-image-scroll ul li').length > 12){
			jQuery('.item-image-scroll').slimScroll({
				height: '136px'
			});
		}
		if(jQuery('.item-image-scroll-local ul li').length > 12){
			jQuery('.item-image-scroll-local').slimScroll({
				height: '136px'
			});
		}
	});
	
	//uniform form elements activation
	jQuery("input:checkbox, input:radio, select").uniform();

    // back to top link
    jQuery(".return-top").click(function(e){
        e.preventDefault();
        jQuery('html,body').animate({ scrollTop: 0 }, 'slow');
    });

    // forgot pass dialog
    jQuery(document).on("click","#quicklogin #login-form .buttons-set .fpasscheck span",function() {
        jQuery("#quicklogin #fpass").slideToggle();
    });

    // filters control
    jQuery(".filters-container .filter-selected").click(function(){
        jQuery(".filters-container .filters-list-container").slideToggle();
        jQuery(this).parent().toggleClass("filters-activated");
    });
    jQuery('.filters-list-container > .filters-list-cat > li:has(ul)').addClass("has-sub-filter");
    jQuery('.filters-list-container > .filters-list-cat > li.has-sub-filter > a').append("<span class='catMore'></span>");
    jQuery('.filters-list-container > .filters-list-cat > li > a > span.catMore').click(function() {
        var checkElement = jQuery(this).parent().next();
        jQuery('.filters-list-container li').removeClass('active');
        jQuery(this).closest('li').addClass('active');
        if((checkElement.is('ul')) && (checkElement.is(':visible'))) {
            jQuery(this).closest('li').removeClass('active');
            checkElement.slideUp('normal');
        }
        if((checkElement.is('ul')) && (!checkElement.is(':visible'))) {
            jQuery('.filters-list-container ul ul:visible').slideUp('normal');
            checkElement.slideDown('normal');
        }
        if (checkElement.is('ul')) {
            return false;
        } else {
            return true;
        }
    });


    // price slider range
    var minRangeRead = jQuery("#priceAmountMin").val();
    var maxRangeRead = jQuery("#priceAmountMax").val();
    var rangeReadCombined = maxRangeRead - minRangeRead;
    jQuery( "#slider-range" ).slider({
        range: true,
        min: 1,
        max: 99,
        values: [ minRangeRead, maxRangeRead ],
        slide: function(event, ui){
            jQuery("#priceAmountMin").val(ui.values[0]);
            jQuery("#priceAmountMax").val(ui.values[1]);
            jQuery(".ui-slider-handle:eq(0) .handle-range-amount").html("$" + ui.values[0]);
            jQuery(".ui-slider-handle:eq(1) .handle-range-amount").html("$" + ui.values[1]);
            var handleMinPos = ui.values[0];
            var handleMaxPos = ui.values[1];
            var handleRangeCombined = handleMaxPos - handleMinPos;
            if((handleRangeCombined < 20) && (handleMaxPos < 40)){
                jQuery(".filters-price-slide-container").addClass("priceTextRight");
                jQuery(".ui-slider-handle:eq(1) .handle-range-amount").addClass("handleGoTop");
            }else if(handleRangeCombined < 20){
                jQuery(".ui-slider-handle:eq(1) .handle-range-amount").addClass("handleGoTop");
            }else{
                jQuery(".ui-slider-handle:eq(1) .handle-range-amount").removeClass("handleGoTop");
                jQuery(".ui-slider-handle:eq(0) .handle-range-amount").removeClass("handleGoTop");
                jQuery(".filters-price-slide-container").removeClass("priceTextRight");
            }
        }
    });
    if((rangeReadCombined < 20) && (maxRangeRead < 40)){
        jQuery(".filters-price-slide-container").addClass("priceTextRight");
        jQuery(".ui-slider-handle:eq(1) .handle-range-amount").addClass("handleGoTop");
    }else if((rangeReadCombined < 20) && (rangeReadCombined > 0)){
        jQuery(".ui-slider-handle:eq(1) .handle-range-amount").addClass("handleGoTop");
        jQuery(".ui-slider-handle:eq(0) .handle-range-amount").removeClass("handleGoTop");
    }else{
        jQuery(".ui-slider-handle:eq(1) .handle-range-amount").removeClass("handleGoTop");
        jQuery(".filters-price-slide-container").removeClass("priceTextRight");
    }
    jQuery("#priceAmountMin").val(jQuery("#slider-range").slider("values",0));
    jQuery("#priceAmountMax").val(jQuery("#slider-range").slider("values",1));
    jQuery(".ui-slider-handle:eq(0) .handle-range-amount").html("$" + jQuery("#slider-range").slider("values",0));
    jQuery(".ui-slider-handle:eq(1) .handle-range-amount").html("$" + jQuery("#slider-range").slider("values",1));

    // product social share
    jQuery(".img-overlay-share").each(function(){
        jQuery(document).on("click",".img-overlay-share" , function(e){
            e.preventDefault();
            jQuery(this).parent().parent().parent().find(".product-list-img-share-overlay").slideDown();
            jQuery(this).addClass("shareOpenX");
            jQuery(this).removeClass("img-overlay-share");
            jQuery(this).find(".icon-sprite").addClass("activeShare");
        });
        jQuery(document).on("click",".shareOpenX" , function(e){
            e.preventDefault();
            jQuery(this).parent().parent().parent().find(".product-list-img-share-overlay").slideUp();
            jQuery(this).removeClass("shareOpenX");
            jQuery(this).addClass("img-overlay-share");
            jQuery(this).find(".icon-sprite").removeClass("activeShare");
        });
    });
    jQuery('.product-list-img-overlay').click(function(e) {
        if (jQuery(e.target).closest('.product-list-img-overlay-inner').length === 0 && jQuery(e.target).closest('.product-list-img-share-overlay').length === 0) {
            jQuery(".product-list-img-share-overlay").hide();
        }
    });
    jQuery('.product-list-img-overlay').each(function(){
        jQuery(document).on("mouseleave",".product-list-img-overlay",function(){
            jQuery(this).find(".product-list-img-overlay-inner").show();
            jQuery(this).find(".product-list-img-share-overlay").fadeOut();
            jQuery(this).find(".shareOpenX").addClass("img-overlay-share");
            jQuery(this).find(".img-overlay-share").removeClass("shareOpenX");
            jQuery(this).find(".img-overlay-share .icon-sprite").removeClass("activeShare");
        });
    });

    //search
    jQuery(".search-toolbar-links .current-search").click(function(e){
        e.preventDefault();
    });
    //responsive header search tooggle
    jQuery(".search-open-toggle").click(function(){
        jQuery(".response-search-container").slideToggle();
        jQuery(this).toggleClass("srcOpnRes");
    });
    jQuery(".response-search-container .search-input-holder input").focus(function(){
        jQuery(this).parent().parent().parent().parent().addClass("search-focus");
        jQuery(".search-open-toggle").addClass("srcOpnResAlt");
    }).blur(function(){
        jQuery(this).parent().parent().parent().parent().removeClass("search-focus");
        jQuery(".search-open-toggle").removeClass("srcOpnResAlt");
    });

    // product share dialog open
    jQuery(".product-social-container .product-share-open").click(function(){
        jQuery(this).toggleClass("activeSocialMenu");
        jQuery(this).find("span").toggleClass("share-white-big-ico");
        jQuery(this).find("span").toggleClass("share-grey-big-ico");
        jQuery(".product-social-share-buttons-container").slideToggle();
    });


    //slide-show social/share dialog
    jQuery( ".product-list-img-holder .product-list-img-overlay" ).each(function(){
        jQuery(document).on("mouseover", ".product-list-img-holder .product-list-img-overlay", function() {
            if (jQuery(this).width() < 154) {
                jQuery(this).find(".product-list-img-overlay-inner").hide();
            }else{
                jQuery(this).find(".product-list-img-overlay-inner").slideDown();
            }
        });
        jQuery(document).on("mouseleave",".product-list-img-holder .product-list-img-overlay", function() {
            if (jQuery(this).width() < 154) {
                jQuery(this).find(".product-list-img-overlay-inner").hide();
            }else{
                jQuery(this).find(".product-list-img-overlay-inner").slideUp();
            }
        });
    });


    // vertical centering
//    jQuery(document).on("mouseover",".product-list-img-holder" , function() {
//        jQuery( ".product-list-img-overlay-inner" ).each(function() {
//            jQuery(this).css({
//                'position' : 'absolute',
//                'left' : '50%',
//                'top' : '50%',
//                'margin-left' : -jQuery(this).width()/2,
//                'margin-top' : -jQuery(this).height()/2
//            });
//        });
//    });

    // index grid - list view
    jQuery(document).on('click','.index-view-mode .list' , function(e){
        e.preventDefault();
        jQuery(".index-col-main .section.group").addClass("single-line-display-section");
        jQuery(".index-col-main .section.group .col-1-of-2").parent().addClass("original-1-of-2");
        jQuery(".index-col-main .section.group .col-1-of-4").addClass("col-1-of-2").removeClass("col-1-of-4");
        jQuery(".index-col-main .section.group .col-1-of-3").addClass("col-2-of-2").removeClass("col-1-of-3");
        jQuery(this).parent().find("span").removeClass("hover");
        jQuery(this).find("span").addClass("hover");
        jQuery(this).css("cursor","default");
        jQuery(".index-view-mode .grid").css("cursor","pointer");
    });
    jQuery(document).on('click','.index-view-mode .grid' , function(e){
        e.preventDefault();
        jQuery(".index-col-main .section.group").removeClass("single-line-display-section");
        jQuery(".index-col-main .section.group .col-1-of-2").addClass("col-1-of-4").removeClass("col-1-of-2");
        jQuery(".index-col-main .section.group .col-2-of-2").addClass("col-1-of-3").removeClass("col-2-of-2");
        jQuery(".index-col-main .section.group.original-1-of-2 .col-1-of-4").addClass("col-1-of-2").removeClass("col-1-of-4");
        jQuery(this).parent().find("span").removeClass("hover");
        jQuery(this).find("span").addClass("hover");
        jQuery(this).css("cursor","default");
        jQuery(".index-view-mode .list").css("cursor","pointer");

    });

    //upload field control
    jQuery(document).on('change', '.local-file-up span :file', function() {
        var input = jQuery(this),
            numFiles = input.get(0).files ? input.get(0).files.length : 1,
            label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
        input.trigger('fileselect', [numFiles, label]);
    });
    jQuery(document).ready( function() {
        jQuery('.local-file-up span :file').on('fileselect', function(event, numFiles, label) {

            var input = jQuery('.local-file-up-display'),
                log = numFiles > 1 ? numFiles + ' files selected' : label;

            if( input.length ) {
                input.val(log);
                jQuery(".upload-btn-submit").show();
                jQuery(".local-upload-container").addClass("upload-valid-show");
            }
        });
    });

    //stripe click trigger
    jQuery('#stripe_cc_expiration_year').on('change', function() {
        jQuery(".cart-btn-update").click();
    });

    //shipping address actions
    jQuery(".shipping-address-select").on('change', function(){
        jQuery(".cart-btn-update").click();
        jQuery("#reload-shipping-method-button").click();
    });

    jQuery(".add-shipping-address").click(function(e){
        e.preventDefault();
        jQuery(this).hide();
        jQuery("#shipping-new-address-form").slideDown();
        jQuery(".targetShippingIf").val('1');
    });
    jQuery(".cancel-add-address").click(function(e){
        e.preventDefault();
        jQuery("#shipping-new-address-form").slideUp();
        jQuery(".targetShippingIf").val('0');
        setTimeout(function(){
            jQuery(".add-shipping-address").show();
        },400);
    });



    //select checkout country - billing
    jQuery('.country-select-container select').on('change', function (e) {
        if(jQuery('.cart-province-select').css('display') == 'none'){
            jQuery('.cart-province-select').parent().hide();
            jQuery('.cart-province-text').show();
        }else{
            jQuery('.cart-province-select').parent().show();
            jQuery('.cart-province-text').hide();
        }
        jQuery(".cart-province-select").uniform();
        jQuery.uniform.update();
    });
    if(jQuery('.cart-province-select').css('display') == 'none'){
        jQuery('.cart-province-select').parent().hide();
        jQuery('.cart-province-text').show();
    }else{
        jQuery('.cart-province-select').parent().show();
        jQuery('.cart-province-text').hide();
    }

    //select checkout country - shipping
    jQuery('.country-select-container-alt select').on('change', function (e) {
        if(jQuery('.cart-province-select-alt').css('display') == 'none'){
            jQuery('.cart-province-select-alt').parent().hide();
            jQuery('.cart-province-text-alt').show();
        }else{
            jQuery('.cart-province-select-alt').parent().show();
            jQuery('.cart-province-text-alt').hide();
        }
        jQuery(".cart-province-select-alt").uniform();
        jQuery.uniform.update();
    });
    if(jQuery('.cart-province-select-alt').css('display') == 'none'){
        jQuery('.cart-province-select-alt').parent().hide();
        jQuery('.cart-province-text-alt').show();
    }else{
        jQuery('.cart-province-select-alt').parent().show();
        jQuery('.cart-province-text-alt').hide();
    }

    //select account adress country
    jQuery('.country-select-container-account select').on('change', function (e) {
        if(jQuery('.country-select-province-account').css('display') == 'none'){
            jQuery('.country-select-province-account').parent().hide();
            jQuery('.country-text-province-account').show();
        }else{
            jQuery('.country-select-province-account').parent().show();
            jQuery('.country-text-province-account').hide();
        }
        jQuery(".country-select-province-account").uniform();
        jQuery.uniform.update();
    });
    if(jQuery('.country-select-province-account').css('display') == 'none'){
        jQuery('.country-select-province-account').parent().hide();
        jQuery('.country-text-province-account').show();
    }else{
        jQuery('.country-select-province-account').parent().show();
        jQuery('.country-text-province-account').hide();
    }


    //select fits radio shipping
    jQuery(".col-right-inner-left .shipping #co-shipping-method-form ul li:eq(0) input:radio").prop("checked", true);
    //change shipping text
    jQuery(".col-right-inner-right #shopping-cart-totals-table tbody tr.t-total-ship td:first-child").html("Shipping & Taxes");


    //select profile country
    jQuery('.settings-section-address-1 .input-country select').on('change', function (e) {
        if(jQuery('.settings-section-address-1 .input-region select').css('display') == 'none'){
            jQuery(".settings-section-address-1 .input-region .selector").hide();
        }else{
            jQuery(".settings-section-address-1 .input-region .selector").css("display","block");
        }
    });
    if(jQuery('.settings-section-address-1 .input-region select').css('display') == 'none'){
        jQuery(".settings-section-address-1 .input-region .selector").hide();
    }else{
        jQuery(".settings-section-address-1 .input-region .selector").css("display","block");
    }

    //list dialogs open close control
    jQuery(document).on("click",".p-create-list-container .p-list-container-inner",function(){
        var posYA = jQuery(window).scrollTop() + 100;
        jQuery(".p-create-list-dialog").css("top", posYA);
        jQuery(".p-create-list-dialog").fadeIn();
        jQuery(".p-create-list-overlay").show();
    });
    jQuery(document).on("click",".p-create-list-overlay",function(){
        jQuery(".p-create-list-dialog").hide();
        jQuery(".p-create-list-overlay").fadeOut();
    });
    jQuery(".p-list-container .p-list-container-inner").each(function(){
        jQuery(this).click(function(){
            var posY = jQuery(window).scrollTop() + 100;
            jQuery(this).parent().find(".p-edit-list-dialog").css("top", posY);
            jQuery(this).parent().find(".p-edit-list-dialog").fadeIn();
            jQuery(".p-create-list-overlay").show();
        });
    });
    jQuery(".p-create-list-overlay").click(function(){
        jQuery(".p-edit-list-dialog").hide();
        jQuery(".p-create-list-overlay").fadeOut();
    });

    //input file list image styling
    jQuery(".listImgUpload input").each(function(){
        jQuery(this).on("change",function(){
            var listImg = jQuery(this).val();
            jQuery(this).parent().parent().find(".imageNameDisplay").html(listImg);
            jQuery(this).parent().parent().find(".imageNameDisplay").css("display","block");
        })
    });
    //no app popup
    jQuery(".noApp,.app-store-href,.google-store-href").click(function(e){
        e.preventDefault();
        jQuery(".noApp-overlay").fadeIn();
    });
    jQuery(".noApp-overlay").click(function(){
        jQuery(this).hide();
    });




});

jQuery(window).load(function(){
    // product gallery
    jQuery(".all-product-images ul li a").fadeIn(1500).css({"display":"block"});
    setTimeout(function(){
        if(jQuery(".all-product-images ul li a").hasClass('activeImg')) {
            //do nothing
        }else{
            var imgMain = jQuery(".product-image img").attr("src");
            jQuery('.all-product-images ul li a[href="'+imgMain+'"]').addClass("activeImg");
        }
    },1500);
    jQuery(".all-product-images ul li a").each(function(){
        jQuery(this).click(function(e){
            var imageActive = jQuery(".product-image img").attr("src");
            var imageSource = jQuery(this).attr("href");
            if(imageActive == imageSource){
                //do nothing
            }else{
                jQuery(".all-product-images ul li a").removeClass("activeImg");
                jQuery(this).addClass("activeImg");
                jQuery(".product-image img").remove();
                jQuery(".product-image").append("<img style='display:none;' src='"+imageSource+"'/>");
                jQuery(".product-image img").fadeIn(1000);
            }
            e.preventDefault();
        });
    });

});

jQuery(window).resize(function() {
    var width = jQuery(document).width();
    if (width > 790) {
        jQuery(".response-search-container").show();
        jQuery(".search-open-toggle").removeClass("srcOpnRes");
    }else{
        jQuery(".response-search-container").hide();
        jQuery(".search-open-toggle").removeClass("srcOpnRes");
    }
});

jQuery(function(){
    // responsive menu (must be last to call)
    jQuery(".responsive-main-menu ul.dl-menu li ul").addClass("dl-submenu");
    jQuery( '#dl-menu' ).dlmenu();
});