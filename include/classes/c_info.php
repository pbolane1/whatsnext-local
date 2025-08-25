<?php
class info extends DBRowEx
{
	public function __construct()
	{

	}

	public function Display($params=array())
	{
		//$whatever= newwhatever();
		//$whatever->InitByKeys('',$params['key']);
		
		var_dump($params['key']);
	}

};
?>