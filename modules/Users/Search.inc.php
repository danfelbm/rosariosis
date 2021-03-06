<?php

if(!$_REQUEST['search_modfunc'])
{
	switch(User('PROFILE'))
	{
		case 'admin':
		case 'teacher':
			//if(UserStaffID() && ($_REQUEST['modname']!='Users/Search.php' || $_REQUEST['student_id']=='new'))
			if(UserStaffID() && User('PROFILE')=='admin' && $_REQUEST['staff_id']=='new')
				unset($_SESSION['staff_id']);

			$_SESSION['Search_PHP_SELF'] = PreparePHP_SELF($_SESSION['_REQUEST_vars'],array('bottom_back','advanced'));
			if($_SESSION['Back_PHP_SELF']!='staff')
			{
				$_SESSION['Back_PHP_SELF'] = 'staff';
				unset($_SESSION['List_PHP_SELF']);
			}

			echo '<BR />';

			PopTable('header',$extra['search_title']?$extra['search_title']:_('Find a User'));

			echo '<FORM name="search" id="search" action="Modules.php?modname='.$_REQUEST['modname'].'&modfunc='.$_REQUEST['modfunc'].'&search_modfunc=list&next_modname='.$_REQUEST['next_modname'].'&advanced='.$_REQUEST['advanced'].$extra['action'].'" method="GET">';

			echo '<TABLE><TR class="valign-top"><TD>';

//FJ css WPadmin
			echo '<TABLE class="width-100p col1-align-right" id="general_table">';
			echo '<TR><TD><label for="last">'._('Last Name').'</label></TD><TD><INPUT type="text" name="last" id="last" size="30"></TD></TR>';
			echo '<TR><TD><label for="first">'._('First Name').'</label></TD><TD><INPUT type="text" name="first" id="first" size="30"></TD></TR>';
			echo '<TR><TD><label for="usrid">'._('User ID').'</label></TD><TD><input type="text" name="usrid" id="usrid" size="30"></TD></TR>';
			echo '<TR><TD><label for="username">'._('Username').'</label></TD><TD><INPUT type="text" name="username" id="username" size="30"></TD></TR>';

			if(User('PROFILE')=='admin')
				$options = array(''=>_('N/A'),'admin'=>_('Administrator'),'teacher'=>_('Teacher'),'parent'=>_('Parent'),'none'=>_('No Access'));
			else
				$options = array(''=>_('N/A'),'teacher'=>_('Teacher'),'parent'=>_('Parent'));

			if($extra['profile'])
				$options = array($extra['profile']=>$options[$extra['profile']]);

			echo '<TR><TD><label for="profile">'._('Profile').'</label></TD><TD><SELECT name="profile" id="profile">';

			foreach($options as $key=>$val)
				echo '<OPTION value="'.$key.'">'.$val.'</OPTION>';

			echo '</SELECT></TD></TR>';

			if(!isset($extra))
				$extra = array();

			StaffWidgets('user',$extra);

			Search('staff_fields',is_array($extra['staff_fields'])?$extra['staff_fields']:array());


			echo '</TABLE></TD><TR><TD class="center">';

			if($extra['search_second_col'])
				echo $extra['search_second_col'];

			if(User('PROFILE')=='admin')
			{
//FJ add <label> on checkbox
//FJ if only one school, no Search All Schools option
				if (SchoolInfo('SCHOOLS_NB') > 1)
					echo '<label><INPUT type="checkbox" name="_search_all_schools" value="Y"'.(Preferences('DEFAULT_ALL_SCHOOLS')=='Y'?' checked':'').'>&nbsp;'._('Search All Schools').'</label><BR />';
			}

			echo '<label><INPUT type="checkbox" name="include_inactive" value="Y"> '._('Include Parents of Inactive Students').'</label><BR /><BR />';

			echo Buttons(_('Submit'),_('Reset'));

			echo '</TD></TR>';

			if ($extra['search'] || $extra['extra_search'] || $extra['second_col'])
			{
				echo '<TR><TD><TABLE class="widefat width-100p cellspacing-0 col1-align-right">';

				if($extra['search'])
					echo $extra['search'];
				if($extra['extra_search'])
					echo $extra['extra_search'];
				if($extra['second_col'])
					echo $extra['second_col'];

				echo '</TABLE></TD></TR>';
			}
				
			echo '<TR class="valign-top"><TD>';

			if($_REQUEST['advanced']=='Y')
			{
				$extra['search'] = '';
				StaffWidgets('all',$extra);

				if ($extra['search'])
				{
					echo '<TABLE class="postbox cellspacing-0"><THEAD><TR><TH>';
					echo '<H3>'._('Widgets').'</H3></TH></TR></THEAD><TBODY>';
					echo $extra['search'];
					echo '</TBODY></TABLE><br />';
				}

				echo '<TABLE class="postbox cellspacing-0"><THEAD><TR><TH>';
				echo '<H3>'._('User Fields').'</H3></TH></TR></THEAD><TBODY><TR><TD>';
				Search('staff_fields_all',is_array($extra['staff_fields'])?$extra['staff_fields']:array());
				echo '</TD></TR>';
				echo '</TBODY></TABLE>';

				echo '<BR /><A href="'.PreparePHP_SELF($_REQUEST,array(),array('advanced'=>'N')).'">'._('Basic Search').'</A>';
			}
			else
				echo '<BR /><A href="'.PreparePHP_SELF($_REQUEST,array(),array('advanced'=>'Y')).'">'._('Advanced Search').'</A>';

			echo '</TD></TR></TABLE></FORM>';

			// set focus to last name text box
			// update Bottom.php
			echo '<script> document.search.last.focus(); var footer_link = document.createElement("a"); footer_link.href = "Bottom.php"; footer_link.target = "footer"; ajaxLink(footer_link); old_modname="";</script>';

			PopTable('footer');
		break;

		default:
			echo User('PROFILE');
	}
}
//if($_REQUEST['search_modfunc']=='list')
else
{
	if(!$_REQUEST['next_modname'])
		$_REQUEST['next_modname'] = 'Users/User.php';

	if(User('PROFILE')=='admin')
	{
		if(!isset($extra))
			$extra = array();

		StaffWidgets('user',$extra);

		if($_REQUEST['advanced']=='Y')
			StaffWidgets('all',$extra);
	}

	if(!$extra['NoSearchTerms'])
	{
		if($_REQUEST['_search_all_schools']=='Y')
			$_ROSARIO['SearchTerms'] .= '<b>'._('Search All Schools').'</b><BR />';
	}

	$extra['WHERE'] .= appendStaffSQL('',array('NoSearchTerms'=>$extra['NoSearchTerms']));
	$extra['WHERE'] .= CustomFields('where','staff',array('NoSearchTerms'=>$extra['NoSearchTerms']));

	if(!isset($_ROSARIO['DrawHeader']))
		DrawHeader(_('Choose A User'));

	$staff_RET = GetStaffList($extra);

	if($extra['profile'])
	{
		// DO NOT translate those strings since they will be passed to ListOutput ultimately
		$options = array('admin'=>'Administrator','teacher'=>'Teacher','parent'=>'Parent','none'=>'No Access');
		$singular = $options[$extra['profile']];
		$plural = $singular.($options[$extra['profile']]=='none'?'':'s');
		$columns = array('FULL_NAME'=>$singular,'STAFF_ID'=>sprintf(_('%s ID'),Config('NAME')));
	}
	else
	{
		$columns = array('FULL_NAME'=>_('User'),'PROFILE'=>_('Profile'),'STAFF_ID'=>sprintf(_('%s ID'),Config('NAME')));
	}

	$name_link['FULL_NAME']['link'] = 'Modules.php?modname='.$_REQUEST['next_modname'];
	$name_link['FULL_NAME']['variables'] = array('staff_id'=>'STAFF_ID');

	if(isset($extra['link']) && is_array($extra['link']))
		$link = $extra['link'] + $name_link;
	else
		$link = $name_link;

	if(isset($extra['columns_before']) && is_array($extra['columns_before']))
		$columns = $extra['columns_before'] + $columns;

	if(isset($extra['columns_after']) && is_array($extra['columns_after']))
		$columns += $extra['columns_after'];

	if(count($staff_RET)>1 || $link['add'] || !$link['FULL_NAME'] || $extra['columns_before'] || $extra['columns_after'] || ($extra['BackPrompt']==false && count($staff_RET)==0) || ($extra['Redirect']===false && count($staff_RET)==1))
	{
		if($_REQUEST['expanded_view']!='true')
			DrawHeader('<A HREF="'.PreparePHP_SELF($_REQUEST,array(),array('expanded_view'=>'true')) . '">'._('Expanded View').'</A>',$extra['header_right']);
		else
			DrawHeader('<A HREF="'.PreparePHP_SELF($_REQUEST,array(),array('expanded_view'=>'false')) . '">'._('Original View').'</A>',$extra['header_right']);

		DrawHeader($extra['extra_header_left'],$extra['extra_header_right']);
		DrawHeader(str_replace('<BR />','<BR /> &nbsp;',mb_substr($_ROSARIO['SearchTerms'],0,-6)));

		if(!$_REQUEST['LO_save'] && !$extra['suppress_save'])
		{
			$_SESSION['List_PHP_SELF'] = PreparePHP_SELF($_SESSION['_REQUEST_vars'],array('bottom_back'));

			if($_SESSION['Back_PHP_SELF']!='staff')
			{
				$_SESSION['Back_PHP_SELF'] = 'staff';
				unset($_SESSION['Search_PHP_SELF']);
			}

			echo '<script>var footer_link = document.createElement("a"); footer_link.href = "Bottom.php"; footer_link.target = "footer"; ajaxLink(footer_link); old_modname="";</script>';
		}

		if($extra['profile'])
			ListOutput($staff_RET,$columns,$singular,$plural,$link,false,$extra['options']);
		else
			ListOutput($staff_RET,$columns,'User','Users',$link,false,$extra['options']);
	}
	elseif(count($staff_RET)==1)
	{
		if(count($link['FULL_NAME']['variables']))
		{
			foreach($link['FULL_NAME']['variables'] as $var=>$val)
				$_REQUEST[$var] = $staff_RET['1'][$val];
		}

		if(!is_array($staff_RET[1]['STAFF_ID']))
		{
			SetUserStaffID($staff_RET[1]['STAFF_ID']);

			unset($_REQUEST['search_modfunc']);
		}

		if($_REQUEST['modname']!=$_REQUEST['next_modname'])
		{
			$modname = $_REQUEST['next_modname'];

			if(mb_strpos($modname,'?'))
				$modname = mb_substr($_REQUEST['next_modname'],0,mb_strpos($_REQUEST['next_modname'],'?'));

			if(mb_strpos($modname,'&'))
				$modname = mb_substr($_REQUEST['next_modname'],0,mb_strpos($_REQUEST['next_modname'],'&'));

			if($_REQUEST['modname'])
				$_REQUEST['modname'] = $modname;

			//FJ security fix, cf http://www.securiteam.com/securitynews/6S02U1P6BI.html
			if (mb_substr($modname, -4, 4)!='.php' || mb_strpos($modname, '..')!==false || !is_file('modules/'.$modname))
			{
				include('ProgramFunctions/HackingLog.fnc.php');
				HackingLog();
			}
			else
				include('modules/'.$modname);
		}
	}
	else
	{		
		DrawHeader('',$extra['header_right']);
		DrawHeader($extra['extra_header_left'],$extra['extra_header_right']);
		DrawHeader(str_replace('<BR />','<BR /> &nbsp;',mb_substr($_ROSARIO['SearchTerms'],0,-6)));

		echo ErrorMessage(array(_('No Users were found.')));
	}
}
?>
