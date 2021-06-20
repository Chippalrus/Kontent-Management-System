<?php
//=========================================================================================
//	CCache by Chippalrus
//=========================================================================================
class CCache
{
//=========================================================================================
//	Members
//=========================================================================================
	private		$m_CacheDir;		// Directory for Caching
//=========================================================================================
//	Constructor / Destructor
//=========================================================================================
	protected	function	__construct		( $sDir )
	{
		// Check for existing directory
		if( !is_dir( __DIR__ . DIRECTORY_SEPARATOR . $sDir ) )
		{
			// Create directory if it doesn't exist
			mkdir( __DIR__ . DIRECTORY_SEPARATOR . $sDir, 0755 );
		}
		// Set directory
		$this->m_CacheDir = __DIR__ . '/' . $sDir . '/';
	}

	public	function	__destruct(){}
//=========================================================================================
//	Get
//=========================================================================================
	public	function	GetDirectory	()	{	return $this->m_CacheDir;	}
//=========================================================================================
//	Set
//=========================================================================================
//=========================================================================================
//	Methods
//=========================================================================================
	// Write cache file
	protected	function	WriteCache		( $sFileName, $sContent, $eURI )
	{
		// Check for existing directory to write to
		if( !is_dir( $this->m_CacheDir . $eURI ) )
		{
			// Create folder if it doesn't exist
			mkdir( $this->m_CacheDir . $eURI, 0755 );
		}

		// checks for valid input
		if( isset( $sFileName ) )
		{
			// Write to directory
			$writeJson = fopen( $this->m_CacheDir . $eURI . '/' . $sFileName, 'w' );
			fwrite( $writeJson, $sContent );
			fclose( $writeJson );
		}
		else
		{
			echo '0: Cache could not be written. <br/>';
			echo 'PARAM_1 : $sFileName: ' . $sFileName . '<br/>';
		}
	}

	// Gets the cache file
	protected	function	GetCache( $sFileName, $eURI )
	{
		$sTemp;
		// Checks if file exists
		if( file_exists( $this->m_CacheDir . $eURI . '/' . $sFileName ) )
		{
			// Get the file contents
			$sTemp = file_get_contents( $this->m_CacheDir . $eURI . '/' . $sFileName );
		}
		else	{	$sTemp = 'error';	}
		return $sTemp;
	}
	
	// Checks if file is old -- boolean ( Should make this customizeable )
	protected	function	IsExpired( $sFileName, $eURI, $sExpireTime )
	{
		$bExpired = true;
		$sFile = $this->m_CacheDir . $eURI . '/' . $sFileName;
		// Check if the file exists
		if( file_exists( $sFile ) )
		{
			// Checks to see if the file is within the time '-6 hours'
			if( filemtime( $sFile ) > strtotime( $sExpireTime ) )
			{
				$bExpired = false;
			} else { $bExpired = true; }
		}
		return $bExpired;
	}
}
?>