/*parsed HTML*/
$(function(){
	//DONE in CODE.$(".client_dashboard .client_dashboard_body .col").each(function(){$(this).contents().wrapAll("<div class='box_inner'></div>");})
	//DONE in CODE.$(".cards_list .card").each(function(){$(this).contents().wrapAll("<div class='box_inner'></div>");})
	AttachBehaviors(jQuery('BODY'),'');
})

$(function(){
	$("HTML").on("dragover", function(event) {
	    event.preventDefault();  
	    event.stopPropagation();
	});
	$("HTML").on("dragleave", function(event) {
	    event.preventDefault();  
	    event.stopPropagation();
	});
  	$('HTML').on('drop',function(event){
		event.preventDefault();  
		event.stopPropagation();
	});	
	
  	$('BODY').delegate('.drop_target','drop',function(event){
		event.preventDefault();  
		event.stopPropagation();
	   	FileDrop(event,this);
	});
  	$('BODY').delegate('.drop_target','dragover',function(event){
	    $(this).addClass('drop_target_dragover');
	});
  	$('BODY').delegate('.drop_target','dragleave',function(event){
	    $(this).removeClass('drop_target_dragover');
	});	
});

$(window).bind("load", function(){
	jQuery("#loader").fadeOut(function(){jQuery("#loader").remove();});
});


$(window).bind("scroll", scroll_handler).bind("load", scroll_handler)
function scroll_handler(){
	jQuery('BODY').removeClass('scrolled');
	if(jQuery(window).scrollTop()>0)
		jQuery('BODY').addClass('scrolled');
	if(jQuery('BODY').scrollTop()>0)
		jQuery('BODY').addClass('scrolled');
}

$(function(){
 	sidebar_handler('load');
	//menu and mini cart overlays
	jQuery(window).scroll(function(){
		sidebar_handler('scroll');		
	});
	jQuery(window).resize(function(){
		sidebar_handler('resize');		
	});
});

var sidebar_offset=0;
function sidebar_handler(type)
{
	var outer=jQuery("#progress_meter_container_outer");
	var inner=jQuery("#progress_meter_container");
	if(outer.length && inner.length)
	{
		var offset = outer.offset();
		var scrollTop = $(window).scrollTop();
		var top=offset.top-scrollTop;
		var pad=25;
		
		if($(window).width()>=767)
		{
			if(top<pad)
				jQuery(inner).css({top: ((0-top)+pad)+'px'});        
			else
				jQuery(inner).css({top: '0px'});        
		}
	    ////console.log(type+' offs:'+sidebar_offset+' pad:'+topPadding+' scroll:'+scrolltop+' set:'+top+' diff:'+diff);
	}
}



/*add event*/
$(window).bind("resize", height_handler).bind("load", height_handler)
function height_handler(){
/*
	var pad=5;
	var nav_links=$("DIV.navbar-collapse>UL>LI>A");
	var w=$("DIV.navigation_area UL.nav").width();
	if($(window).width()>767)
	{
		var total=0;
		for(var i=0;i<nav_links.length;i++)
			total+=$(nav_links.get(i)).width();
		pad=Math.floor(((w-total)/nav_links.length)/2);
		pad=Math.max(pad,5);
		
	}
	$("DIV.navbar-collapse>UL>LI>A").css({'padding-left':pad+'px','padding-right':pad+'px'});
*/

	if($(window).width()<=767)
		jQuery(".navbar .navbar-collapse").css({top:jQuery(".header_area").outerHeight()-11});

	$(".cards_list .card").noHeights();
	if(jQuery('BODY').width()>767)
		$(".cards_list .card").equalHeights();


	$(".client_dashboard .client_dashboard_body .col").equalHeights();
	$(".client_dashboard .client_dashboard_body .col").valignCenter('.box_inner');		
	
	$('TABLE.mceLayout').each(function(){
		$(this).width($(this).parent('.mceEditor').width());
	})
	
	
	if(!jQuery('.timeline_row .timeline_col .wysiwyg_input').length)
	{
		if($(window).width()>767)
			jQuery('.timeline_row .sidebar_col').before(jQuery('.timeline_row .timeline_col'));	
		else
			jQuery('.timeline_row .timeline_col').before(jQuery('.timeline_row .sidebar_col'));	
	}

	jQuery("#popup_content .agent_tools A").equalHeights();
	jQuery("#popup_content .agent_tools DIV.box_inner").css({display:'inline-block'});
}
/*glob function*/

(function($) {
    $.fn.isAfter = function(sel){
        return this.prevAll().filter(sel).length !== 0;
    };

    $.fn.isBefore= function(sel){
        return this.nextAll().filter(sel).length !== 0;
    };
})(jQuery);

(function($){
	$.fn.equalHeights=function(minHeight,maxHeight){
		tallest=(minHeight)?minHeight:0;
		this.each(function(){
		 	if($(">.box_inner", this).length==0)
				$(this).each(function(){$(this).contents().wrapAll("<div class='box_inner'></div>");})
			if($(">.box_inner", this).outerHeight()>tallest)
				tallest=$(">.box_inner", this).outerHeight()
		});
		if((maxHeight)&&tallest>maxHeight) tallest=maxHeight;
		return this.each(function(){$(this).height(tallest)})
	}
	$.fn.noHeights=function(){
		return this.each(function(){$(this).height('')})
	}
	$.fn.equalHeightsN=function(n,minHeight,maxHeight){
		tallest=(minHeight)?minHeight:0;
		var cnt=0;
		var cntstart=0;
		for(var i=0;i<this.length;i++)
		{			
			cnt++;			
			if($(">.box_inner", jQuery(this.get(i))).outerHeight()>tallest)
			{
				tallest=$(">.box_inner", jQuery(this.get(i))).outerHeight()
			}
			if((cnt==n) || i==(this.length-1))
			{
				for(var j=0;j<n;j++)			
				{
					jQuery(this.get(j+cntstart)).height(tallest);
				}
				tallest=(minHeight)?minHeight:0;
				var cntstart=i+1;
				var cnt=0;
			}
		}
		return this;
	}
	$.fn.valignCenter=function(selector){
		this.each(function(){
			var img=jQuery(selector, this);
			var half=(jQuery(this).height()-img.height())/2;
			if(half>0)
				img.css({'margin-top':half+'px','margin-bottom':half+'px'});
		});
	}		
})(jQuery)


function UpdateWYSIWYG(context)
{
	jQuery('.wysiwyg_input',context).each(function(){
		if(tinymce.get(this.id))
			jQuery('#'+this.id).val(tinymce.get(this.id).getContent());
	});	
}

function FileDrop(event,element)
{
	var files = event.originalEvent.dataTransfer.files;
	var target=jQuery(element).attr('data-target');
	jQuery('#'+target).get(0).files=files;
	jQuery('#'+target).change();	
	jQuery(element).addClass('loading');
}

function AttachBehaviors(context,id)
{
	$('INPUT,SELECT,TEXTAREA',context).each(function(){
		var placeholder=jQuery(this).attr('placeholder');
		var tooltip=jQuery(this).attr('title');
		if(placeholder && !tooltip)
		{
			var tooltip=jQuery(this).attr('title',placeholder);
			var tooltip=jQuery(this).attr('data-toggle','tooltip');
		}
	})

    $(".datepicker",context).datepicker();
	$('.colorpicker',context).each(function(){
	 	$(this).minicolors({
			format:'hex',
			defaultValue:jQuery('INPUT',jQuery(this).parent()).val(),
			change: function(value, opacity) {
				if( !value ) return;
				//if( opacity ) value += ', ' + opacity;
				jQuery('INPUT',jQuery(this).parent()).val(value);
			},
			hide: function() {
				jQuery('INPUT',jQuery(this).parent()).trigger('change');
			},			
		});
	});

  	$('[data-toggle="tooltip"]',context).tooltip();
	$('[data-info]',context).focus(function(){
		InfoBubble(this,jQuery(this).attr('data-info'));
	})	
	$('[data-info-click]',context).click(function(){
		InfoBubble(this,jQuery(this).attr('data-info'));
	})	
	$('[data-info-hover]',context).hover(function(){
		InfoBubble(this,jQuery(this).attr('data-info'));
	})	

	//cheating here.  Create the missing ones!	
	var create_info_bubbles=false;
	if(create_info_bubbles)
	{
		$('[data-info]',context).each(function(){	
		 	var key=jQuery(this).attr('data-info');
		 	var section=jQuery('BODY').attr('info_bubble_section');
	
			if(!jQuery('#'+key).length)//could redraw the info bubbles, but...nah
				ObjectFunctionAjax('info_bubble','','Create','','NULL','','key='+key+'&section='+section,function(){});
		});
	}

	height_handler();
//	$('[data-info]').blur(function(){
//		InfoBubbleClose();
//	})		
}

function InfoBubble(element,key)
{
	if(jQuery('BODY').hasClass('hide_info_bubbles'))
		return false;

	jQuery('.info_bubble_showing').removeClass('info_bubble_showing');
	jQuery(element).addClass('info_bubble_showing');

//	if(jQuery('BODY').hasClass('DEMO'))
	{
		InfoBubblePosition(key) 
	}
	if(jQuery('.info_bubble_active').length)
	{
		jQuery('.info_bubble_active').fadeOut(100,function(){
			jQuery('.info_bubble_active').removeClass('info_bubble_active');
			jQuery('#'+key).fadeIn(100);
		 	jQuery('#'+key).addClass('info_bubble_active');		 		 	
			//scrollIntoViewIfNeeded(jQuery(element));

		});
	}
	else
	{
		jQuery('#'+key).fadeIn(100);
	 	jQuery('#'+key).addClass('info_bubble_active');
		//scrollIntoViewIfNeeded(jQuery(element));
	}

	jQuery('#'+key+' .info_bubble_previous').css({display:'none'});
	var prev=jQuery('#'+key).prev('.info_bubble');
	if(prev.length)
	{
		var prev_target=prev.get(0).id;
		var prev_target_element=jQuery('[data-info='+prev_target+']');
		if(prev_target_element.length)
			jQuery('#'+key+' .info_bubble_previous').css({display:'block'});
	}

	jQuery('#'+key+' .info_bubble_next').css({display:'none'});
	var next=jQuery('#'+key).next('.info_bubble');
	if(next.length)
	{
		var next_target=next.get(0).id;
		var next_target_element=jQuery('[data-info='+next_target+']');
		if(next_target_element.length)
			jQuery('#'+key+' .info_bubble_next').css({display:'block'});
	}
	
	InfoBubblePosition(key);
	
}

function InfoBubblePosition(key)
{
	if(!key)
	{
		if(!jQuery('.info_bubble_active').length)
			return;
		key=jQuery('.info_bubble_active').get(0).id;
	}
	var element=jQuery('.info_bubble_showing').get(0);
	
	var pos=jQuery(element).offset();
	var width=jQuery(element).outerWidth();
	var height=jQuery(element).outerHeight();
	var scroll=jQuery('window').scrollTop();
	var scroll=$(document).scrollTop();

	var popup_width=jQuery('.info_bubble_active').outerWidth();

	var new_top=pos.top-scroll;
	var new_left=pos.left+width+10;
	
	if(jQuery('BODY').hasClass('DEMO'))
		return jQuery('#'+key).css({top:new_top});	


	if(new_left+popup_width>jQuery(window).width())
	{
		//go right uinder it.
		new_top+=height+10;
		new_left=pos.left;
		//
		if(new_left+popup_width>jQuery(window).width())
			new_left=jQuery(window).width()-popup_width-10;
	}
	jQuery('#'+key).css({top:new_top,left:new_left});	
}

$(window).bind("resize", info_bubble_handler).bind("scroll", info_bubble_handler)
function info_bubble_handler(){
	InfoBubblePosition('');
}
function InfoBubbleSelect(key)
{
	var context=jQuery('.info_bubble_showing').parent('info_group');
	if(context && jQuery('[data-info='+key+']',context).length)
		jQuery('[data-info='+key+']',context).focus();
	else
	{
		var item=jQuery('[data-info='+key+']').get(0);
		if(jQuery(item).attr('data-info-click'))
		{
			jQuery(item).click();
			scrollIntoViewIfNeeded(jQuery(item));
		}
		else if(jQuery(item).attr('data-info-none'))
		{
			InfoBubble(item,jQuery(item).attr('data-info'))
			scrollIntoViewIfNeeded(jQuery(item));
		}
		else
			jQuery(item).focus();
	}
}

function InfoBubbleNext()
{
	var next=jQuery('.info_bubble_active').next('.info_bubble');
	InfoBubbleSelect(next.get(0).id);
}

function InfoBubblePrev()
{
	var prev=jQuery('.info_bubble_active').prev('.info_bubble');
	InfoBubbleSelect(prev.get(0).id);
}


function InfoBubbleClose()
{
	jQuery('.info_bubble_showing').removeClass('info_bubble_showing');
 	jQuery('.info_bubble').fadeOut(100);
 	jQuery('.info_bubble').removeClass('info_bubble_active');
}


function scrollIntoViewIfNeeded($target) {
    if ($target.offset()) {
        if ($target.offset().top < jQuery(window).scrollTop()){
            //scroll up
            $('html,body').animate({scrollTop: $target.offset().top});
        }
        else if ($target.offset().top + $target.height() >
            $(window).scrollTop() + (
                window.innerHeight || document.documentElement.clientHeight
            )) {
            //scroll down
            $('html,body').animate({scrollTop: $target.offset().top -
                (window.innerHeight || document.documentElement.clientHeight)
                    + $target.height() + 15}
            );
        }
    }
}

function __X__scrollIntoViewIfNeeded($target) {
    if ($target.position()) {
        if ($target.position().top < jQuery(window).scrollTop()){
            //scroll up
            $('html,body').animate({scrollTop: $target.position().top});
        }
        else if ($target.position().top + $target.height() >
            $(window).scrollTop() + (
                window.innerHeight || document.documentElement.clientHeight
            )) {
            //scroll down
            $('html,body').animate({scrollTop: $target.position().top -
                (window.innerHeight || document.documentElement.clientHeight)
                    + $target.height() + 15}
            );
        }
    }
}

$(function(){
 	$('BODY').delegate('.has-flare','click',function(event){
		ShowFlare(event.target,false);
	});
});
var flare_count=0
function ShowFlare(element,callback_fn)
{
	if(jQuery(element).hasClass('flare-action'))
		null;
//	else if(!element.checked)
//		return;
	flare_count++;
	if(flare_count>=1)
	{	 	
		var rand=Math.floor(Math.random() * (jQuery("DIV.flare").length));
		var flare=jQuery("DIV.flare").get(rand);
		var pos=jQuery(element).offset();
		var width=jQuery(element).width();
		var height=jQuery(element).height();
		if(jQuery(flare).hasClass('flare_gif'))
		{
			jQuery("IMG",flare).attr('src',jQuery("IMG",flare).attr('data-src')+'?'+(new Date).getTime());
			jQuery(flare).css({display:'block',opacity:0,top:pos.top+height/2,left:pos.left+width/2});
			jQuery(flare).animate({opacity:1},500,function(){
				window.setTimeout(function(){
					jQuery(flare).animate({display:'none',opacity:0},1000,function(){
						jQuery(flare).css({display:'none',top:0,left:0});
						if(callback_fn)
							callback_fn();	
					});
				},1000);
			});

		}
		else if(jQuery(flare).hasClass('flare_json'))
		{
			jQuery("LOTTIE-PLAYER",flare).load(jQuery("LOTTIE-PLAYER",flare).attr('data-src')+'?'+(new Date).getTime());
			jQuery(flare).css({display:'block',opacity:0,top:pos.top+height/2,left:pos.left+width/2});
			jQuery(flare).animate({opacity:1},500,function(){
				window.setTimeout(function(){
					jQuery(flare).animate({display:'none',opacity:0},1000,function(){
						jQuery(flare).css({display:'none',top:0,left:0});	
						if(callback_fn)
							callback_fn();	
					});
				},1000);
			});
		}
		else if(jQuery(flare).hasClass('flare_json'))
		{
			jQuery("#flare_json").load(jQuery(flare).attr('data-src')+'?'+(new Date).getTime());
			jQuery("#flare_json").css({display:'block',opacity:0,top:pos.top+height/2,left:pos.left+width/2});
			jQuery("#flare_json").animate({opacity:1},500,function(){
				window.setTimeout(function(){
					jQuery("#flare_json").animate({display:'none',opacity:0},1000,function(){
						jQuery("#flare_json").css({display:'none',top:0,left:0});	
						if(callback_fn)
							callback_fn();	
					});
				},1000);
			});
		}
		
		var rand=Math.floor(Math.random() * (jQuery("AUDIO.flare_sound").length));
		var audio_flare=jQuery("AUDIO.flare_sound").get(rand);
		if(audio_flare)
		    audio_flare.play();		
		
		flare_count=0;
	}
}

function CongratulationsFlare(element,callback_fn)
{
	var flare=jQuery("#congratulations-flare").get(0);
	var pos=jQuery(element).offset();
	var width=jQuery(element).width();
	var height=jQuery(element).height();

	jQuery("LOTTIE-PLAYER",flare).load(jQuery("LOTTIE-PLAYER",flare).attr('data-src')+'?'+(new Date).getTime());
	jQuery(flare).css({display:'block',opacity:0});
	jQuery(flare).animate({opacity:1},500,function(){
		window.setTimeout(function(){
			jQuery(flare).animate({display:'none',opacity:0},1000,function(){
				jQuery(flare).css({display:'none'});	
				if(callback_fn)
					callback_fn();	
			});
		},1000);
	});

	var audio_flare=jQuery("#congratulations-sound").get(0);
	if(audio_flare)
	    audio_flare.play();		
}


function DeleteFlare(element,callback_fn)
{
	var flare=jQuery("#delete-flare").get(0);
	var pos=jQuery(element).offset();
	var width=jQuery(element).width();
	var height=jQuery(element).height();

	jQuery("LOTTIE-PLAYER",flare).load(jQuery("LOTTIE-PLAYER",flare).attr('data-src')+'?'+(new Date).getTime());
	jQuery(flare).css({display:'block',opacity:0,top:pos.top+height/2,left:pos.left+width/2});
	jQuery(flare).animate({opacity:1},100,function(){
		window.setTimeout(function(){
			jQuery(flare).animate({display:'none',opacity:0},1000,function(){
				jQuery(flare).css({display:'none',top:0,left:0});	
				if(callback_fn)
					callback_fn();	
			});
		},1500);
	});

	var audio_flare=jQuery("#delete-sound").get(0);
	if(audio_flare)
	    audio_flare.play();		
}

var progress_timeout_length=3000;
var onetime_timeouts=new Array();
function SetOneTimeTimeout(which,callback,timeout)
{
	if(onetime_timeouts[which])
		window.clearTimeout(onetime_timeouts[which]);
	onetime_timeouts[which]=window.setTimeout(callback,timeout);	
}



$(function(){
	jQuery("#progress_meter_container_mobile").click(function(){
		jQuery("#progress_meter_container_mobile").toggleClass('progress_meter_mobile_expanded');
	});
})

function ExpandMobileProgressMeter()
{
	jQuery("#progress_meter_container_mobile").addClass('progress_meter_mobile_expanded');	
	SetOneTimeTimeout('ProgresssMeter',function(){
		jQuery("#progress_meter_container_mobile").removeClass('progress_meter_mobile_expanded');
	},5000);
}
