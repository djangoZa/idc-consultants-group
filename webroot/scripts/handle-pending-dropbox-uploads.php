<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__FILE__) . "/../lib/bootstrap.php";

$tabletDropboxService = Container::get('IDC_Tablet_Dropbox_Service');
$tabletOutput = Container::get('IDC_Tablet_Output', array($tabletDropboxService));

echo "OK: Start\n";

echo "OK: Checking for pending tablet outputs\n";

if ($tabletDropboxService->hasPendingTabletOutputs())
{
	echo "OK: Found pending tablet outputs\n";

	$pendingFolders = $tabletDropboxService->getPendingFolders();

	foreach ($pendingFolders as $folderIndex => $pendingFolder)
	{
		echo "OK: Looking at pending tablet output ($folderIndex)\n";

        if ($pendingFolder->isReadyToBeProcessed())
        {
        	echo "OK: Pending tablet output ($folderIndex) ready for processing\n";

        	$tabletOutput->setFolder($pendingFolder);
        	$siteId = $tabletOutput->getSiteId();

        	if (!empty($siteId))
        	{
        		echo "OK: Got site id ($siteId)\n";

            	$tabletDropboxService->movePendingFolderToSiteFolder($pendingFolder, $siteId);

            	echo "OK: Tablet output for site id ($siteId) moved to its designated site folder\n";	

        	} else {

        		echo "NOTICE: Cant find site id for tablet output ($folderIndex)\n";
        		
        		$tabletDropboxService->movePendingFolderToCorruptedFolder($pendingFolder);

        		echo "NOTICE: Moved tablet output to currupted folder\n";

        	}
        } else {

        	echo "NOTICE: Pending tablet output ($folderIndex) not ready for processing\n";

        }
	}
} else {

    echo "OK: No pending tablet outputs found\n";
    
}
	
echo "OK: End\n\n";