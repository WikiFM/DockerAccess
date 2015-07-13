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
	'url' => 'https://www.wikifm.org/',
	'author' => 'Riccardo Iaconelli, WikiFM team',
	'descriptionmsg' => 'Mediawiki plugin for WikiFM\'s docker virtualfactory',
	'version' => '0.1'
);

$virtualFactoryURL = "";
$virtualFactoryUser = "";
$virtualFactoryPass = "";

$wgAutoloadClasses['SpecialDockerAccess'] = __DIR__ . '/SpecialDockerAccess.php'; # Location of the SpecialDockerAccess class (Tell MediaWiki to load this file)
// $wgMessagesDirs['DockerAccess'] = __DIR__ . "/i18n"; # Location of localisation files (Tell MediaWiki to load them)
$wgExtensionMessagesFiles['DockerAccessAlias'] = __DIR__ . '/DockerAccess.alias.php'; # Location of an aliases file (Tell MediaWiki to load it)
$wgSpecialPages['DockerAccess'] = 'SpecialDockerAccess'; # Tell MediaWiki about the new special page and its class name

$wgHooks['ParserFirstCallInit'][] = 'dockerAccess_Setup';

function dockerAccess_Setup( &$parser ) {
	$parser->setHook( 'virtualinstance', 'dockerAccess_Render' );
	return true;
}

function dockerAccess_Render( $input, array $args, Parser $parser, PPFrame $frame ) {
	// Nothing exciting here, just escape the user-provided
	// input and throw it back out again
	return htmlspecialchars( $input );
}