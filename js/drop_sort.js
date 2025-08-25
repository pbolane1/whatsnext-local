/***********/
/* Globals */
/***********/
var dropsort_dragging=false;
var dropsort_dragging_item='';
var dropsort_dragging_item_index='';
var dropsort_dragging_item_container=''
var dropsort_drag_goback=false;
var dropsort_lastcursorX=0;
var dropsort_lastcursorY=0;
var dropsort_draggables=new Array();



/*****************/
/* JQUERY onload */
/*****************/

$(document).ready(function(){
  	//attach mouse move to body - if dragging, then drag when the mouse moves
    $("BODY").mousemove(
	   function(event) 
	   {	     	     
		    if(dropsort_dragging)
				DropSortDragItem(event.pageX,event.pageY);
	   }
   	);

	//attach mouse move to body - if dragging, then drop when the mouse button is let up
    $("BODY").mouseup(
	   function(event) 
	   {	     	     
		    if(dropsort_dragging)
				DropSortEndDragItem(event.pageX,event.pageY);
	   }
   	);
});


/**********************************/
/* Enable Dragging on a container */
/**********************************/

function DropSortEnable(container,barclass,objclass,sortclass,ajaxurl)
{
  	////////////////////////
    //DRAG AND DROP EVENTS
    ////////////////////////

	//save params for use in helper functins
	dropsort_draggables[container]=new Array();
	dropsort_draggables[container]['barclass']=barclass;
	dropsort_draggables[container]['objclass']=objclass;
	dropsort_draggables[container]['sortclass']=sortclass;
	dropsort_draggables[container]['ajaxurl']=ajaxurl;

    //initiate dragging via the edit bar
    //fix event 
    $("#"+container+" DIV."+barclass).mousedown(
	   function(event) 
	   {	     	     
	     	if(event.srcElement)
			 	event.originalTarget=event.srcElement;
	     	if(event.originalTarget.id==this.id)
	     	{
	     	  	//remember who is dragging and start dragging them
	     	  	dropsort_dragging_item_container=container;
	    		dropsort_dragging_item_index=$("#"+container+" DIV."+barclass).index($(this)[0]);
			    thebox=$("#"+container+" DIV."+objclass).get(dropsort_dragging_item_index);
				DropSortStartDragItem(thebox,event.pageX,event.pageY);
			}
	   }
   	);
}

/************************/
/*  helper functions    */
/************************/
function DropSortStartDragItem(item,cursorX,cursorY)
{	
	//begin dragging the item
	dropsort_dragging_item=item;
	dropsort_dragging=true;

	if(dropsort_dragging && dropsort_dragging_item)
	{
	  	//set sragging style and class
		dropsort_dragging_item.style.zIndex=10000;	
		dropsort_dragging_item.style.position='absolute';			
		jQuery(dropsort_dragging_item).addClass(dropsort_draggables[dropsort_dragging_item_container]['objclass']+'_dragging');

		//get the placehoder where we were and update our position
		DropSortPlaceholder(dropsort_dragging_item,true);
		DropSortPositionUpdate(cursorX,cursorY);
	}
}

function DropSortDragItem(cursorX,cursorY)
{
  	//if dragging, update position
	if(dropsort_dragging && dropsort_dragging_item)
		DropSortPositionUpdate(cursorX,cursorY); 
}

function DropSortPositionUpdate(cursorX,cursorY)
{
  	//move dragged guy to cursor
	cursorX-=20;
	cursorY-=20;
		
	dropsort_dragging_item.style.left=cursorX;
	dropsort_dragging_item.style.top=cursorY; 

	return;
	
	//alternate strategy - put plce holder where we are hovering.
	//needs work...?
	/*
	if(Math.abs(dropsort_lastcursorX-cursorX)>100 || Math.abs(dropsort_lastcursorY-cursorY)>100)
	{
		theindex=DropSortDragTarget(cursorX,cursorY);
	
		//move place holder
		var list=$("#"+dropsort_dragging_item_container+" DIV."+dropsort_draggables[dropsort_dragging_item_container]['objclass']);
		DropSortPlaceholder(list.get(theindex),true);

		dropsort_lastcursorX=cursorX;
		dropsort_lastcursorY=cursorY;
	}
	*/
}

function DropSortEndDragItem(cursorX,cursorY)
{

	
	//remove place holder, replace image in list	
	DropSortSortTo(dropsort_dragging_item,DropSortDragTarget(cursorX,cursorY));
	DropSortPlaceholder('',false);
			
	//DONE!
	//relative positioning
	if(dropsort_dragging && dropsort_dragging_item)
	{
		dropsort_dragging_item.style.position='relative';	
		dropsort_dragging_item.style.top=0;
		dropsort_dragging_item.style.left=0;	
		dropsort_dragging_item.style.zIndex=0;	
	}	
	//remove dragging class and dragging styles
	jQuery(dropsort_dragging_item).removeClass(dropsort_draggables[dropsort_dragging_item_container]['objclass']+'_dragging');	 
	
	dropsort_dragging_item='';
	dropsort_dragging_item_index='';
	dropsort_dragging=false; 
	dropsort_dragging_item_container='';
}

function DropSortDragTarget(cursorX,cursorY)
{
	//find where we dropped on
    theindex=dropsort_dragging_item_index;
    dropsort_drag_goback=true;
    
	//find where center of dragged item is compared to potential  targets (drag siblings)
	cursorX+=dropsort_dragging_item.offsetWidth/2;
	cursorY+=dropsort_dragging_item.offsetHeight/2;
	var list=$("#"+dropsort_dragging_item_container+" DIV."+dropsort_draggables[dropsort_dragging_item_container]['objclass']);
	for(var i=0;i<list.length;i++)
	{
		var itop=list.get(i).offsetTop;
		var ileft=list.get(i).offsetLeft;
		var ibottom=list.get(i).offsetHeight+itop;
		var iright=list.get(i).offsetWidth+ileft;
			
		//if we're in bounds and didn't find ourselves, this is the target		
		if(i!=dropsort_dragging_item_index && cursorX<iright && cursorX>ileft && cursorY>itop && cursorY<ibottom)
		{
			theindex=i;
			dropsort_drag_goback=false;
		}

	}
	return theindex;
}

function DropSortPlaceholder(element,apply)
{
  	//must be gradding
	if(!dropsort_dragging_item_container)
		return;

	//the placeholder item
	var placeholder=$("#"+dropsort_dragging_item_container+" DIV.placeholder");	
	if(apply)	
	{
	  	//show place holder.  size and place according to who's being dragged
		$(element).before(placeholder);
		placeholder.css("display", "block");
		placeholder.css("height",element.offsetHeight+"px");
	}
	else
	{
	  	//remove placeholder, put at end of list.
		placeholder.css("display", "none");
		$("#"+dropsort_dragging_item_container).append(placeholder);		
	}
		
}

function DropSortSortTo(element,theindex,dontsave)
{
	var list=$("#"+dropsort_dragging_item_container+" DIV."+dropsort_draggables[dropsort_dragging_item_container]['objclass']);

	//if called via dragging, we know original position.  otherwise, calculated it
	//if we're down sorting more than one space, decrement; thus we grab our target's place
	//done for consistency with up sorting or down sort by one.  always get the position you drop on
	var cur=dropsort_dragging_item_index;
	if(!dropsort_dragging_item)
	{
		for(var i=0;i<list.length;i++)
		{
			if(list.get(i)==element)
				cur=i;
		}
	}
	else if((cur+1)<theindex && !dropsort_drag_goback)
		theindex--;  

	//if less than 0, go to the end.  if more than length, go to start
	//wrap sorting.
	if(theindex<0)
		theindex=list.length-1;
	else if(theindex>(list.length-1))
		theindex=0;
	
	//before or after item? (depends on  where we come from)
	if(cur>theindex)
		$(list.get(theindex)).before((element));
	else 
		$(list.get(theindex)).after((element));

	//don't save flag if we don't need to save via ajax
	if(!dontsave)
	{
		//update our sort order field and everyone else's based on our new position	
		var sortf=$("#"+element.id+" INPUT."+dropsort_draggables[dropsort_dragging_item_container]['sortclass']).get(0);
		if(sortf)
		{
		  	//value +/- 0.01 to ensure we grab the spot we want.  PHP logic will resort and round to integers 
		  	sortf.value=parseInt(theindex)+1+(cur<theindex?0.01:-0.01);
			AjaxSave(dropsort_draggables[dropsort_dragging_item_container]['ajaxurl'],jQuery("#"+element.id+" FORM."+dropsort_draggables[dropsort_dragging_item_container]['sortclass']).get(0));	    
			
			//javascript logicv to round everyone to integers
			var items=$("#"+dropsort_dragging_item_container+" INPUT."+dropsort_draggables[dropsort_dragging_item_container]['sortclass']);
			for(var i=0;i<items.length;i++)
			    items.get(i).value=(i+1);  	    	    	    	    
		}
	}
}

function DropSortSetReference(container)
{
  	//to be called if sorting occurs via direct calls.
  	//set the reference container for save, etc calls  	
  	dropsort_dragging_item_container=container;
}





function AjaxDelete(element)
{
  	//fade out when delete function called
	$(element).fadeOut(1000,function(){element.style.display='none'});
}

function AjaxSave(url,form,callback)
{
  	//get all params from the form 
  	//(ould use AjaxRequest.Serialize?)
  	var params=new Array();
  	for(var i=0;i<form.length;i++)
	  	params[form[i].name]=form[i].value;
	
	//dummy callback...not currently used, but probably will be later.
	if(!callback)  	
		callback=function(request){};
	  	
	//send the ajax request to desired location.  	
	AjaxRequest.post(
	{
		'url':url,
		'parameters':params
	}
	);  
}