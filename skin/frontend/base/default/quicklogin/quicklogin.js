var checkurl = URL + "customer/account/signupformpopup/";
/* URL is define in header.phtml */
	function showForgotSection (it, box) {
			var vis = (box.checked) ? "block" : "none";
			document.getElementById(it).style.display = vis;
			}
	        if($('alogin')){
				T$('alogin').onclick = function()
				{
                TINY.box.show({url: checkurl ,width:550,height:100,opacity:20,topsplit:10});
                jQuery('html,body').animate({ scrollTop: 0 }, 'fast');
				}
			} 
			 else if($('aclose')){
				T$('aclose').onclick = function()
				{
				TINY.box.alpha(p,-1,0,3)
				}
			}
                
		/*Ajax Login Function */
		function loginAjax() {		
			var valid = new Validation('login-form');
			 if(valid.validate()){
			    var request = new Ajax.Request(
				 URL + "customer/account/ajaxLogin",
				{
				    method:'post',
				    onComplete: function(){
				       
				    },
				    onSuccess: function(transport){
				       if (transport && transport.responseText){
					 try{
					    response = eval('(' + transport.responseText + ')');
					  }
					  catch (e) {
						response = {};
					  }
					}
					
					if (response.success){
//					   alert('Successfully Loggedin');

                        var htmlvar = 'You Are Logged In';
                        var htmlvaralt = '';

                        jQuery("<div class='reg-success-msg-overlay'><div class='reg-success-msg'><p>" + htmlvar + "</p><span>" + htmlvaralt + "</span></div></div>").appendTo('body');


					   redirectTime = "3000";
                       redirectURL = URLindex;
					   setTimeout("location.href = redirectURL;",redirectTime);
					}else{
					    if ((typeof response.message) == 'string') {
//						alert(response.message);

                            var htmlvar = 'Login Error';

                            jQuery("<div class='reg-success-msg-overlay'><div class='reg-success-msg'><p>" + htmlvar + "</p><span>" + response.message + "</span><div class='close-notice-pass-send'>OK</div></div></div>").appendTo('body');

                            jQuery(document).on("click",".close-notice-pass-send", function(){
                                jQuery(".reg-success-msg-overlay").remove();
                            });


					    } 
					    return false;
					}
				    },
				    onFailure: function(){
				      alert("Failed");
				    },
				    parameters: Form.serialize('login-form')
				}
			      );
			  }else{
			 
			    return false;
			  }
			  
		}	
            /*Ajax Register Customer Function */
                function registerAjax() {		
						
					 var valid = new Validation('regis-form');
					 if(valid.validate()){
						  var request = new Ajax.Request(
						URL + "customer/account/ajaxCreate",
						{
							method:'post',
							onComplete: function(){
							   
							},
							onSuccess: function(transport){
							   if (transport && transport.responseText){
							 try{
								response = eval('(' + transport.responseText + ')');
							  }
							  catch (e) {
								response = {};
							  }
							}
							
							if (response.success){

                                var htmlvar = 'Registration Successful';
                                var htmlvaralt = 'Thank you for registering.';

                                jQuery("<div class='reg-success-msg-overlay'><div class='reg-success-msg'><p>" + htmlvar + "</p><span>" + htmlvaralt + "</span></div></div>").appendTo('body');


								   redirectTime = "4000";
								   redirectURL = URL;
								   //redirectURL = URL + "customer/account";
								   setTimeout("location.href = redirectURL;",redirectTime);
							    }else{
								if ((typeof response.message) == 'string') {
								alert(response.message);
								} 
								return false;
							}
							},
							onFailure: function(){
							  alert("Failed");
							},
							parameters: Form.serialize('regis-form')
						}
						  );
					  }else{
					 
						return false;
					  }
			  
		        }	
		/*Forget Password Function */
		function forgetpass(){	
			var req2 = new Ajax.Request(URL + "customer/account/ajaxForgotPassword/",
			 {
				method:'post',
				parameters: $('forgot-form').serialize(true) ,
				onSuccess: function(transport){
				   var response = eval('(' + transport.responseText + ')');
				   if(response.success){
//					  alert(response.message);
//					  TINY.box.hide();

                       var htmlvar = 'Password recovery';

                       jQuery("<div class='reg-success-msg-overlay'><div class='reg-success-msg'><p>" + htmlvar + "</p><span>" + response.message + "</span><div class='close-notice-pass-send'>OK</div></div></div>").appendTo('body');

                       jQuery(document).on("click",".close-notice-pass-send", function(){
                           jQuery(".reg-success-msg-overlay").remove();
                           TINY.box.hide();
                       });

				   }else{
//					 alert(response.message);

                       var htmlvar = 'Email Error';

                       jQuery("<div class='reg-success-msg-overlay'><div class='reg-success-msg'><p>" + htmlvar + "</p><span>" + response.message + "</span><div class='close-notice-pass-send'>OK</div></div></div>").appendTo('body');

                       jQuery(document).on("click",".close-notice-pass-send", function(){
                           jQuery(".reg-success-msg-overlay").remove();
                       });

				   }
				},
				onFailure: function(){ alert('Something went wrong...') }
			 });
 
        }

// added for multiple instances of login/registration links
jQuery(function(){
    jQuery(".necessary-login").attr("onclick", "return false;");
    jQuery(document).on("click",".necessary-login",function(e) {
        var prForm = jQuery(this).closest("form");
        e.preventDefault();
        TINY.box.show({url: checkurl ,width:550,height:100,opacity:20,topsplit:10});
        jQuery('html,body').animate({ scrollTop: 0 }, 'fast');
        setTimeout(function(){
            jQuery(".tinybox").append("<div class='warning-add-to-cart'>To add an item to the cart you need to login or register.</div>");
        },1500);
    });

    jQuery(document).on("click",".necessary-login-review, .necessary-login-all",function(e) {
        e.preventDefault();
        TINY.box.show({url: checkurl ,width:550,height:100,opacity:20,topsplit:10});
        jQuery('html,body').animate({ scrollTop: 0 }, 'fast');
        jQuery("#remember_me").uniform();jQuery.uniform.update();
    });


    if (jQuery('body').hasClass('body-login-required')) {
        TINY.box.show({url: checkurl ,width:550,height:100,opacity:20,topsplit:10});
        jQuery('html,body').animate({ scrollTop: 0 }, 'fast');
        jQuery("#remember_me").uniform();jQuery.uniform.update();
        setTimeout(function(){
            jQuery(".tinybox").append("<div class='warning-add-to-cart'>Please login in order to use all of the sites functionality.</div>");
        },1500);
    }





    jQuery(document).on('click','.tinymask', function(){
        jQuery(".warning-add-to-cart").remove();
    });


    jQuery(document).on("keypress","#login-form input",function(e) {
        if (e.which == 13) {
            jQuery("form#login-form .action-btn").click();
        }
    });

    jQuery(document).on('click','#login-form #remember_me', function() {
        if (jQuery('#login-form #remember_me').is(':checked')) {
            // save username and password
            localStorage.usrname = jQuery('#login-form #email').val();
            localStorage.pass = jQuery('#login-form #pass').val();
            localStorage.chkbx = jQuery('#login-form #remember_me').val();
        } else {
            localStorage.usrname = '';
            localStorage.pass = '';
            localStorage.chkbx = '';
        }
    });
});