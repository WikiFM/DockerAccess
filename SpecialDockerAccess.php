<?php
class SpecialDockerAccess extends SpecialPage {
	function __construct() {
		parent::__construct( 'DockerAccess' , 'autoconfirmed' );
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
		
		if ( $par === "token" ) {
			$token = $request->getText( 'id' );
			$success = 0;
			$context = stream_context_create(array(
				'http' => array(
					'header'  => array("Authorization: Basic " . base64_encode("$virtualFactoryUser:$virtualFactoryPass"),
								"Content-type: application/x-www-form-urlencoded"),
					'method'  => 'POST',
					'content' => http_build_query(array('token'=>$token))   )
					));
			$response = file_get_contents("$virtualFactoryURL/0.1/query", false, $context);
			$data = json_decode($response)->data;
			$success = $data->status;
			if ( !$success ) {
				$redirect = SkinTemplate::makeSpecialUrl( 'DockerAccess', "token?id=$token" );
				$this->getOutput()->redirect( $redirect );
			} else {
				if ( isset( $_SERVER['HTTPS'] ) ) {
					$encrypted = 1;
					$port = $data->host_ssl_port;
				} else {
					$encrypted = 0;
					$port = $data->host_port;
                }
				$host = $data->hostname;
				$password = $data->instance_password;
				$path = $data->instance_path;
				$url = "http://dockers.wikifm.org/vnc.html?resize=scale&autoconnect=1&host=" . $host . "&port=" . $port . "&password=" . $password . "&path=" . $path . "&encrypted=" . $encrypted;
				$this->getOutput()->redirect( $url );
			}
		}
		
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
		
		$data = array("user" => $userID, "image" => $imageID, "enable_cuda" => 1);
		
		$context = stream_context_create(array(
			'http' => array(
				'header'  => array("Authorization: Basic " . base64_encode("$virtualFactoryUser:$virtualFactoryPass"),
							"Content-type: application/x-www-form-urlencoded"),
				'method'  => 'POST',
				'content' => http_build_query($data)
			)
		));
		
		$response = file_get_contents("$virtualFactoryURL/0.1/create", false, $context);
		
		$token = json_decode($response)->data->token;
		$success = 0;
		$data = 0;
		
		$redirect = SkinTemplate::makeSpecialUrl( 'DockerAccess', "token?id=$token" );
		
		$this->getOutput()->redirect( $redirect );
		
		
// 		$success_text = "/vnc.html";
// 		# If $response starts with $success_text...
// 		if (substr($response, 0, strlen($success_text)) === $success_text) {
// 			$wikitext = "Your VNC virtual instance is ready. To access it, [".$virtualFactoryURL.$response.' please click here].';
// 		} else {
// 			$wikitext = "'''Internal Error!'''\n\nServer replied:\n ".$response."\nIf you believe it is a bug, please report it to [mailto:sysadmin@wikitolearn.org sysadmin@wikitolearn.org].";
// 		}
// 		
// 		$output->addWikiText( $wikitext );
	}
}
