<?php
class Kutu_Core_Hol_Catalog
{
	public function getPrice($catalogGuid)
	{
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		$rowset = $tblCatalog->find($catalogGuid);
		if(count($rowset))
		{
			$row = $rowset->current();
			return $row->price;
			
		}
		else
		{
			return 0;
		}
	}
	
}