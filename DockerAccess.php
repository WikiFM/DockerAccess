<?php
/* Modeled around the ContributionScores extension */

/** \file
* \brief Contains setup code for the Contribution Scores Extension.
*/

# Not a valid entry point, skip unless MEDIAWIKI is defined
if ( !defined( 'MEDIAWIKI' ) ) {
	echo "DockerAccess extension";
	exit( 1 );
}

$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'Docker Access (virtualfactory)',
	'url' => 'https://www.WikiToLearn.org/',
	'author' => 'Riccardo Iaconelli, WikiToLearn team',
	'descriptionmsg' => 'Mediawiki plugin for WikiToLearn\'s docker virtualfactory',
	'version' => '0.1'
);

$virtualFactoryURL = "";
$virtualFactoryUser = ""; // User for server HTTP authentication
$virtualFactoryPass = ""; // Password for server HTTP authentication
$virtualFactoryImages = array(); // List of images allowed to run: 'image'=>'readableName'


$wgAutoloadClasses['SpecialDockerAccess'] = __DIR__ . '/SpecialDockerAccess.php';
// $wgMessagesDirs['DockerAccess'] = __DIR__ . "/i18n"; # Location of localisation files (Tell MediaWiki to load them)
$wgExtensionMessagesFiles['DockerAccessAlias'] = __DIR__ . '/DockerAccess.alias.php';
$wgSpecialPages['DockerAccess'] = 'SpecialDockerAccess'; # Tell MediaWiki about the new special page and its class name

$wgHooks['ParserFirstCallInit'][] = 'dockerAccess_Setup';

function dockerAccess_Setup( &$parser ) {
	$parser->setHook( 'virtualvnc', 'dockerAccess_Render' );
	return true;
}

function dockerAccess_Render( $input, array $args, Parser $parser, PPFrame $frame ) {
	// Basic check of tag correctness, output an usage summary if the syntax is really incorrect
	if (array_key_exists('image', $args)) {
	
		$image = htmlspecialchars($args['image']);
		global $virtualFactoryImages;
		
		// If the image was explicitly added to the configuration of this wiki...
		if (array_key_exists($image, $virtualFactoryImages)) {
		
			// Default text, overwritten by user's input
			$text = "Click here for accessing a VNC image of type: ".$virtualFactoryImages[$image];
			if ($input) {
				$text = $input;
			}
			$text = htmlspecialchars($text);
			
			return $parser->internalParse("<span class=\"plainlinks\">[{{fullurl:Special:DockerAccess}}/launch?image=$image $text]</span>");
		} else { 
			return $parser->internalParse(htmlspecialchars("'''Could not find an image named '$image'. Please check your spelling!'''"));
		}
	} else {
		return $parser->internalParse(htmlspecialchars("'''Invalid usage!''' Tag usage: <virtualvnc image=imagename /> or <virtualvnc image=imagename>link text</virtualvnc>"));
	}
}