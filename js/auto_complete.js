var ac_cur_searchterm=new Array();
var ac_sel_searchterm=new Array();
var ac_searchtimer=new Array();
var ac_last_requestindex=new Array();
var ac_loading_class=new Array();
var ac_search_field=new Array();
var ac_min_length=new Array();
var ac_close_delay=1000;
function EnableAutoComplete(container_id,field_id,showloading,min_length) 
{

	ac_cur_searchterm[container_id]=-1;
	ac_sel_searchterm[container_id]=true;
	ac_searchtimer[container_id]=0;
	ac_last_requestindex[container_id]=-1;
	ac_loading_class[container_id]=showloading;
	ac_search_field[container_id]=field_id;
	ac_min_length[container_id]=min_length;

	//search related, keyboard navigation
	jQuery("#"+field_id).on('click',function(event){
		var str=document.getElementById(field_id).value;
		if(str.length>=ac_min_length[container_id])	  
			SubmitSearch(container_id,document.getElementById(field_id).value,field_id,showloading);
		ac_sel_searchterm[container_id]=true;	
	});

	jQuery("#"+field_id).on('focus',function(event){
		ShowSearchResults(container_id,true);	  
		ac_sel_searchterm[container_id]=true;	
	});

	jQuery("#"+field_id).on('blur',function(event){
		ShowSearchResults(container_id,false);
	});

	jQuery("#"+field_id).on('mouseover',function(event){
		ShowSearchResults(container_id,true);	  
		ac_sel_searchterm[container_id]=true;	
	});

	jQuery("#"+field_id).on('keydown',function(event){
		if(event.keyCode==13)
		{
			return GoToSearchTerm(container_id,ac_cur_searchterm[container_id]);		  				
		}
	});

	jQuery("#"+field_id).on('keyup',function(event){
		if(event.keyCode==38)
			SelectSearchTerm(container_id,ac_cur_searchterm[container_id]-1,!ac_sel_searchterm[container_id]);		  				
		else if(event.keyCode==40)
			SelectSearchTerm(container_id,ac_cur_searchterm[container_id]+1,!ac_sel_searchterm[container_id]);		  				
		else if(event.keyCode==13)
		{
		  	return false;
//			return GoToSearchTerm(container_id,ac_cur_searchterm[container_id]);		  				
		}
		else if(event.keyCode==27)
			ClearSearchResults(container_id);
		else
		{
			var str=document.getElementById(field_id).value;
			if(str.length>=ac_min_length[container_id])
				SubmitSearch(container_id,document.getElementById(field_id).value,field_id);
			SelectSearchTerm(container_id,-1,true);	
		}
		
		return false;				  				
	});
	
	jQuery("#"+container_id).on("mouseleave",function(event){
		SelectSearchTerm(container_id,-1,true);
		ShowSearchResults(container_id,false);
		ac_sel_searchterm[container_id]=true;	
	});

	jQuery("#"+container_id).on("mouseenter",function(event){
		SelectSearchTerm(container_id,-1,true);	
		ShowSearchResults(container_id,true);
		ac_sel_searchterm[container_id]=false;
	});

//  TO DO: ALLOW SELECTION WITH ARROWS ON CONTAINER.
//	jQuery("#"+container_id).on('keyup',function(event){
//		if(event.keyCode==38)
//			SelectSearchTerm(container_id,ac_cur_searchterm[container_id]-1,!ac_sel_searchterm[container_id]);		  				
//		else if(event.keyCode==40)
//			SelectSearchTerm(container_id,ac_cur_searchterm[container_id]+1,!ac_sel_searchterm[container_id]);		  				
//	});
	
	
	
	jQuery("#"+container_id).css({'width':jQuery("#"+field_id).width()+'px'});
}


function SubmitSearch(container_id,search_for,identifier)
{
  	ac_sel_searchterm[container_id]=true;
  	ac_cur_searchterm[container_id]=-1;
  	ShowSearchResults(container_id,true);
	AjaxRequest.post(
		{
			'url':'/ajax/auto_complete.php',
			'timeout':'20000',
			'parameters':{ 'search_term':search_for,'identifier':identifier},
			'onComplete':function(request){PopulateSearch(container_id,request)}
		}
	);
	SearchShowLoading(container_id,true);
}

function SearchShowLoading(container_id,showit)
{
	if(ac_loading_class[container_id] && showit)	
	  	jQuery("#"+ac_search_field[container_id]).addClass(ac_loading_class[container_id]);
	if(ac_loading_class[container_id] && !showit)	
	  	jQuery("#"+ac_search_field[container_id]).removeClass(ac_loading_class[container_id]);
	  	
}

function PopulateSearch(container_id,request)
{
	if(request.requestIndex>ac_last_requestindex[container_id])
	{
	  	jQuery("#"+container_id).html(request.responseText);
		ac_last_requestindex[container_id]=request.requestIndex;  
		
		
		results=jQuery("#"+container_id+" A");
		if(results.length==1)
			SelectSearchTerm(container_id,0,false);	
	
		SearchShowLoading(container_id,false);	
		
		
//  TO DO: ALLOW SELECTION WITH ARROWS ON CONTAINER - BIND MOUSEOVER ITEMS
//		results.bind("mouseenter",function(event){
//			SelectSearchTerm(container_id,results.index(this),true);	
//		});
		
	}
}

function ShowSearchResults(container_id,vis)
{
	if(vis && ac_searchtimer[container_id])
		clearTimeout(ac_searchtimer[container_id]);
	if(!vis)
		ac_searchtimer[container_id]=setTimeout(function(){ClearSearchResults(container_id)},ac_close_delay);
}

function ClearSearchResults(container_id)
{
	jQuery("#"+container_id).html('');
}



function SelectSearchTerm(container_id,termindex,bail)
{
	results=jQuery("#"+container_id+" A");

	if(ac_cur_searchterm[container_id]>=0)
		results.removeClass('cur');		
	
	ac_cur_searchterm[container_id]=termindex;
	if(bail)
		return;
	if(ac_cur_searchterm[container_id]>=results.length)
		ac_cur_searchterm[container_id]=-1;
	if(ac_cur_searchterm[container_id]<-1)
		ac_cur_searchterm[container_id]=results.length-1;

	if(termindex>=0)
	{
		var offset_top=jQuery(results.get(ac_cur_searchterm[container_id])).get(0).offsetTop;
		var parent=jQuery(results.get(ac_cur_searchterm[container_id])).get(0).parentNode;			
		var height=jQuery(jQuery(results.get(ac_cur_searchterm[container_id])).get(0)).height();
		var parent_height=jQuery(jQuery(results.get(ac_cur_searchterm[container_id])).get(0).parentNode).height();
	
		if(offset_top<parent.scrollTop)
			parent.scrollTop=offset_top;
		if(offset_top+height>parent.scrollTop+parent_height)
			parent.scrollTop=offset_top+height-parent_height;
	}


	jQuery(results.get(ac_cur_searchterm[container_id])).addClass('cur');
}

function GoToSearchTerm(container_id,termindex)
{
	results=jQuery("#"+container_id+" A");
	if(ac_cur_searchterm[container_id]>=0 && ac_cur_searchterm[container_id]<results.length)
	{
		results.get(termindex).onclick();		
		return false;
	}
	return true;
}