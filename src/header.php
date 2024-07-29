<?php
/*
 * This code makes HTMLTidy do it's magic:
 */

function tidy_entire_page(string $buffer): string {
	// Instantiate a copy of HTMLTidy:
	$tidy = new tidy;

	// Configure HTMLTidy:
	$tidy->parseString( $buffer, [
		'indent'                      => true,           // Indent, please
		'indent-with-tabs'            => true,           // Indent with tabs, please
		'output-html'                 => true,           // HTML5, not XHTML
		'force-output'                => true,           // Always output, even in case of errors
		'warn-proprietary-attributes' => false,          // proprietary attributes aren't a cause for concern
		'logical-emphasis'            => true,           // replace <i>, <b>, tags
		'css-prefix'                  => 'tidy-',        // when writing CSS rules to replace e.g. <font>, use this class prefix
		'fix-style-tags'              => true,           // any hoist <style> blocks to the <head>
		'repeated-attributes'         => 'keep-last',    // <elem attr="prop1" attr="prop2"> coalesces to <elem attr="prop2">
		'escape-cdata'                => true,           // ditch <![CDATA[]]> wrappers; this is HTML5 not XHTML
		'hide-comments'               => true,           // drop <!-- comments --> to help minimise file size
		'break-before-br'             => true,           // always \n before you <br>
		'priority-attributes'         => 'id,class,name',// put these attributes first
		'sort-attributes'             => 'alpha',        // and the rest in alphabetical order
		'tidy-mark'                   => false,          // Don't advertise yourself in the <meta>, please
		'wrap'                        => 200,            // Wrap at this many chars
		'new-inline-tags'             => 'data',         // Ensure that empty <data> tags aren't stripped (DanQ.me uses these for e.g. lat/lng on checkins)
	], 'utf8' );                                       // Ensure we treat the incoming document as UTF-8, 'cos it probably is

	// Repair broken HTML and tidy according to the rules above:
	$tidy->cleanRepair();

	return $tidy;
}
ob_start( 'tidy_entire_page' );

/*
 * Everything below here is boilerplate TwentyTwentyOne!
 */









/**
 * The header.
 *
 * This is the template that displays all of the <head> section and everything up until main.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */

?>
<!doctype html>
<html <?php language_attributes(); ?> <?php twentytwentyone_the_html_classes(); ?>>
<head>
				<meta charset="<?php bloginfo( 'charset' ); ?>" />
				<meta name="viewport" content="width=device-width, initial-scale=1" />
				<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
				<a class="skip-link screen-reader-text" href="#content">
								<?php
								/* translators: Hidden accessibility text. */
								esc_html_e( 'Skip to content', 'twentytwentyone' );
								?>
				</a>

				<?php get_template_part( 'template-parts/header/site-header' ); ?>

				<div id="content" class="site-content">
								<div id="primary" class="content-area">
												<main id="main" class="site-main">