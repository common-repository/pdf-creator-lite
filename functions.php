<?php


//--- boot up TCpdf --------------------------------------------

include( EXPORT_AS_PDF_PATH . 'tcpdf/config/tcpdf_config.php' );
include( EXPORT_AS_PDF_PATH . 'tcpdf/tcpdf.php' );


//--- extend TCPDF class ---------------------------------------

class SSA_PDF extends TCPDF
{

	var $bg_rgb = array(
		'red' 	=> 255,
		'green' => 255,
		'blue' 	=> 255 
	);

	var $text_font = 'helvetica';
	var $text_hex = '#363636';
	var $link_hex = '#3333ff';

	var $text_rgb = array(
		'red' 	=> 40,
		'green' => 40,
		'blue' 	=> 40 
	);
	
	var $link_rgb = array(
		'red' 	=> 40,
		'green' => 40,
		'blue' 	=> 255 
	);
		
	var $displayDate = '';
	var $siteURL = '';
	var $siteTitle = '';
	
	
	//--- custom header
	public function Header()
	{
		//set the bg colour
		$bMargin = $this->getBreakMargin(); 		//get the current page break margin
		$auto_page_break = $this->AutoPageBreak; 	//get current auto-page-break mode
		$this->SetAutoPageBreak(false, 0); 			//disable auto-page-break						
		
		$this->Rect	( 
			0,
			0,
			210,
			297,
			'F',
			array(),
			array( $this->bg_rgb['red'], $this->bg_rgb['green'], $this->bg_rgb['blue'] ) 
		);	
		
		$this->SetAutoPageBreak( $auto_page_break, $bMargin ); 	//restore auto-page-break status
		$this->setPageMark(); 									//set the starting point for the page content
		
		//make header content
		$headerContent = '<p style="font-family:' . $this->text_font . '; font-size:14px; color:' . $this->text_hex . '; line-height:20px;">' . $this->siteTitle . '&nbsp;&nbsp;<span style="font-size:11px;">- ' . $this->displayDate . '<br />View online at <a href="' . $this->siteURL . '" style="color:' . $this->link_hex . '; text-decoration:none;">' . $this->siteURL . '</a></span></p>';
		
		$this->writeHTMLCell(
			0,
			3,
			PDF_MARGIN_LEFT,
			5,
			$headerContent,
			0,
			2,
			false,
			true,
			'L',
			false
		);
		
		$style = array( 'width' => 0.25, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'phase' => 1, 'color' => array($this->text_rgb['red'], $this->text_rgb['green'], $this->text_rgb['blue']) );
		$this->Line( 20, 17, 188, 17, $style );
	}
	
	
	//--- custom footer
	public function Footer ()
	{
		$this->SetY( -15 ); //position 15 mm from bottom		
		$this->SetFont( $this->text_font , 'N', 8 );

		$this->SetTextColorArray	(
			array( $this->text_rgb['red'], $this->text_rgb['green'], $this->text_rgb['blue'] ),
			false
		);
		
		$this->Cell( 173, 10, 'Page '.$this->getAliasNumPage(), 0, false, 'R', 0, '', 0, false, 'T', 'M' );
		
		$style = array( 'width' => 0.25, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'phase' => 1, 'color' => array($this->text_rgb['red'], $this->text_rgb['green'], $this->text_rgb['blue']) );
		$this->Line(20, 282, 188, 282, $style);
	}
	
} //close class SSA_PDF






/**
 *	Writes the admin page
 */
function SSAPDF_export_site_as_pdf ()
{
	
	$blogID = get_current_blog_id();
	
	?>
		
		<style type="text/css">
			.mp3j-tabbuttons-wrap {
				position:relative; border-bottom:1px solid #ccc; height:30px; padding:5px 0 0 0; width:auto; max-width:800px; overflow:visible;
			}
			.mp3j-tabbutton { 
				float:left; 
				padding:6px 18px 6px 18px; 
				font-size:11px; margin:0 2px 0 0;
				background:#ddd; 
				font-weight:700;
				color:#777;
				-webkit-border-top-left-radius: 3px;
				-webkit-border-top-right-radius: 3px;
				-moz-border-radius-topleft: 3px;
				-moz-border-radius-topright: 3px;
				border-top-left-radius: 3px;
				border-top-right-radius: 3px;
				border-bottom:1px solid #ccc;
				cursor:pointer;
			}
			.active-tab {
				background:#f0f0f0;
				border:1px solid #ccc;
				border-bottom:0px;
				padding:5px 17px 7px 17px;
				color:#444;
			}

			.mp3j-tabs-wrap { position:relative; height:auto; border-bottom:1px solid #bbb; max-width:800px; }
			.mp3j-tab { position:relative; height:auto; padding:20px 0px 40px 12px; background:url('images/admin-grad-1.png') repeat-x left bottom; }

			#mp3j_tab_1 { display:none; }
			#mp3j_tab_2 { display:none; }
		</style>
	
	
		<div class="wrap">
			
			<h2>PDF Creator &nbsp;<span style="font-size:8px;">v 1.1.3</span></h2>
			
			
						
			
	<?php
	
	
	if ( isset($_POST['formSubmit']) )
	{
		
		SSAPDF_generatePDF();
				
		$siteTitle = get_bloginfo( 'name', 'raw' );
		$pdfFileName = preg_replace( "/&#?[a-z0-9]{2,8};/i", "", $siteTitle );
		$pdfFileName = str_replace( " ", "-",  $pdfFileName );
		$pdfFileName = str_replace( "/", "_",  $pdfFileName );
		$pdfFileName = $pdfFileName . ".pdf"; 
		
		$protocol = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ) ? 'https' : 'http';
		$rootURL = $protocol . '://' . $_SERVER['HTTP_HOST'];
		
		$fileLocal = '/ssapdf_tmp/blog' . $blogID . '/' . $pdfFileName;
		$fileURL = $rootURL . $fileLocal;
		
		$pluginFolder = plugins_url('', __FILE__);
		
		echo '<br />';
		echo '<p>Your PDF is ready!</p>';
		
		echo '<a href="' . $fileURL . '" id="forceDownloadLink" class="button-primary" style="padding-left:25px; padding-right:25px;">Download PDF</a>';
		
		echo '&nbsp;<a id="previewLink" class="button-primary" style="padding-left:25px; padding-right:25px;" target="_blank" href="' . $rootURL . '/ssapdf_tmp/blog' . $blogID . '/' . $pdfFileName . '">Preview</a>';
		echo '<br /><p>Please note that preview is only available in some browsers.</p>';
		
		echo '<div id="previewDownload"></div>';
		
		echo '<p><a href="' . $_SERVER["REQUEST_URI"] . '">&laquo; Back to Export Settings</a></p>';
		
		echo '<br />';
		echo '<p style="color:#777;"><em>Problems downloading? Here is a direct link to the PDF file, to save it you can right-click it<br />and choose \'save target\': </em>
				&nbsp;<a href="' . $fileURL . '">' . $pdfFileName . '</a></p>';
		
		echo '<div id="forceDownload"></div>';
		?>
		
		
		
		<script type="text/javascript">
		
		var SSAPDF = {};
		
		/*
		SSAPDF.read_cookie = function ( name ) {
			var i, cookie, allCookies = document.cookie.split('; ');
			if ( allCookies.length > 0 ) {
				for ( i = 0; i < allCookies.length; i += 1 ) {
					cookie = allCookies[i].split( '=' );
					if ( cookie[0] === name ) {
						return cookie[1];
					}
				}
			}
			return false;
		};

		SSAPDF.write_cookie = function ( name, value, days ) {
			var date, expires = "";
			if ( days ) {
				date = new Date();
				date.setTime( date.getTime() + (days*24*60*60*1000) );
				expires = "; expires=" + date.toGMTString();
			}
			document.cookie = name + "=" + value + expires + "; path=/";
			return this.read_cookie( name );
		};
		*/
		
		SSAPDF.addForceFrame = function ( file ) {
			jQuery('#forceDownload').empty().append('<iframe id="forceDownloadFrame" name="forceDownloadFrame" src="<?php echo $pluginFolder; ?>/download.php?pdf=loc' + file + '" style="display:none;"></iframe>');
		};
		
		SSAPDF.addPreviewFrame = function ( url ) {
			jQuery('#previewDownload').empty().append('<iframe style="width:422px; height:580px; border:1px solid #aaa;" id="previewFrame" name="previewFrame" src="' + url + '"></iframe>');
		};
		
		
		jQuery(document).ready( function () {
		
			jQuery('#forceDownloadLink').click( function ( e ) {
				SSAPDF.addForceFrame( '<?php echo $fileLocal; ?>' );
				e.preventDefault();
			});
			
			jQuery('#previewLink').click( function ( e ) {
				SSAPDF.addPreviewFrame( '<?php echo $fileURL; ?>' );
				jQuery(this).text('Refresh Preview');
				e.preventDefault();
			});
		
		});
		
		</script>
		
		
		
		
		
	<?php
	}
	else
	{
			
			
			echo '<p>This will export all or some of the pages in this site as a PDF file.<br />Use the options below to choose how your PDF will be created.</p><br />';
			
			
			
			//$wp_install_url = network_site_url(); //falls back to site_url() if not multisite.
			//$path = explode( $_SERVER['HTTP_HOST'], $wp_install_url, 2 );
			//$wp_install_path = urlencode( $_SERVER['DOCUMENT_ROOT'] . $path[1] . '/' );
			
			//echo '<form target="_blank" method="post" action="' . plugins_url() . '/simple-save-as-pdf/exportpdf.php?blogID=' . get_current_blog_id() . '&wppath=' . $wp_install_path . '">';
			//echo '<form method="post" action="' . plugins_url() . '/simple-save-as-pdf/exportpdf.php?blogID=' . get_current_blog_id() . '&wppath=' . $wp_install_path . '">';
			//echo '<form method="post" action="' . $_SERVER["REQUEST_URI"] . '?blogID=' . get_current_blog_id() . '">';
			echo '<form method="post" action="' . $_SERVER["REQUEST_URI"] . '">';
			
			
			////
			//$woof = wp_upload_dir();
			//print_r( $woof );
			//echo '<br /><br />';
			//echo $_SERVER['DOCUMENT_ROOT'];
			//echo '<br /><br />';
			//echo getcwd();
			?>
			
			
			<div class="mp3j-tabbuttons-wrap">
				<div class="mp3j-tabbutton" id="mp3j_tabbutton_0">FORMAT</div>
				<div class="mp3j-tabbutton" id="mp3j_tabbutton_1">INSERTS</div>
				<div class="mp3j-tabbutton" id="mp3j_tabbutton_2">PAGES</div>
				<br class="clearB" />
			</div>
			<div class="mp3j-tabs-wrap">
				
	<!-- TAB 0 - FORMAT.......................... -->
				<div class="mp3j-tab" id="mp3j_tab_0">
					
					<?php
					echo '<style type="text/css"> ';
					echo 	'table.pdf td { vertical-align:top; padding:10px; } ';
					echo '</style>';		
					
					
					echo '<table class="pdf"><tbody>';
					
					echo 	'<tr>';
					echo 		'<td style="width180px;"><input type="radio" value="none" name="useCSS" id="radioNone" checked="checked" /> <label for="radioNone">Default Style</label></td>';
					echo 		'<td><span class="description" style="color:#aaa;"></span></td>';
					echo 	'</tr>';		
					
					echo 	'<tr>';
					echo 		'<td><input type="radio" value="custom" name="useCSS" id="radioCustom" /> <label for="radioCustom">Customise Style</label></td>';
					echo 		'<td>';
					echo			'<span class="description" style="color:#aaa;">&nbsp;&nbsp; Use these settings to customise your PDF.</span>';
					echo 			'<div style="padding:15px 0px 0px 10px;">';
					
					echo				'<div style="float:left; width:100px;">Font:</div>';
					echo				'<input type="text" value="#333" name="font_cpicker" id="font_cpicker"/>';
					echo 				'&nbsp; &nbsp;<select name="fontFamily" id="fontFamily">';
					echo 					'<option value="helvetica" selected="selected">Arial / Helvetica</option>';
					echo 					'<option value="times">Times New Roman</option>';
					echo 					'<option value="courier">Courier</option>';
					echo 				'</select>';
					echo				'<br clear="left" /><br />';
					
					echo				'<div style="float:left; width:100px;">Background:</div>';
					echo				'<input type="text" value="#fff" name="bg_cpicker" id="bg_cpicker"/>';
					echo				'<br clear="left" /><br />';
					
					echo				'<div style="float:left; width:100px;">Links:</div>';
					echo				'<input type="text" value="#4848ff" name="link_cpicker" id="link_cpicker"/>';
					echo				'<br clear="left" />';
					
					echo 			'<div>';
					echo 		'</td>';
					echo 	'</tr>';		
					
					echo '</tbody></table>';
					?>
					
				</div>
				
				
	<!-- TAB 1 - INSERTS.......................... -->			
				<div class="mp3j-tab" id="mp3j_tab_1">
				
				<?php
				echo '<p><input type="checkbox" name="addFrontPage" id="addFrontPage" style="margin-left:10px;" /> <label for="addFrontPage">Add a Front Page.</label></p>';
				echo '<p><input type="checkbox" name="addToC" id="addToC" style="margin-left:10px;" /> <label for="addToC">Add a Table of Contents.</label></p>';
				?>
				
				</div>
				
				
	<!-- TAB 2 - PAGES.......................... -->			
				<div class="mp3j-tab" id="mp3j_tab_2">
				
				<?php
				$parents = array();
			
				$args = array(
					'sort_order' => 'ASC',
					'sort_column' => 'menu_order',
					'hierarchical' => 1,
					'exclude' => '',
					'include' => '',
					'post_type' => 'page',
					'post_status' => 'publish'
				);
				$pages = get_pages( $args );
				
				echo '<div style="height:30px; border-bottom:1px solid #ccc;"><input type="checkbox" value="true" id="checkAll" name="checkAll" checked="checked" /><label for="checkAll">Uncheck / Check All</label></div><br />';
				
				if ( !empty($pages) && $pages != false )
				{
					foreach ($pages as $page)
					{
						$depth = SSAPDF_getPageDepth( $page->ID );
						$fontWeight = ($depth == 0) ? '700' : '500';
						if ( $depth == 0 )
						{
							$checkerClass = ' page' . $page->ID;
							$addCheckerClass = false;
							$parents[] = $page->ID;
						}
						else
						{
							$addCheckerClass = true;
						}
						
						echo '<p style="font-weight:' . $fontWeight . '; margin:0 0 4px ' . ($depth > 0 ? 2*$depth : '') . '0px;"><input type="checkbox" name="checkerPage[' . $page->ID . ']" class="checkerPage' . ($addCheckerClass === true ? $checkerClass : '') . '" value="' . $page->ID . '" checked="checked" id="page' . $page->ID . '" /><label for="page' . $page->ID . '">' . $page->post_title . '</label></p>';
					}
				}
				?>
				
				</div>
				
				
			</div>
			
			
			<?php						
			echo '<br /><br />';
			echo '<div style="float:left; width:120px; height:50px; padding-top:5px;"><input type="submit" value="Export PDF" name="formSubmit" class="button-primary" /></div>';
			echo '<p style="float:left; margin:0;">Your PDF will be generated in a new window, for large sites<br />it may take a minute or two to complete the conversion!</p>';
			
			echo '</form>';
			
			?>
			
			<script src="<?php echo SSAPDF_PLUGIN_URL; ?>/colourpicker/spectrum.js"></script>
			
			
			<script type="text/javascript">
				
				function checkCustomRadio () {
					jQuery('#radioCustom').attr('checked', true);
				};
				
				
				var SSA_ADMIN = {
			
					last_tab: 0,
					
					add_tab_listener: function ( j ) {
						var that = this;
						jQuery('#mp3j_tabbutton_' + j).click( function (e) {
							if ( j !== that.last_tab ) {
								jQuery('#mp3j_tab_' + that.last_tab).hide();
								jQuery('#mp3j_tabbutton_' + that.last_tab).removeClass('active-tab');
								jQuery('#mp3j_tab_' + j).show();
								jQuery('#mp3j_tabbutton_' + j).addClass('active-tab');
								that.last_tab = j;
							}
						});
					},
					
					init: function () {
						var j;
						for ( j = 0; j < 3; j += 1 ) {
							this.add_tab_listener( j );
						}
						jQuery('#mp3j_tabbutton_' + this.last_tab).addClass('active-tab');
					}
				};
					
				
				jQuery(document).ready( function () {
				
					jQuery('#bg_cpicker').spectrum({
						color: "#fff",
						clickoutFiresChange: true,
						change: function(color) {
							checkCustomRadio();
						}
					});
					jQuery('#font_cpicker').spectrum({
						color: "#333",
						clickoutFiresChange: true,
						change: function(color) {
							checkCustomRadio();
						}
					});
					jQuery('#link_cpicker').spectrum({
						color: "#4848ff",
						clickoutFiresChange: true,
						change: function(color) {
							checkCustomRadio();
						}
					});
					
					jQuery('#fontFamily').on( 'change', function ( e ) {
						checkCustomRadio();
					});
					
					jQuery('#checkAll').on( 'change', function ( e ) {
						var checked = jQuery(this).is(':checked');
						jQuery( '.checkerPage' ).prop('checked', checked);
					});
					
					
					
		<?php
		if ( ! empty( $parents ) )
		{
			$selector = '';
			$c = count( $parents );
			foreach ( $parents as $i => $pageID )
			{
				$selector .= '#page' . $pageID . ($i < ($c-1) ? ', ' : ''); 
			}
		?>
			
					jQuery('<?php echo $selector; ?>').on( 'change', function ( e ) {
						var sel = jQuery(this).attr('id');	
						var checked = jQuery(this).is(':checked');
						jQuery( '.' + sel ).prop('checked', checked);
					});


		<?php
		}
		?>			
					SSA_ADMIN.init();
					
				});
						
			</script>
			
		</div>
    
<?php
	}

}





/**
 *
 */
function SSAPDF_generatePDF ()
{
	//set up some default style options
	$bg_rgb = array(
		'red' 	=> 255,
		'green' => 255,
		'blue' 	=> 255 
	);

	$text_font = 'helvetica';
	$text_hex = '#363636';
	$link_hex = '#3333ff';

	//swap in custom options if needed
	if ( $_POST['useCSS'] == 'custom' )
	{
		$bg_rgb = SSAPDF_hex2RGB( $_POST['bg_cpicker'] );
		
		$text_font = $_POST['fontFamily'];
		$text_hex = $_POST['font_cpicker'];
		$link_hex = $_POST['link_cpicker'];
	}

	$text_rgb = SSAPDF_hex2RGB( $text_hex );
	$link_rgb = SSAPDF_hex2RGB( $link_hex );
	
	//make a display creation date
	$utsNow = strtotime('now');
	$displayDate = date( 'jS F Y', $utsNow );
	
	//grab the site info
	$siteURL = get_bloginfo('url');
	$siteTitle = get_bloginfo( 'name', 'raw' ); 
	
	//clean up site title to use as filename
	$pdfFileName = preg_replace( "/&#?[a-z0-9]{2,8};/i", "", $siteTitle );
	$pdfFileName = str_replace( " ", "-",  $pdfFileName );
	$pdfFileName = str_replace( "/", "_",  $pdfFileName );
	$pdfFileName = $pdfFileName . ".pdf";
	
	
	//--- init TCpdf ---------------------------------------
	
	$pdf = new SSA_PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	
	//pass style settings into the class vars
	$pdf->bg_rgb = $bg_rgb;
	
	$pdf->text_font = $text_font;
	$pdf->text_hex = $text_hex;
	$pdf->link_hex = $link_hex;

	$pdf->text_rgb = $text_rgb;
	$pdf->link_rgb = $link_rgb;
	
	$pdf->displayDate = $displayDate;
	$pdf->siteURL = $siteURL;
	$pdf->siteTitle = $siteTitle;
	
	
	
	
	// set document information
	$pdf->SetCreator('SSA-PDF(TC)');
	$pdf->SetAuthor('');
	$pdf->SetTitle( $pdfFileName );
	$pdf->SetSubject('');
	$pdf->SetKeywords('');

	// set header data
	$pdf->SetHeaderMargin( PDF_MARGIN_HEADER );
	// set footer data
	$pdf->SetFooterMargin( PDF_MARGIN_FOOTER );
	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	// set margins
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	// set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
	// set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	
	// set default font subsetting mode
	//$pdf->setFontSubsetting( true );

	// Set font
	// dejavusans is a UTF-8 Unicode font, if you only need to
	// print standard ASCII chars, you can use core fonts like
	// helvetica or times to reduce file size.
	//$pdf->SetFont( 'dejavusans', '', 11, '', true, true );
	
	$pdf->SetTextColorArray	(
		array( $text_rgb['red'], $text_rgb['green'], $text_rgb['blue'] ),
		false
	);
		
	
	//--- build the contents ---------------------------------------
	
	$blogID = get_current_blog_id();
	
	if ( function_exists('switch_to_blog') ) //check multisite
	{
		//$blogID = $_GET['blogID'];
		switch_to_blog( $blogID );
	}
	
	if ( current_user_can( 'manage_options' ) ) //Only let them download the file if they are admin
	{ 
		//get the WP pages
		$args = array(
			'sort_order' => 'ASC',
			'sort_column' => 'menu_order',
			'hierarchical' => 1,
			'exclude' => '',
			'include' => '',
			'post_type' => 'page',
			'post_status' => 'publish'
		);
		$pages = get_pages( $args );
		
		
		//set some extra document css 
		$cssStr = '<style type="text/css"> ';
		$cssStr .= '.pageBreak { page-break-after: always; } ';
		$cssStr .= '* { font-family:' . $text_font . ';	} ';
		/*
		$cssStr .= '
				p { color:' . $text_hex . '; } 
				h1 { color:' . $text_hex . '; } 
				h2 { color:' . $text_hex . '; } 
				h3 { color:' . $text_hex . '; } 
				h4 { color:' . $text_hex . '; } 
				h5 { color:' . $text_hex . '; } 
				h6 { color:' . $text_hex . '; } 
				li { color:' . $text_hex . '; } 
			';
		*/
		$cssStr .= 'a { color:' . $link_hex . '; } ';
		$cssStr .= 'a:visited { color:' . $link_hex . '; } ';
		$cssStr .= ' </style>';
				

		//make a front page
		if ( isset( $_POST['addFrontPage'] ) )
		{
			$displayTitle = get_bloginfo( 'name', 'display' );
			//$htmlStr .= '<div class="pageBreak">';
			$htmlStr = '&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br /><h1 style="font-size:40px; text-align:center; text-decoration:underline;">' . $displayTitle . '</h1>';
			$htmlStr .= '<p style="text-align:center;">&nbsp;<br />&nbsp;<br />PDF Created on ' . $displayDate . '<br /><a href="' . $siteURL . '">' . $siteURL . '</a></p>';
			//$htmlStr .= '</div>';
			
			$pdf->AddPage();
			$pdf->writeHTML	(
				$cssStr . $htmlStr,
				true,
				false,
				false,
				false,
				'' 
			);
		}
		

		//build the WP pages
		$pageno = 1;
		$checkedPages = $_POST['checkerPage'];
		foreach ($pages as $page_data)
		{
			if ( isset($checkedPages[$page_data->ID]) )
			{
				$title = $page_data->post_title; 
				$content = $page_data->post_content;
				
				//remove any sitemap shortcodes
				$content = preg_replace( '/\[sitemap_pages[^\]]*]/i', '', $content );
				
				$depth = SSAPDF_getPageDepth( $page_data->ID );
				
				//only add the page if it's not contents page
				if ( $title != 'Contents' )
				{
					//prepend the root url to any local absolute img paths
					$PRcontent = preg_replace_callback(
						"/<img [^>]*src=['\"](\/[^\"']*)['\"][^>]*>/iU",
						function ( $matches ) {				
							$protocol = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ) ? 'https' : 'http';
							$rootURL = $protocol . '://' . $_SERVER['HTTP_HOST'];
							return '<img src="' . $rootURL . $matches[1] . '" />';
						},
						$content
					);
					
					//run wp formatting filters and process shortcodes
					$PRcontent = apply_filters( 'the_content', $PRcontent ); 
								
					$topPage = ( isset($_POST['addFrontPage']) ) ? '#2' : '#1';
					
					//build the page content
					$htmlStr = '<h1>' . $title . '</h1>';
					$htmlStr .= $PRcontent;
					
					//tcpdf issue linking to toc. 
					//only adding a top link when there's a cover page or no TOC,
					//jumps you to cover page regardless.
					if ( isset( $_POST['addFrontPage'] ) || ! isset( $_POST['addToC'] ) )
					{
						$htmlStr .= '<br /><a href="#1" style="font-size:10px;">Top</a><br /><br />';
					}
					
					$pdf->AddPage();				
					$pdf->Bookmark( $title, $depth, 0, '', 'B', array($link_rgb['red'], $link_rgb['green'], $link_rgb['blue']), 0, '#TOC' );
					
					$pdf->writeHTML	(
						$cssStr . $htmlStr,
						true,
						false,
						false,
						false,
						'' 
					);
									
					$pageno++;
				}
			}
		}
		
		
		//add table of contents
		if ( isset( $_POST['addToC'] ) )
		{
			$pdf->addTOCPage();

			// write the TOC title
			$pdf->SetFont( $text_font, 'B', 16);
			$pdf->MultiCell(0, 16, 'Table of Contents', 0, 'L', 0, 1, '', '', true, 0);

			$pdf->SetFont( $text_font, '', 11);
			$insertAt = ( isset($_POST['addFrontPage']) ) ? 2 : 1;
			$pdf->addTOC( $insertAt, $text_font, '.', 'TOC', 'B', array( $link_rgb['red'], $link_rgb['green'], $link_rgb['blue'] ));

			$pdf->endTOCPage();
		}
		
		
		//--- output the PDF ---------------------------------------
		
		
		$basePath = $_SERVER['DOCUMENT_ROOT'] . 'ssapdf_tmp/blog' . $blogID;
		if ( ! file_exists( $basePath ) ) {
			mkdir( $basePath, 0777, true );
		}
		
		//temp set server limit and timeout
		ini_set("memory_limit", "512M");
		ini_set("max_execution_time", "600");
		ini_set("allow_url_fopen", "1");
		
		// Close and output PDF document.
		//$pdf->Output( $pdfFileName, 'I');
		//$pdf->Output( '/var/www/efoliodev/htdocs/sites/' . $pdfFileName, 'F');
		
		//$wpUploadDir = wp_upload_dir(); //will create dir if doesn't yet exist.
		//$pdf->Output( '/var/www/efoliodev/htdocs/sites/pdf_tmp/' . $pdfFileName, 'F');
		$pdf->Output( $basePath . '/' . $pdfFileName, 'F');
		
	}
	else
	{
		echo '<html><head></head><body><p>You do not have permission to perform this action.<br />Please contact an administrator.</p></body></html>';
	}

} 






/**
 *
 */
function SSAPDF_hex2RGB ( $hexStr, $returnAsString = false, $seperator = ',' )
{
	$hexStr = preg_replace( "/[^0-9A-Fa-f]/", '', $hexStr ); // Gets a proper hex string
	$rgbArray = array();
	
	if (strlen($hexStr) == 6) { //If a proper hex code, convert using bitwise operation. No overhead... faster
		$colorVal = hexdec($hexStr);
		$rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
		$rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
		$rgbArray['blue'] = 0xFF & $colorVal;
	} elseif (strlen($hexStr) == 3) { //if shorthand notation, need some string manipulations
		$rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
		$rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
		$rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
	} else {
		return false; //Invalid hex color code
	}
	return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray; // returns the rgb string or the associative array
}


/**
 *
 */
function SSAPDF_getPageDepth ( $id )
{
	return count( get_post_ancestors( $id ) );
}


/**
 *	Registers a new admin 
 *	sub-menu page under 'Tools'
 */
function SSAPDF_Export_as_PDF ()
{
    $adminPage = add_management_page( 'PDF Creator', 'PDF Creator', 'manage_options', 'exportAsPDF', 'SSAPDF_export_site_as_pdf' );
	add_action( 'admin_head-'. $adminPage, 'SSAPDF_add_to_header' );
}


/**
 *	Enqueues colour picker css 
 */
function SSAPDF_add_to_header ()
{
	wp_enqueue_style( 'spectrumcp', SSAPDF_PLUGIN_URL . '/colourpicker/spectrum.css' );
}

?>