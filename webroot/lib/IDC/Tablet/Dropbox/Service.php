<?php
class IDC_Tablet_Dropbox_Service
{
	private $_client;
	private $_tmpFolder = '/tmp/idc-consultants-group';
    private $_dropboxPendingTabletUploadsPath =  "/idc-consultants-group/uploads/Pending";
    private $_dropboxProcessedTabletUploadsPath = "/idc-consultants-group/uploads/Processed";
    private $_dropboxCorruptedTabletOutputPath = "/idc-consultants-group/uploads/Corrupted";
    private $_processedFolderPath;
    private $_siteFolderPath;

    public function __construct(Dropbox\Client $client)
    {
    	$this->_client = $client;
    }

    public function getOutputsBySiteId($siteId)
    {
        $out = array();
        $path = $this->_dropboxProcessedTabletUploadsPath . "/$siteId/Data";
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
        $tablets = $this->_getFolderContents($this->_dropboxPendingTabletUploadsPath);

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
        $tablets = $this->_getFolderContents($this->_dropboxPendingTabletUploadsPath);

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
        $out = '';

        $tmpDataFilePath = $this->_tmpFolder . "/data-" . uniqid() . ".txt";
        $fileHandle = fopen($tmpDataFilePath, "w+b");
        $fileMetadata = $this->_client->getFile($path, $fileHandle);
        fclose($fileHandle);

        $out = file_get_contents($tmpDataFilePath);

        return $out;
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
        $this->_client->move($folder->getPath(), $this->_dropboxCorruptedTabletOutputPath . '/' . $folderName);
    }

    private function _movePendingFolderToDataDumpFolder(IDC_Tablet_Dropbox_Folder $folder)
    {
        $this->_setProcessedFolderPath($folder);
        $this->_client->move($folder->getPath(), $this->_processedFolderPath);
    }

    private function _setProcessedFolderPath(IDC_Tablet_Dropbox_Folder $folder)
    {
        $folderName = basename($folder->getPath() . "_" . uniqid());
        $this->_processedFolderPath = $this->_siteFolderPath . '/Data/' . $folderName;
    }

    private function _createDataDumpFolder($siteId)
    {
        //create data directory
        $this->_client->createFolder($this->_siteFolderPath . '/Data');
    }

    private function _createSiteFolder($siteId)
    {
        //define the path for the site folder
        $this->_siteFolderPath = $this->_dropboxProcessedTabletUploadsPath . '/' . $siteId;

        //create the site folder if it doesnt exist
        $this->_client->createFolder($this->_siteFolderPath);
    }

    private function _getFolderContents($folderPath)
    {
        $folderMetadata = $this->_client->getMetadataWithChildren($folderPath);
        $out = $folderMetadata['contents'];
        return $out;
    }
}