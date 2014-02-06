<?php
class Container
{
	public static function get($name, Array $options = array())
	{
		$out = null;

		switch($name)
		{
			case 'IDC_Tablet_Dropbox_Service':
				$out = self::_getIDC_Tablet_Dropbox_Service();
				break;
			case 'IDC_Tablet_Output':
				$out = self::_getIDC_Tablet_Output($options);
				break;
			case 'IDC_Tablet_FloorPlan':
				$out = self::_getIDC_Tablet_FloorPlan($options);
			    break;
			default:
				break;
		}

		return $out;
	}

	private static function _getIDC_Tablet_Floorplan(Array $options)
	{
		$out = new IDC_Tablet_FloorPlan($options[0]);
		return $out;
	}

	private static function _getIDC_Tablet_Dropbox_Service()
	{
		$dropboxAccessToken = "hhEvuMaZfGEAAAAAAAAAAbdMwClYVaAQ9NVk57Xn134SdsWsawf3eHHyvGkUC405";
		$dropboxClient = new Dropbox\Client($dropboxAccessToken, "PHP-Example/1.0");
		$out = new IDC_Tablet_Dropbox_Service($dropboxClient);
		return $out;
	}

	private static function _getIDC_Tablet_Output(Array $options)
	{
		$out = new IDC_Tablet_Output($options[0]);
		return $out;
	}
}