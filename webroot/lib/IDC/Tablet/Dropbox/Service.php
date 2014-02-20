<?php
class IDC_Tablet_Dropbox_Service
{
	private $_client;
	private $_tmpFolder = '/tmp/idc-consultants-group';
    private $_localDropboxPath = "/vagrant/dropbox";
    private $_dropboxPendingTabletUploadsPath =  "/idc-consultants-group/uploads/Pending";
    private $_dropboxProcessedTabletUploadsPath = "/idc-consultants-group/uploads/Processed";
    private $_dropboxLocalProcessedTabletUploadsPath = "/vagrant/dropbox/idc-consultants-group/uploads/Processed";
    private $_dropboxCorruptedTabletOutputPath = "/idc-consultants-group/uploads/Corrupted";
    private $_processedFolderPath;
    private $_siteFolderPath;

    public function __construct(Dropbox\Client $client)
    {
    	$this->_client = $client;
    }

    public function cleanTmpDirectory()
    {
        $folder = $this->_tmpFolder;
        foreach(glob("{$folder}/*") as $file)
        {
            unlink($file);
        }
    }

    public function uploadFloorplanImage($localPath, $siteId)
    {
        $path = $this->_localDropboxPath . $this->_dropboxProcessedTabletUploadsPath . '/' . $siteId . "/Floorplans/Overlayed/" . basename($localPath);
        rename($localPath, $path);
    }

    public function getFloorplanImagePathsByFloorplan(IDC_Tablet_Floorplan $floorplan)
    {
        $out = array();
        $versions = array('');

        foreach ($versions as $version)
        {
            $siteId = $floorplan->getSiteId();

            $floorplanImageName = $floorplan->getImageName();
            $floorplanImageNameParts = explode(".", $floorplanImageName);
            
            $path = $this->_dropboxLocalProcessedTabletUploadsPath . '/' . $siteId . '/Floorplans/Original/' . $floorplanImageNameParts[0] . $version . '.' . $floorplanImageNameParts[1];

            $out[$version] = $path;
        }

        return $out;
    }

    public function hasAllRequiredFloorplanImages($siteId, Array $floorplans)
    {
        $out = array('result' => true);
        $floorplanImageNames = $this->_getFloorplanImageNames($floorplans);
        $paths = array();

        //construct all possible floorplan image paths
        foreach($floorplanImageNames as $floorplanImageName)
        {
            $paths[] = $this->_localDropboxPath . $this->_dropboxProcessedTabletUploadsPath . '/' . $siteId . '/Floorplans/Original/' . $floorplanImageName;
        }

        //verify all floorplan images exist
        foreach ($paths as $path)
        {
            $result = is_file($path);
            if (empty($result))
            {
                $out['result'] = false;
                $out['messages'][] = "Floorplan at path ($path) is missing.";
            }
        }

        return $out;
    }

    public function createFloorplanFolderSkeleton($siteId)
    {
        $basePath = $this->_localDropboxPath . $this->_dropboxProcessedTabletUploadsPath . '/' . $siteId;
        $floorplansPath  = $basePath . '/Floorplans';
        $originalPath = $floorplansPath . '/Original';
        $overlayedPath = $floorplansPath . '/Overlayed';

        if (!is_dir($floorplansPath))
        {
            mkdir($floorplansPath, 1777, $recursive = true);
        }

        if (!is_dir($originalPath))
        {
            mkdir($originalPath, 1777, $recursive = true);
        }

        if (!is_dir($overlayedPath))
        {
            mkdir($overlayedPath, 1777, $recursive = true);
        }
    }

    public function getOutputsBySiteId($siteId)
    {
        $out = array();
        $path = $this->_localDropboxPath . $this->_dropboxProcessedTabletUploadsPath . "/$siteId/Data";
        $outputs = $this->_getFolderContents($path);

        if (!empty($outputs))
        {
            foreach($outputs as $folder)
            {
                $folder = new IDC_Tablet_Dropbox_Folder($folder);
                $output = new IDC_Tablet_Output($this);
                $output->setFolder($folder);
                $out[] = $output;
            }
        }

        return $out;
    }

    public function hasPendingTabletOutputs()
    {
        $out = false;
        $tablets = $this->_getFolderContents($this->_localDropboxPath . $this->_dropboxPendingTabletUploadsPath);

        foreach($tablets as $tablet)
        {
            $outputs = $this->_getFolderContents($tablet['path']);

            //if we have outputs, break and return true
            if (count($outputs))
            {
                $out = true;
                break;
            }
        }

        return $out;
    }

    public function getPendingFolders()
    {
        $out = array();
        $tablets = $this->_getFolderContents($this->_localDropboxPath . $this->_dropboxPendingTabletUploadsPath);

        foreach($tablets as $tablet)
        {
            $outputs = $this->_getFolderContents($tablet['path']);

            foreach($outputs as $folder)
            {
                $out[] = new IDC_Tablet_Dropbox_Folder($folder);
            }
        }

        return $out;
    }

    public function getFileContents($path)
    {
        $out = file_get_contents($path);
        return $out;
    }

    public function getFloorplanImageDimensions($siteId, $floorplanImageName)
    {
        $path = $this->_dropboxLocalProcessedTabletUploadsPath . '/' . $siteId . '/Floorplans/Original/' . $floorplanImageName;
        $result = getimagesize($path);

        return array(
            'width' => $result[0],
            'height' => $result[1]
        );
    }

    public function movePendingFolderToSiteFolder(IDC_Tablet_Dropbox_Folder $folder, $siteId)
    {
        $this->_createSiteFolder($siteId);
        $this->_createDataDumpFolder($siteId);
        $this->_movePendingFolderToDataDumpFolder($folder);
    }

    public function movePendingFolderToCorruptedFolder(IDC_Tablet_Dropbox_Folder $folder)
    {
        $folderName = basename($folder->getPath() . "_" . uniqid());
        rename($folder->getPath(), $this->_localDropboxPath . $this->_dropboxCorruptedTabletOutputPath . '/' . $folderName);
    }

    private function _sortContentsByPath($array, $key)
    {
        $sorter = array();
        $ret = array();
        reset($array);

        foreach ($array as $ii => $va)
        {
            $sorter[$ii] = $va[$key];
        }

        arsort($sorter);
        foreach ($sorter as $ii => $va)
        {
            $ret[$ii] = $array[$ii];
        }

        $array = $ret;

        return $array;
    }

    private function _movePendingFolderToDataDumpFolder(IDC_Tablet_Dropbox_Folder $folder)
    {
        $this->_setProcessedFolderPath($folder);
        rename($folder->getPath(), $this->_processedFolderPath);
    }

    private function _setProcessedFolderPath(IDC_Tablet_Dropbox_Folder $folder)
    {
        $folderName = basename($folder->getPath() . "_" . uniqid());
        $this->_processedFolderPath = $this->_siteFolderPath . '/Data/' . $folderName;
    }

    private function _createDataDumpFolder($siteId)
    {
        $path = $this->_siteFolderPath . '/Data';

        //create data directory
        if (!is_dir($path))
        {
            mkdir($path, 1777, $recursive = true);
        }
    }

    private function _createSiteFolder($siteId)
    {
        //define the path for the site folder
        $this->_siteFolderPath = $this->_localDropboxPath . $this->_dropboxProcessedTabletUploadsPath . '/' . $siteId;

        //create the site folder if it doesnt exist
        if (!is_dir($this->_siteFolderPath))
        {
            mkdir($this->_siteFolderPath, 1777, $recursive = true);
        }
    }

    private function _getFolderContents($folderPath)
    {
        $contents = array();
        $folders = scandir($folderPath);
        foreach ($folders as $folder)
        {
            if ($folder === '.' or $folder === '..') continue;

            $newPath = $folderPath . '/' . $folder;
            if (is_dir($newPath))
            {
                $contents[] = array('path' => $newPath);
            }
        }

        $out = $this->_sortContentsByPath($contents, 'path');
        return $out;
    }

    private function _getFloorplanImageNames(Array $floorplans)
    {
        $out = array();

        foreach($floorplans as $floorplan)
        {
            $out[] = $floorplan->getImageName();
        }

        return array_unique($out);
    }
}