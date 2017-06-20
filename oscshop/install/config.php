<?php
define('INSTALL_APP_PATH', realpath('./') . '/');
return [
	
    'view_replace_str'=>[    
		'__PUBLIC__'=>'/public/static',
	    '__RES__'=>'/public/static/view_res',
	    '__ADMIN__' =>'/public/static/view_res/admin',
	    'IMG_ROOT'=>'/public',	
	     '__NAME__'=>'Waynes电子商务系统',
	    '__COMPANY__'=>' 深圳韦恩斯科技有限公司 ',
	    '__WEBSITE__'=>'www.waynes-tech.com',
	    '__COMPANY_WEBSITE__'=>'www.waynes-tech.com'
	],		
	
	'template'      => [
	    // 模板引擎
	    'type'   => 'think',
	    //标签库标签开始标签 
	    'taglib_begin'  =>  '<',
	    //标签库标签结束标记
	    'taglib_end'    =>  '>',     
	],

];
