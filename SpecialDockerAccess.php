<?php
class SpecialDockerAccess extends SpecialPage {
	function __construct() {
		parent::__construct( 'DockerAccess' , 'users' );
// 		parent::__construct( 'DockerAccess' );
	}

	function execute( $par ) {
	
		$request = $this->getRequest();
		$output = $this->getOutput();
		$this->setHeaders();
		
		if (  !$this->userCanExecute( $this->getUser() )  ) {
			$this->displayRestrictionError();
			return;
		}
		
		global $virtualFactoryImages;
		
		if (! ($par === "launch") ) {
			$wikitext = "Please select an image to launch from the follwing list:\n";
			foreach ( $virtualFactoryImages as $image => $readableName ) {
				$wikitext .= "* <span class=\"plainlinks\">[{{fullurl:{{FULLPAGENAME}}}}/launch?image=$image $readableName]<span>\n";
			}
			$output->addWikiText( $wikitext );
			return;
		}
		
		# Check Image Parameter
		$imageID = $request->getText( 'image' );
		if ($imageID === "") {
			$wikitext = "No image specified";
			$output->addWikiText( $wikitext );
			return;
		}
		$userID = $this->getUser()->getId();
		
		if (!array_key_exists($imageID, $virtualFactoryImages)) {
			$wikitext = "Image $imageID is not in the list of authorized images. Please see [[Special:DockerAccess]]";
			$output->addWikiText( $wikitext );
			return;
		}
		
		global $virtualFactoryURL;
		global $virtualFactoryUser;
		global $virtualFactoryPass;
		
		$context = stream_context_create(array(
			'http' => array(
				'header'  => "Authorization: Basic " . base64_encode("$virtualFactoryUser:$virtualFactoryPass")
			)
		));
		$response = file_get_contents("$virtualFactoryURL/create?user=$userID&image=$imageID", false, $context);
		
		
		$success_text = "/vnc.html";
		# If $response starts with $success_text...
		if (substr($response, 0, strlen($success_text)) === $success_text) {
			$wikitext = "Your VNC virtual instance is ready. To access it, [".$virtualFactoryURL.$response.' please click here].';
		} else {
			$wikitext = "'''Internal Error!'''\n\nServer replied:\n ".$response."\nIf you believe it is a bug, please report it to [mailto:wikifm@kde.org wikifm@kde.org].";
		}
		
		$output->addWikiText( $wikitext );
	}
}