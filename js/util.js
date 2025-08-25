var show_one=new Array();

function ShowOneOf(id,group,display,closesame,activeclassname)
{
	if(!display)	display='block';

	if(show_one[group])
	{
		if(display=='accordian')
			jQuery('#'+show_one[group]).slideUp('normal');
		else
			jQuery('#'+show_one[group]).css({display:'none'});  	  

		if(activeclassname)
			jQuery('#'+show_one[group]+'_link').removeClass(activeclassname);

		old=show_one[group];
		show_one[group]='';
		if(old==id && closesame)  
			return;		
	}

	if(display=='accordian')
		jQuery('#'+id).slideDown('normal');
	else
		jQuery('#'+id).css({display:display});  	  		
	show_one[group]=id;

	if(activeclassname)
		jQuery('#'+show_one[group]+'_link').addClass(activeclassname);

}

function MoveTo(obj,to_obj,context)
{
	var jcontext=jQuery(context);
	var jobj=jQuery(obj);
	var jto_obj=jQuery(to_obj);

	var w=jcontext.width();
	var h=jcontext.height();
	var mw=jobj.width();
	var mh=jobj.height();
	var tw=jto_obj.width();
	var th=jto_obj.height();
	var pos=jto_obj.position();

	if(mh+pos.top>h)	tt=pos.top-mh;
	else				tt=pos.top+th;
	if(mw+pos.left>w)	tl=(pos.left-mw)+tw;
	else				tl=pos.left;

	jobj.css({top:tt+'px',left:tl+'px'})
}