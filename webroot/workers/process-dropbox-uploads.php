<?php
require_once "../lib/bootstrap.php";

$accessToken = "hhEvuMaZfGEAAAAAAAAAAbdMwClYVaAQ9NVk57Xn134SdsWsawf3eHHyvGkUC405";
$dbxClient = new Dropbox\Client($accessToken, "PHP-Example/1.0");

$folderMetadata = $dbxClient->getMetadataWithChildren("/idc-consultants-group/uploads/pending");
$tablets = $folderMetadata['contents'];

foreach($tablets as $tablet)
{
	$folderMetadata = $dbxClient->getMetadataWithChildren($tablet['path']);
	$outputs = $folderMetadata['contents'];

	foreach($outputs as $output)
	{
		$folderMetadata = $dbxClient->getMetadataWithChildren($output['path']);
		$contents = $folderMetadata['contents'];
		echo "<pre>";
		var_dump($contents);
		echo "</pre>";
	}
}