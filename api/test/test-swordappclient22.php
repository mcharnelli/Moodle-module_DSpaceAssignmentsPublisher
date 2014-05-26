<?php
    // Test the V2 PHP client implementation using the Simple SWORD Server (SSS)


	
	// The user (if required)
	$testuser = "mcharnelli@mail.linti.unlp.edu.ar";
	
	// The password of the user (if required)
	$testpw = "burbuja";
	

	// The URL of the example deposit collection
	$testdepositurl = "http://repositorio.info.unlp.edu.ar/sword/deposit/123456789/244";

	// The test atom entry to deposit
	$testatomentry = "test-files/atom_multipart/atom";


	// The test content zip file to deposit
	$testzipcontentfile = "test-files/sword-article.zip";



	// The content type of the test file
	$testcontenttype = "application/zip";

        $packageformat="http://purl.org/net/sword-types/METSDSpaceSIP";
	
	require("../swordappclient.php");
    $testsac = new SWORDAPPClient();
    
	$testdr = $testsac->deposit($testdepositurl, $testuser, $testpw, '', $testzipcontentfile, $packageformat,$testcontenttype, false);

		print "Received HTTP status code: " . $testdr->sac_status . " (" . $testdr->sac_statusmessage . ")\n";
		
		if (($testdr->sac_status >= 200) || ($testdr->sac_status < 300)) {
            $testdr->toString();
        }

        print "\n\n";


 

?>
