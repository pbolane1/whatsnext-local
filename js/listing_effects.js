/***********/
/* Globals */
/***********/
var row_highlight_enabled=false;
var last_row_highlight='';
var button_highlight_enabled=false;
var last_button_highlight='';

/*****************/
/* JQUERY onload */
/*****************/
function EnableRowHighlight()
{
	row_highlight_enabled=true;
  	//attach mouse move enter/leave
    $("TABLE.listing TR.list_item").bind("mouseenter",
	   function(event) 
	   {	     	     
			if(last_row_highlight)
				jQuery(last_row_highlight).removeClass('row_hover');
			jQuery(this).addClass('row_hover');
			last_row_highlight=this;
	   }
   	);
	
    $("TABLE.listing TR.list_item").bind("mouseleave",
	   function(event) 
	   {	     	     
			jQuery(this).removeClass('row_hover');
			last_row_highlight='';
	   }
   	);

}

function EnableButtonHighlight()
{
	button_highlight_enabled=true;
  	//attach mouse move enter/leave
    $("TABLE.listing INPUT.button").bind("mouseenter",
	   function(event) 
	   {	     	     
			if(last_button_highlight)
				jQuery(last_button_highlight).removeClass('button_hover');
			jQuery(this).addClass('button_hover');
			last_button_highlight=this;
	   }
   	);
	
    $("TABLE.listing INPUT.button").bind("mouseleave",
	   function(event) 
	   {	     	     
			jQuery(this).removeClass('button_hover');
			last_button_highlight='';
	   }
   	);
    $("TABLE.listing INPUT.submit").bind("mouseenter",
	   function(event) 
	   {	     	     
			if(last_button_highlight)
				jQuery(last_button_highlight).removeClass('button_hover');
			jQuery(this).addClass('button_hover');
			last_button_highlight=this;
	   }
   	);
	
    $("TABLE.listing INPUT.submit").bind("mouseleave",
	   function(event) 
	   {	     	     
			jQuery(this).removeClass('button_hover');
			last_button_highlight='';
	   }
   	);   	

}