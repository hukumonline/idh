<?php
class Kutu_View_Helper_GetGroupName
{
	public function getGroupName($id)
    {
    	$modelGroup = new Kutu_Core_Orm_Table_Group();
    	$row = $modelGroup->fetchRow("id=$id");
    	return ($row) ? $row->name : '-';
   	}
}
?>