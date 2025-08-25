var object_function_ajax_path='/';
function ObjectFunctionAjaxSetPath(path)
{
	object_function_ajax_path=path;	
}

function ObjectFunctionAjax(object,object_id,fn,target_id,formid,loadingclass,addparams,callback_fn)
{
/*  
	var params=new Object();
	var inputs=jQuery('#'+formid+' INPUT');
	for(var i=0;i<inputs.length;i++)
	{
		if(inputs[i].type=='radio')
		{
		   	if(inputs[i].checked)
			  	params[inputs[i].name]=inputs[i].value;
		}
		else if(inputs[i].type=='checkbox')
		{
		   	if(inputs[i].checked)
			  	params[inputs[i].name]=inputs[i].value;
		   	else
			  	params[inputs[i].name]=0;
		}
		else
		  	params[inputs[i].name]=inputs[i].value;
	}
	var selects=jQuery('#'+formid+' SELECT');
	for(var i=0;i<selects.length;i++)
	  	params[selects[i].name]=selects[i].value;	
	var textareas=jQuery('#'+formid+' TEXTAREA');
	for(var i=0;i<textareas.length;i++)
	  	params[textareas[i].name]=textareas[i].value;
*/
//	if(loadingtext)
//		jQuery('#'+target_id).html("<div class='loading'><div class='loading_inner'>"+loadingtext+"</div></div>");
	if(loadingclass)
		jQuery('#'+target_id).addClass(loadingclass);
	else
	{
		jQuery('#'+target_id).addClass('loading');
		adjustBackgroundPosition(jQuery('#'+target_id));
	}
		
	var params=jQuery('#'+formid).serialize();
	var checkboxes=jQuery('#'+formid+' input[type=checkbox]');
	for(var i=0;i<checkboxes.length;i++)
	{
	   	if(!checkboxes[i].checked)
		  	params+='&'+checkboxes[i].name+'=0';
	}


	var url=object_function_ajax_path+'ajax/ObjectFunction.php?object='+object+'&object_id='+object_id+'&object_function='+fn+'&'+addparams;

	var fd = new FormData(jQuery('#'+formid).get(0)); 	
	var checkboxes=jQuery('#'+formid+' input[type=checkbox]');
	for(var i=0;i<checkboxes.length;i++)
	{
	   	if(!checkboxes[i].checked)
		  	fd.append(checkboxes[i].name,0)
	}
    $.ajax({
        url: url,
        type: 'POST',
        data: fd,
        dataType: 'html',
        success:function(data){
			jQuery('#'+target_id).html(data);
			jQuery('#'+target_id).removeClass('loading');
		 	if(callback_fn)
		 		callback_fn();
		 	if(AttachBehaviors)
		 		AttachBehaviors(jQuery('#'+target_id),target_id);
        },
        error:function(data){
			jQuery('#'+target_id).html(data);
			jQuery('#'+target_id).removeClass('loading');
		 	if(callback_fn)
		 		callback_fn();
		 	if(AttachBehaviors)
		 		AttachBehaviors(jQuery('#'+target_id),target_id);
        },
        cache: false,
        contentType: false,
        processData: false
    });
}

function adjustBackgroundPosition(container) 
{
	if(!container || !container.get(0))
		return;

    const containerRect = container.get(0).getBoundingClientRect();
    const viewportHeight = $(window).height();

    const containerTop = containerRect.top;
    const containerHeight = container.outerHeight();

    if (containerHeight > viewportHeight) 
	{
        // Center the background image in the viewport
        const centerY = viewportHeight / 2 - containerTop;
        container.css('background-position', `center ${centerY}px`);
    } 
	else 
	{
        // Center the background image in the container
        container.css('background-position', 'center center');
    }
}

function ObjectFunctionAjaxPopup(headline,object,object_id,fn,formid,loadingtext,addparams,callback_fn,popup_class,overall_popup_class)
{	
	if(jQuery('#popup').hasClass('modal'))
	{
		jQuery('#popup').removeClass();
		jQuery('#popup').addClass('modal');
		jQuery('#popup').addClass(overall_popup_class);
	 	if(overall_popup_class=='modeless')	
	 		jQuery('#popup').modal({backdrop: 'static',keyboard: false});
		else
	 		jQuery('#popup').modal('show');
		if(!jQuery('#popup').hasClass('show'))
			jQuery('#popup_content').html('');
	}
	else
		jQuery('#popup').css({display:'block'});

	jQuery('#popup_dialog').attr('class','modal-dialog');
	jQuery('#popup_dialog').addClass(popup_class);
	jQuery('#popup_headline').html(headline);
	ObjectFunctionAjax(object,object_id,fn,'popup_content',formid,loadingtext,addparams,callback_fn);
}

function PopupClose()
{	
	if(jQuery('#popup').hasClass('modal'))
 		jQuery('#popup').modal('hide');
	else
		jQuery('#popup').css({display:'none'});
	jQuery('#popup_content').html('');
}

function ObjectFunctionAjaxFile(source,object,object_id,fn,target_id,formid,loadingtext,addparams,callback_fn)
{
	if(loadingtext)
		jQuery('#'+target_id).html("<div class='loading'><div class='loading_inner'>"+loadingtext+"</div></div>");
	else
		jQuery('#'+target_id).addClass('loading');
		
	var url=object_function_ajax_path+'ajax/ObjectFunction.php?object='+object+'&object_id='+object_id+'&object_function='+fn+'&'+addparams;
	
	var fd = new FormData(); 
	fd.append($(source)[0].name, $(source)[0].files[0]);
    //fd.append("CustomField", "This is some extra data");
    $.ajax({
        url: url,
        type: 'POST',
        data: fd,
        dataType: 'html',
        success:function(data){
			jQuery('#'+target_id).html(data);
				jQuery('#'+target_id).removeClass('loading');
			if(callback_fn)
				callback_fn();
        },
        error:function(data){
			jQuery('#'+target_id).html(data);
				jQuery('#'+target_id).removeClass('loading');
			if(callback_fn)
				callback_fn();
        },
        cache: false,
        contentType: false,
        processData: false
    });
}

