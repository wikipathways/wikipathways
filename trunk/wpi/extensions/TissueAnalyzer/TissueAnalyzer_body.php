<?php

class TissueAnalyzer extends SpecialPage {
	protected $name = 'TissueAnalyzer';

	function TissueAnalyzer() {
		SpecialPage::SpecialPage ( $this->name  );
		self::loadMessages();
	}

	function execute($par) {
		global $wgOut, $wgUser, $wgLang;
		$this->setHeaders ();
		$wgOut->setPagetitle ("TissueAnalyzer");

		$species = (isset ( $_GET ["species"] )) ? $_GET ["species"] : "Human";
		$cutoff = (isset ( $_GET ["cutoff"] )) ? $_GET ["cutoff"] : "5";
		$dataset = (isset ( $_GET ["dataset"] )) ? $_GET ["dataset"] : "E-MTAB-2836";
		$generic = (isset ( $_GET ["generic"] )) ? $_GET ["generic"] : "";
		$select = $_GET ["select"];
		
				
		$datasetSelect = "<SELECT name='dataset' id='dataSelect' size='1'>";
		$path = "wpi/bin/TissueAnalyzer/datasets/datasets.config";
		$datasetFile = fopen($path, r);
		$hashArray = array ();
		$speciesArray = array();
		while ( ! feof ( $datasetFile ) ) {
			$line = fgets ( $datasetFile );
			$pieces = explode ( "\t", $line );
			$id = $pieces [0];
			if ($id === '')break;
			$datasetSpecies = 	$pieces [1];
			array_push ($speciesArray, $pieces[1]);
			$hashArray[$id]["species"] = $datasetSpecies;
			$hashArray[$id]["short"] = $pieces [2];
			if (strcmp ( trim ( $species ),trim ($datasetSpecies )) == 0){		
				$datasetSelect .= (strcmp ( trim ( $dataset ), trim ( $id ) ) == 0) ? "<option selected=\"selected\">$id</option>" : "<option>$id</option>";
			}
		}
		fclose ( $datasetFile );
		$datasetSelect .="</SELECT>";
		$speciesArray = array_unique($speciesArray);

		$topTenFile = fopen("wpi/bin/TissueAnalyzer/datasets/".$dataset."_generic.txt", r);
		$topTen = array ();
		while (!feof($topTenFile)) {
			array_push ( $topTen, trim (fgets($topTenFile)) );
		}
		fclose ( $topTenFile );

		$welcomePage = false;
		if (!isset ( $select )) {
				$select="adipose tissue";
				$welcomePage = true;
		}	

		$this->addJs($topTen,$hashArray);		
		$this->createCriteriaDiv($select, $dataset, $cutoff, $species, $datasetSelect, $speciesArray, $generic);		
		$this->createTableResults($select, $dataset, $cutoff, $topTen);	
		$this->createViewerDivs($welcomePage, $cutoff);
		
		return true;
	}

	function addJs($topTen,$hashArray){
		global $wgOut;
		$top = json_encode($topTen);
		$hash = json_encode($hashArray);
		
		//Add CSS
		//Hack to add a css that's not in the skins directory
		global $wgStylePath;
		$oldStylePath = $wgStylePath;
		$wgStylePath = "/wpi/lib/tissueanalyzer/";
		$wgOut->addStyle("/fancybox/jquery.fancybox-1.3.4.css");
		$wgStylePath = $oldStylePath;

		$wgOut->addScriptFile('/wpi/lib/tissueanalyzer/fancybox/jquery.fancybox-1.3.4.js');		
		$wgOut->addScript('<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');
		$wgOut->addScriptFile("/wpi/lib/tissueanalyzer/ChartFancy.js");

		$wgOut->addScript('
				<script language="JavaScript">
					function doToggleTA( elId, msg, expand, collapse ) {
							$("#"+elId+" .toggleMe").toggle();
							if( msg.innerHTML == expand ) {
									msg.innerHTML = collapse;
									$("#check").prop("checked", true);
									checkGeneric();	
							} else {
									msg.innerHTML = expand;
									$("#check").prop("checked", false);check();
							}
					}
					function checkGeneric() {
						var js_array = '.$top.';
						if ($("#check").is(":checked")){
							$(".toggleMe").each(function() {
								if (js_array.indexOf(this.id) != -1)  {
									$(this).show();
								}
							});
						}
						else{
							$(".toggleMe").each(function() {
								if (js_array.indexOf(this.id) != -1)  {
									$(this).hide();
								}
							});
						}
					}
				</script>');
		$wgOut->addScript('<script language="JavaScript">
					function tissue_viewer(id,genes,pathway_name){
						genes = genes.replace(/\./g," ");
						$("#pwyname").attr("style","");
						document.getElementById("pwyname").innerHTML="<b>Selected pathway:</b> " + pathway_name;
						$("#my-legend").attr("style","");
						$("#path_viewer").attr("src",
						"http://www.wikipathways.org/wpi/PathwayWidget.php?id="+id+genes);
						$("#path_viewer").attr("style","overflow:hidden;");
					}
				</script>');
 		$wgOut->addScript('<script type="text/javascript">
    			function updateTextInput(val) {
    			  document.getElementById("cutoff_label").innerHTML=val; 
					}				
				$(function() {
					checkGeneric();
					$("#dataSelect").change(function() {
						$("#tissueSelect").load("/wpi/bin/TissueAnalyzer/datasets/"+$(this).val()+"_tissues_opt.txt");
					});
				
					$("#speciesSelect").change(function() {
						var speciesValue = $(this).val();
						var hash = '.$hash.';
						var dataSelect = document.getElementById("dataSelect");
						dataSelect.options.length = 0;//empty the drop-down;			
						var i = 0;
						var id = "";
						$.each(hash,function(index, value){							
							if (value["species"]==speciesValue){
								//console.log("index: " + index + ",value: " + value + value["species"] + speciesValue);
								dataSelect.options[dataSelect.options.length] = new Option(index, index);
								if (i==0){ // get the first dataset s id;
									id = index;
								}
								i++;						
							}
						});
						$("#tissueSelect").load("/wpi/bin/TissueAnalyzer/datasets/"+id+"_tissues_opt.txt"); //update the tissue drop down with the new first id;
					});
				});
				</script>');
	}

	function createCriteriaDiv($select, $dataset, $cutoff, $species, $datasetSelect, $speciesArray, $generic){
		global $wgOut;
		
		$speciesSelect = "<form name= action=''><SELECT name='species' id='speciesSelect' size='1'>";
		foreach ($speciesArray as $value) {
    	$speciesSelect .= (strcmp ( trim ( $species ), $value ) == 0) ? "<option selected=\"selected\">$value</option>" : "<option>$value</option>";
		}
		$speciesSelect .=  "</SELECT>";

		$tissueSelect = "<SELECT name='select' id='tissueSelect' size='1'>";
		$path = "wpi/bin/TissueAnalyzer/datasets/".$dataset."_tissues_opt.txt";
		$tissuesFile = fopen ($path , r );
		while ( ! feof ( $tissuesFile ) ) {
			$line = fgets ( $tissuesFile );
			$tissue = str_replace("</option>",'',$line);
			$tissue = str_replace("<option>",'',$tissue);
			$tissue = trim ( $tissue );
			if ($tissue === '')
				break;
			$tissueSelect .= (strcmp ( trim ( $select ), trim ( $tissue ) ) == 0) ? "<option selected=\"selected\">$tissue</option>" : "<option>$tissue</option>";
		}
		fclose ( $tissuesFile );
		$tissueSelect .= "</SELECT>";


		$button = <<<HTML
			<INPUT type="submit" name="button" value="Apply" style="cursor:pointer;font-weight:900" >
HTML;

		$slide ='		
				<input type="range" id="cutoff_id" name="cutoff" min="4" max="6" value="'.$cutoff.'" step="1" onchange="updateTextInput(this.value);">                                                       
   				<label id="cutoff_label">'.$cutoff.'</label>';

		$out = <<<HTML
			<div style="display:inline-block;width:100%;">
			<div style="width:800px;display:inline-block;overflow:visible;border: 3px solid blue;">	
			<b>Select your criteria and then click on Apply:</b>
			<table id='nsselect' class='allpages'>
				<tr>
					<td align='bottom'>Species:</td>
					<td align='bottom'>Dataset:</td>		

					<td align='bottom' style='width:270px'>Tissue:</td>
					<td align='bottom'>Active gene expression cutoff:</td>					
				</tr>
				<tr>
					<td align='left'>{$speciesSelect}</td>
					<td align='left'>{$datasetSelect}</td>

					<td align='left'>{$tissueSelect}</td>
					<td align='left'>{$slide}</td>	
					<td align='right'>{$button}</td>						
				</tr>		
			</table>			
HTML;

		$wgOut->addHTML($out);
		if ($generic=="on") $checked="checked";
		else $checked="";
		$checkbox = '
								<input name="generic" id="check" type="checkbox" onchange="checkGeneric(this)" '.$checked.'>
								<label for="check">Show generic pathways</label></form></div>';				
		$wgOut->addHTML($checkbox);

		$out = <<<HTML
			<div style="display:inline-block;overflow:visible;width:100%;">
				<label style='float:right'>	
					<a target="_blank" href="http://projects.bigcat.unimaas.nl/tissueanalyzer/quick-start/">Quick start</a>
				</label>
				<label style='float:right'>	
					<a target="_blank" href="http://projects.bigcat.unimaas.nl/tissueanalyzer/quick-start/">Datasets</a>
				</label>
				<label style='float:right'>	
					<a target="_blank" href="http://projects.bigcat.unimaas.nl/tissueanalyzer/documentation/">Documentation</a>
				</label>
				
			</div></div>
HTML;
		$out = <<<HTML
			<style type='text/css'>
					.navi {
							//list-style: none;
							margin: 0;
							padding: 0px 0 0px 30px;
							font-size: 135%;
					}
					.navi a {
							display: block;
							color: #000;
							padding: 4px 0 0px 0px;
							text-decoration: none;
					}
					.navi a:hover {
							background-color: #555;
							color: white;
					}			
			</style>
			<div style="display:inline-block;overflow:visible;">
				<ul class='navi'>						
					<li><a target="_blank" href="http://projects.bigcat.unimaas.nl/tissueanalyzer/quick-start/">Quick start</a></li>
					<li><a target="_blank" href="http://projects.bigcat.unimaas.nl/tissueanalyzer/datasets/">Datasets</a></li>
					<li><a target="_blank" href="http://projects.bigcat.unimaas.nl/tissueanalyzer/documentation/">Documentation</a></li>
				</ul>
			</div>
		</div>
HTML;
		$wgOut->addHTML($out);
	}


	function createTableResults($select, $dataset, $cutoff, $topTen){
		global $wgOut;

		$url = array ();
		$mean = array ();
		$perc = array ();
		$median = array ();
		$nami = array();
		$path_id = array();
		$path_rev = array();
		$ratio = array();
				
		$average = 0;
		$date = '';
		$collection = "Curated";
		$tissue = fopen ( "wpi/data/TissueAnalyzer/$collection/$dataset/$cutoff/Tissue/$select.txt", r );
		while ( ! feof ( $tissue ) ) {
			$line = fgets ( $tissue );
			if (strpos($line, '#') !== false && strpos($line, 'PDT') !== false   ) {
				$dateTmp = explode ( "\t", $line );
				$date = $dateTmp[1]." PDT";
			}
			if (strpos($line, '#') !== false && strpos($line, 'PST') !== false   ) {
				$dateTmp = explode ( "\t", $line );
				$date = $dateTmp[1]." PST";
			}
			$pieces = explode ( "\t", $line );
			$name = $pieces [0];
			$id = strstr ( $name, 'WP' );
			$id = explode ( "_", $id );
			$path_name = explode ( "_WP", $name );
			$path_name = str_replace ( "Hs_", '', $path_name[0] );
			$path_name = str_replace ( "Mm_", '', $path_name );
			$path_name = str_replace ( "Bt_", '', $path_name );
			$title = Title::newFromText ( ( string ) $id [0], NS_PATHWAY );
			$pp = explode ( ".",$pieces[2]);
			if (isset ( $title )) {
				array_push ( $url, '<a target="_blank" href="' . $title->getFullURL () . '">' . $id[0] . '</a>' );
				array_push ( $mean, $pieces[1] );
				array_push ( $perc, $pp[0] );
				array_push ( $median, $pieces[3] );
				array_push ( $nami, $path_name);
				array_push ( $path_id, $id[0]);
				array_push ( $path_rev, $id[1]);
				array_push ( $ratio,$pieces[4]);
			}
		}

		array_multisort ($median, SORT_NUMERIC, SORT_DESC,
					$url, SORT_STRING, SORT_DESC,
					$mean, SORT_NUMERIC, SORT_DESC,
					$perc, SORT_NUMERIC, SORT_DESC,
					$ratio, SORT_NUMERIC, SORT_DESC,
					$path_id, SORT_NUMERIC, SORT_DESC,
					$path_rev, SORT_NUMERIC, SORT_DESC,
					$nami, SORT_STRING, SORT_DESC );

		$div = "<div style='display:inline-block;overflow:visible;width:100%'>
						<br>
						<label> Last build : $date</label>
						<label id='gradient' class='scale-title' style='float:right;display: none'>Gradient color scale</label><br/>";				
		$wgOut->addHTML ( $div );

		$nrShow = 20;
		$expand = "<b>View all rows...</b>";
		$collapse = "<b>View first ".($nrShow)." rows...</b>";
		$button = "<table style='display:inline-block;width:300px;margin: 0.5em 0em 0em 0px'><td width='51%'><div id='viewAll' onClick='".
				'doToggleTA("tissueTable", this, "' . $expand . '", "' . $collapse . '")' .
				"' style='cursor:pointer;color:#0000FF'>"."$expand<td width='45%'></table>";
			
		$html = "<div style='display:block;overflow:visible;width:100%'>
				<style type='text/css'>
				.scale-title {
				    text-align: left;
				    font-weight: bold;
				    font-size: 90%;
				    }
				  .scale-labels {
				    margin: 0;
				    padding: 0;
				    float: left;
				    list-style: none;
				    }
				  .scale-labels li {
				    display: block;
				    float: left;
				    width: 50px;
				    margin-bottom: 6px;
				    text-align: center;
				    font-size: 80%;
				    list-style: none;
				    }
				  .scale-labels li span {
				    display: block;
				    float: left;
				    height: 15px;
				    width: 50px;
				    }
				#gradient{
				display:inline !important;
				}	
				</style>					
				$button
				<ul class='scale-labels' style='display:inline-block;float:right'>
				    <li><span style='background:#8c8cb9;'></span>0 - 3</li>
				    <li><span style='background:#7676c3;'></span>3 - 5 </li>
				    <li><span style='background:#5151d6;'></span>5 - 7</li>
				    <li><span style='background:#3e3edf;'></span>7 - 10 </li>
				    <li><span style='background:#0000FE;'></span> >10 </li>
				</ul>			
				</div>";

		$html .= "
					<table id='tissueTable' class='wikitable sortable' style='display:inline-block;width:100%'>
					<tr class='table-blue-tableheadings' id='tr_header'>
					<td class='table-blue-headercell' style='width:44%'>Pathways</td>
					<td class='table-blue-headercell' align='center' style='width:10%'>Linkout</td>
					<td class='table-blue-headercell' align='center'style='width:10%'>Median</td>
					<td class='table-blue-headercell' style='width:1%'></td>
					<td class='table-blue-headercell' align='center' style='width:10%'>Active genes</td>
					<td class='table-blue-headercell' align='center' style='width:10%'>Measured genes</td>
					<td class='table-blue-headercell' align='center'style='width:10%' >Active/Measured %</td>
					<td class='table-blue-headercell' align='center'style='width:30%' >Average expression over all tissues</td>";	

		for($i = 0; $i < count ( $mean ); ++ $i) {
			$filename = "wpi/data/TissueAnalyzer/$collection/$dataset/$cutoff/Hs_$nami[$i]_$path_id[$i]_$path_rev[$i].txt";
			$filename2 = "wpi/data/TissueAnalyzer/$collection/$dataset/$cutoff/$nami[$i]_$path_id[$i]_$path_rev[$i].txt";
			$filename = (file_exists ( $filename )) ? $filename : $filename2;
			$list_genes = "";
			$active_index = 0;
			$measure_index = 0;
			$name = "";
			if (file_exists ( $filename )) {
				$file = fopen ( $filename, r );
				while ( ! feof ( $file ) ) {
					$line = fgets ( $file );
					if ($line == false)
						break;
					if (strpos($line, '#') !== false) {
						$averageTmp = explode ( "\t", $line );
						$average = $averageTmp[1];
					}
					$pieces = explode ( "\t", $line );
					$name = $pieces[0];
					if ($name == $select ){
						$genes = explode ( ",", $pieces [4] );
						$mesure = explode ( ",", $pieces [5] );
						$list_genes = "";
						foreach ( $mesure as $gene ) {
							$info = explode ( ' ', $gene );
							if (count ( $info ) > 1) {
								if (strpos($info[1], '&&') !== FALSE){ // Found it									
									$labels = explode ( '&&', $info[1] );
									foreach ( $labels as $geneLabel ) {
										$list_genes .= "&label[]=".$geneLabel;
										$measure_index = $measure_index + 1;
									}									
								}
								else{
									$list_genes .= "&label[]=".$info[1];
									$measure_index = $measure_index + 1;
								}
							}
						}
						foreach ( $genes as $gene ) {
							$info = explode ( ' ', $gene );
							if (count ( $info ) > 1) {
								if (strpos($info[1], '&&') !== FALSE){
									$labels = explode ( '&&', $info[1] );
									foreach ( $labels as $geneLabel ) {
										$list_genes .= "&label[]=".$geneLabel;
										$active_index = $active_index + 1;
									}									
								}
								else{
									$list_genes .= "&label[]=".$info[1];
									$active_index = $active_index + 1;
								}
							}
						}							
					}
				}
			}
			$number = explode ( "/",$ratio[$i]);

			$n = intval($number[0]);
			$m = intval($number[1]);

			// Note color: %23D9A4FF => #D9A4FF
			if (!$list_genes == ""){				
				if ($n===$m ){
					$list_genes .= "&colors=%236A03B2";						
					for($l = 1; $l < $active_index; ++ $l){
						$list_genes .= ",%236A03B2";
					}
				}
				else{
					$list_genes .= "&colors=%23B0B0B0";
					for($k = 1; $k < $measure_index; ++ $k){
						$list_genes .= ",%23B0B0B0";
					}
					for($l = 0; $l < $active_index; ++ $l){
						$list_genes .= ",%236A03B2";
					}
				}
			}
			$r = 0;
			$g = 0;
			$b = 0;
			
			if ( $median[$i] < 1.5 ) {
				$r = 170;
				$g = 170;
				$b = 170;
			}
			elseif ( $median[$i] > 10) {
				$color = 255;
				$r = 0;
				$g = 0;
				$b = 255;
			}
			else {
				$r = 170 - 2 *($median[$i]-1.5)/(10-1.5) * (255-170);
				$g = 170 - 2 * ($median[$i]-1.5)/(10-1.5) * (255-170);
				$b = 170 + ($median[$i]-1.5)/(10-1.5) * (255-170);
			}
			$rgb = array( $r, $g, $b );

			$hex = "#";
			$hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
			$hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
			$hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);

			if ($i < $nrShow && in_array($nami[$i], $topTen)){
				$doShow = 'toggleMe';	
				$styleBackground = "style='background:#C7FCF9;'";
			}
			else if ($i < $nrShow ){
				$doShow = '';
				$styleBackground = '';
			}
			else{
				$doShow = 'toggleMe';
			}

			$pathway_name = str_replace ( "_", " ", $nami[$i] );
			$jsonFile = explode (".", $filename );
			$jsonPath = "/".$jsonFile[0].".json";			

			$html .= <<<HTML
				<tr class='$doShow' $styleBackground id='$nami[$i]'>
				<td ><a  href='#path_viewer' onClick='tissue_viewer("$path_id[$i]","$list_genes","$pathway_name")'> $pathway_name</a></td>
				<td align='center' >$url[$i]</td>
				<td align='center' >$median[$i]</td>
				<td bgcolor='$hex' > </td>
				<td align='center' >$number[0]</td>
				<td align='center' >$number[1]</td>	
				<td align='center' >$perc[$i]</td>		
				<td align='center' >
						<a  id="inline" file="$jsonPath" pathway="$pathway_name" measured="$number[1]" tissue="$select" href="#data">$average</a></td>			
HTML;
		}
		fclose($tissue);
		$html .= '</table>';	
		$wgOut->addHTML($html);
	}



	function createViewerDivs($welcomePage, $cutoff){
		global $wgOut;
		$styleViewer = '';
		if (!$welcomePage){
				$styleViewer = 'style="display: none;"';
		}
									
		$html = '<style type="text/css">
								.my-legend .legend-title {
									text-align: left;
									margin-bottom: 5px;
									font-weight: bold;
									font-size: 90%;
									}
								.my-legend .legend-scale ul {
									margin: 0;
									margin-bottom: 5px;
									padding: 0;
									float: left;
									list-style: none;
									}
								.my-legend .legend-scale ul li {
								display: inline-block;
									font-size: 80%;
									list-style: none;
									margin-left: 0;
									line-height: 18px;
									margin-bottom: 2px;
									}
								.my-legend ul.legend-labels li span {
									display: block;
									float: left;
									height: 16px;
									width: 30px;
									margin-right: 5px;
									margin-left: 0;
									border: 1px solid #999;
									}
							</style>
				</div>
				
				<div class="my-legend" id="my-legend" '.$styleViewer.' >
					<div class="legend-title" id="legend-title" style="display:inline-block;width:100%">Highlighting legend</div>
						<div class="legend-scale" style="display:inline-block">
							<ul class="legend-labels">
								<li><span style="background:#6A03B2;"></span>Active gene (expression > '.$cutoff.')</li>
				    		<li><span style="background:#B0B0B0;"></span>Not-active gene (expression < '.$cutoff.')</li>
							</ul>				
						</div>
				</div>								
				<div id="pwyname" '.$styleViewer.'><b>Selected pathway:</b> Fatty Acid Biosynthesis</div>
				<div style="display:inline-block;overflow:visible;width:100%"><iframe id="path_viewer" 
					src ="http://www.wikipathways.org/wpi/PathwayWidget.php?id=WP357&label[]=ACSL4&label[]=ACSL3
								&label[]=ACACA&label[]=ECHDC1&label[]=PECR&label[]=MECR&label[]=ACSL6&label[]=ECHDC2&label[]=ACSL5&label[]=DECR
								1&label[]=ACAA2&label[]=ACLY&label[]=ACSL1&label[]=ECH1&label[]=ACACB&label[]=ECHS1&label[]=PC&label[]=ACAS2&label[]=FASN
								&label[]=ECHDC3&label[]=HADHSC&label[]=SCD&colors=%23B0B0B0,%23B0B0B0,%23B0B0B0,%23B0B0B0,%23B0B0B0,%23B0B0B0,
								%23B0B0B0,%23B0B0B0,%23B0B0B0,%23B0B0B0,%23B0B0B0,%23B0B0B0,%236A03B2,%236A03B2,%236A03B2,%236A03B2,%236A03B2,
								%236A03B2,%236A03B2,%236A03B2,%236A03B2,%236A03B2"
					width="100%" height="500px" '.$styleViewer.' >
				</iframe></div>
				<div style="display:none"><div style="height:600px;width:1400px" id="data"></div></div>';

		$wgOut->addHTML($html);
	}

	static function loadMessages() {
		static $messagesLoaded = false;
		global $wgMessageCache;
		if ($messagesLoaded)
			return true;
		$messagesLoaded = true;

		require (dirname ( __FILE__ ) . '/TissueAnalyzer.i18n.php');
		foreach ( $allMessages as $lang => $langMessages ) {
			$wgMessageCache->addMessages ( $langMessages, $lang );
		}
		return true;
	}
}
