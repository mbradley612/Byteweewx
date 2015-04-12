<?php
/**
 * @Plugin Name:  ByteWeewx - WEEWX Wordpress Plugin by ByteInsight
 * Plugin URI: http://weather.davies-barnard.uk/about
 * Description: A plugin that works with the bytexml template available from http://weather.davies-barnard.uk/about that makes most WEEWX data available inside a Wordpress page via shortcodes.
 * Version: 0.0.1
 * Author: Chris Davies-Barnard
 * Author URI: http://weather.davies-barnard.uk/about
 * License: GPL2
 */


//Check that the class does not already exist.
if(!class_exists("byteweewx")) {

	
	global $byteweewx_version;
	$byteweewx_version = "0.0.1";


	/**
	* The ByteWeewx Plugin Class
	*/
	class byteweewx {
	
		protected $url = "";
		protected $xml = Null;
		protected $fcxml = Null;
		protected $output = "";		

		/**
		* Constructor Function
		*/
		function byteweewx () {
		
			$this->url = plugins_url();
			
		} //END constructor function
			
			
		/**
		* Header Method to put stuff into the <head>
		*/
		function byteweewx_addHeaderCode() {
			echo "<!-- This site uses byteweewx (the WEEWX Data Stream Plugin) - Version 0.0.1 -->";
		} //END byteweewx_addHeaderCode


		/**
 		* Proper way to enqueue scripts and styles
 		*/
		function byteweewx_scripts() {
			wp_enqueue_style( 'style-name', plugins_url('/media/styles.css', __FILE__ ));
		}

		/**
		* Get Data Method
		*/
		function byteweewx_get_data() {
		
			$file_path = WP_PLUGIN_DIR."/byteweewx/data/data.xml";
			$file_url = $this->url."/byteweewx/data/data.xml";
		
			//If Null then we load the xml file.
			if ( is_null($this->xml) ) {
				if (file_exists($file_path)) {
					libxml_use_internal_errors(true);
	    			$this->xml = simplexml_load_file($file_url);
	    			
	    			//Something is wrong with the XML file
	    			if ($this->xml === false) {
	    				$this->output .= "Failed loading XML<br />";
	    				foreach(libxml_get_errors() as $error) {
	        				$this->output .= $error->message."<br />";
	    				}
	    				return 0;
					} 
		    		
			    //Failed to open the XML data file
				} else {
					$this->output = "Error opening file!</br>";
	    			return 0; 
	    		}
	    	
	    	} //End not null else
    		
    		//Method completes without error.
    		return 1; 
    		
		} //END byteweewx_get_data
		
		/**
		* Get Forecast Data Method
		*/
		function byteweewx_get_forecast_data() {
		
			$file_path = WP_PLUGIN_DIR."/byteweewx/data/forecast.xml";
			$file_url = $this->url."/byteweewx/data/forecast.xml";
		
			//If Null then we load the xml file.
			if ( is_null($this->fcxml) ) {
				if (file_exists($file_path)) {
					libxml_use_internal_errors(true);
	    			$this->fcxml = simplexml_load_file($file_url);
	    			
	    			//Something is wrong with the XML file
	    			if ($this->fcxml === false) {
	    				$this->output .= "Failed loading XML<br />";
	    				foreach(libxml_get_errors() as $error) {
	        				$this->output .= $error->message."<br />";
	    				}
	    				return 0;
					} 
		    		
			    //Failed to open the XML data file
				} else {
					$this->output = "Error opening file!</br>";
	    			return 0; 
	    		}
	    	
	    	} //End not null else
    		
    		//Method completes without error.
    		return 1; 
    		
		} //END byteweewx_get_forecast_data


		/**
		* Display method for station info
		*/
		function byteweewx_display_stationinfo() {
			$this->output .= "<table class='weewx-table'>".
				"<tr><td>Hardware</td><td>".$this->xml->station->hardware."</td></tr>".
				"<tr><td>Up Time</td><td>".$this->xml->uptime."</td></tr>".
				"<tr><td>Weewx Version</td><td>".$this->xml->version."</td></tr>".
				"<tr><td>Weewx Time</td><td>".$this->xml->weewxtime."</td></tr>".				
				"</table>";
		} //END byteweewx_display_stationinfo
		
				
		/**
		* Display method for current observation
		*/
		function byteweewx_display_location($id=null) {
			//This is a map request
			if (!is_null($id) || !empty($id)) {
				$path = explode(':', $id);
				if(sizeof($path)!=4) {
					$this->output .= "MAP ERROR for Location:".$this->xml->station->latitude.",".$this->xml->station->longitude;
				}
				$this->output .= "<img src='https://maps.googleapis.com/maps/api/staticmap?center=".
					$this->xml->station->latitude['degs'].",".$this->xml->station->longitude['degs'].
					"&zoom=$path[0]&size=$path[1]x$path[2]' />";
			//Just the location
			} else {
				$this->output .= "Location:".$this->xml->station->latitude.",".$this->xml->station->longitude;
			}
		}		
		
		/**
		* Display method for current observation
		*/
		function byteweewx_display_current() {
			$this->output .= "<table class='weewx-table'>".
				"<tr><td>Observed at</td><td>".$this->xml->current->dateTime."</td></tr>".			
				"<tr><td>Outside Temp</td><td>".$this->xml->current->outTemp."</td></tr>".
				"<tr><td>Barometer</td><td>".$this->xml->current->barometer." Trending:".$this->xml->current->barometer['trend']."</td></tr>".
				"<tr><td>Wind</td><td>".$this->xml->current->wind." @ ".$this->xml->current->wind['compass']." (".$this->xml->current->wind['winddir'].")"."</td></tr>".
				"<tr><td>Wind Chill</td><td>".$this->xml->current->windchill."</td></tr>".
				"<tr><td>Rain Rate</td><td>".$this->xml->current->rainRate."</td></tr>".				
				"<tr><td>Dew Point</td><td>".$this->xml->current->dewpoint."</td></tr>".
				"<tr><td>Heat Index</td><td>".$this->xml->current->heatindex."</td></tr>".
				"<tr><td>Inside Temp</td><td>".$this->xml->current->inTemp."</td></tr>".					
				//Uncomment these following lines for UV and Radiation
				//"<tr><td>UV</td><td>".$this->xml->current->uv."</td></tr>".
				//"<tr><td>Radiation</td><td>".$this->xml->current->radiation."</td></tr>".				
				"</table>";
		} //END byteweewx_display_current
		
		
		/**
		* Display Table for Archive Intervals
		*/
		function byteweewx_display_table($data) {
			$this->output .= "<table class='weewx-table'>".
				"<tr><td>Outside Max Temp</td><td>".$data->outTempmax."</td><td>".str_replace("(","<br />(",$data->outTempmax['time'])."</td></tr>".
				"<tr><td>Outside Min Temp</td><td>".$data->outTempmin."</td><td>".str_replace("(","<br />(",$data->outTempmin['time'])."</td></tr>".					
				"<tr><td>Heat Index</td><td>".$data->heatindex."</td><td>".str_replace("(","<br />(",$data->heatindex['time'])."</td></tr>".
				"<tr><td>Wind Chill</td><td>".$data->windchill."</td><td>".str_replace("(","<br />(",$data->windchill['time'])."</td></tr>".
				"<tr><td>Max Humidity</td><td>".$data->maxhumidity."</td><td>".str_replace("(","<br />(",$data->maxhumidity['time'])."</td></tr>".
				"<tr><td>Min Humidity</td><td>".$data->minhumidity."</td><td>".str_replace("(","<br />(",$data->minhumidity['time'])."</td></tr>".	
				"<tr><td>Max Dewpoint</td><td>".$data->maxdewpoint."</td><td>".str_replace("(","<br />(",$data->maxdewpoint['time'])."</td></tr>".
				"<tr><td>Min Dewpoint</td><td>".$data->mindewpoint."</td><td>".str_replace("(","<br />(",$data->mindewpoint['time'])."</td></tr>".	
				"<tr><td>Max Barometer</td><td>".$data->maxbarometer."</td><td>".str_replace("(","<br />(",$data->maxbarometer['time'])."</td></tr>".
				"<tr><td>Min Barometer</td><td>".$data->minbarometer."</td><td>".str_replace("(","<br />(",$data->minbarometer['time'])."</td></tr>".	
				"<tr><td>Rain Rate / Sum</td><td>".$data->rainrate."<br />".$data->rainsum."</td><td>".str_replace("(","<br />(",$data->rainrate['time'])."</td></tr>".
				"<tr><td>Max Wind</td><td>".$data->windmax."<br />@".$data->windmax['direction']."</td><td>".str_replace("(","<br />(",$data->windmax['time'])."</td></tr>".									
				"<tr><td>Vector Wind</td><td>".$data->windvector."<br />@".$data->windvector['direction']."</td><td>RMS=".$data->windrms."<br />AVG=".$data->windaverage."</td></tr>".	
				"<tr><td>Inside Max Temp</td><td>".$data->inTempmax."</td><td>".str_replace("(","<br />(",$data->inTempmax['time'])."</td></tr>".
				"<tr><td>Inside Min Temp</td><td>".$data->inTempmin."</td><td>".str_replace("(","<br />(",$data->inTempmin['time'])."</td></tr>".																	
				"</table>";
		} //END byteweewx_display_table

				
		/**
		* Get a single element from the xml document.
		*/
		function byteweewx_display_element($id=null){
			if (!is_null($id) || !empty($id)) {
				$path = explode(':', $id);
				switch(sizeof($path)){
					case 1:
						$this->output .= $this->xml->{$path[0]};
						break;
					case 2:
						$this->output .=$this->xml->{$path[0]}->{$path[1]}.
						$this->xml->{$path[0]}->{$path[1]}['time'];
						break;					
					default:
						$this->output .= "Unknown or Undefined Element";
						break;
				}
			} else {
				$this->output .= "Unknown or Undefined Element";
			}
		} //END byteweewx_display_element
		
		
		/**
		* Display one of the images that has been uploaded
		*/
		function byteweewx_display_image($id=null){
			if (!is_null($id) || !empty($id)) {
				$this->output .= "<img src='".$this->url."/byteweewx/data/".$id.".png' />";
			}
		} //END byteweewx_display_image
		
		
		/**
		* Display method for current observation
		*/
		function byteweewx_display_almanac() {
			$this->output .= "<h4>Sun</h4>".
				"<table class='weewx-table'>".
				"<tr><td>Sun Rise</td><td>".$this->xml->almanac->sun->sunrise." starting at ".$this->xml->almanac->sun->twilightRise."</td></tr>".
				"<tr><td>Transit</td><td>".$this->xml->almanac->sun->transit." at ".number_format((float)$this->xml->almanac->sun->azimuth,2)."&deg;</td></tr>".
				"<tr><td>Sun Set</td><td>".$this->xml->almanac->sun->sunset." finishing at ".$this->xml->almanac->sun->twilightSet."</td></tr>".
				"<tr><td>Altitude</td><td>".number_format((float)$this->xml->almanac->sun->altitude,2)."</td></tr>".
				"<tr><td>Declination</td><td>".number_format((float)$this->xml->almanac->sun->declination,2)."</td></tr>".
				"<tr><td>Right Ascension</td><td>".number_format((float)$this->xml->almanac->sun->rightascension,2)."</td></tr>".
				"<tr><td>Equinox</td><td>".$this->xml->almanac->sun->equinox."</td></tr>".
				"<tr><td>Solstice</td><td>".$this->xml->almanac->sun->solstice."</td></tr>".		
				"</table>";
			$this->output .= "<h4>Moon</h4>".
				"<table class='weewx-table'>".
				"<tr><td>Moon Rise</td><td>".$this->xml->almanac->moon->moonrise." starting at ".$this->xml->almanac->moon->twilightRise."</td></tr>".
				"<tr><td>Transit</td><td>".$this->xml->almanac->moon->transit." at ".number_format((float)$this->xml->almanac->moon->azimuth,2)."&deg;</td></tr>".
				"<tr><td>Moon Set</td><td>".$this->xml->almanac->moon->moonset." finishing at ".$this->xml->almanac->moon->twilightSet."</td></tr>".
				"<tr><td>Altitude</td><td>".number_format((float)$this->xml->almanac->moon->altitude,2)."</td></tr>".
				"<tr><td>Declination</td><td>".number_format((float)$this->xml->almanac->moon->declination,2)."</td></tr>".
				"<tr><td>Right Ascension</td><td>".number_format((float)$this->xml->almanac->sun->rightascension,2)."</td></tr>".
				"<tr><td>New Moon</td><td>".$this->xml->almanac->moon->newmoon."</td></tr>".
				"<tr><td>Full Moon</td><td>".$this->xml->almanac->moon->fullmoon."</td></tr>".
				"<tr><td>Moon Phase</td><td>".$this->xml->almanac->moon->phase."(".$this->xml->almanac->moon->phase['fullness']."%)</td></tr>".			
				"</table>";
		} //byteweewx_display
		
		
		/**
		* Display method for archive files
		* that produces a list of the available txt files.
		*/
		function byteweewx_display_archive() {		
			$file_url = $this->url."/byteweewx/data/NOAA/";
			$archive_path = WP_PLUGIN_DIR."/byteweewx/data/NOAA";
			$files = preg_grep('/^([^.])/', scandir($archive_path));
			arsort($files);
			$this->output .= "<ul>";
			foreach ($files as $filename) {
    			if (!strpos($filename,'tmp')) {
        			$this->output .= "<li><a target='_blank' href='".$file_url.$filename."'>".$filename."</a></li>";
    			}
			}
			$this->output .= "</ul>";
		} //END byteweewx_display_archive
		
		
		/**
		* Display method for the forecast
		*/		
		function byteweewx_display_forecast($id) {
			if($this->byteweewx_get_forecast_data()==0 || $this->fcxml->forecast['status']=="false") {
				$this->output .= "Forecast not available!";
				return 0;
			}
			if (!is_null($id) || !empty($id)) {
				$results = $this->fcxml->xpath('//period');
				foreach($results as $period) {
					$this->byteweewx_display_period_forecast($period,$this->fcxml->forecast->legend);
				}			
			} else {
				$results = $this->fcxml->xpath('//summary');
				foreach($results as $summary) {
					$this->byteweewx_display_summary_forecast($summary,$this->fcxml->forecast->legend);
				}
			}				
		}
		
		function byteweewx_display_period_forecast($period,$legend) {
			$ip = $this->url."/byteweewx/media/icons/";
			$windDirs = "background:url(".$ip.$period->wind['icon'].".png)";
			$this->output .= "<h6>".$period->day." ".$period->date." <strong>".$period->hour."</strong></h6>";
			$this->output .= "<table class='weewx-table'><tr>".
				"<td></td>".
				"<td><img class='icon' title='".$legend->temp['title']."' src='".$ip.$legend->temp['icon']."'/></td>".
				"<td><img class='icon' title='".$legend->dewpoint['title']."' src='".$ip.$legend->dewpoint['icon']."'/></td>".
				"<td><img class='icon' title='".$legend->humidity['title']."' src='".$ip.$legend->humidity['icon']."'/></td>".
				"<td><img class='icon' title='".$legend->wind['title']."' src='".$ip.$legend->wind['icon']."'/></td>".
				"<td><img class='icon' title='".$legend->pop['title']."' src='".$ip.$legend->pop['icon']."'/></td>".
				"<td><img class='icon' title='".$legend->sun['title']."' src='".$ip.$legend->sun['icon']."'/></td>".
				"<td><img class='icon' title='".$legend->moon['title']."' src='".$ip.$legend->moon['icon']."'/></td>".
				"<td><img class='icon' title='".$legend->tides['title']."' src='".$ip.$legend->tides['icon']."'/></td>".
				"</tr><tr>".
				"<td><img class='icon' src='".$ip.$period->outlook['icon']."'/></td>".
				"<td>".$period->temp.$legend->temp."</td>".
				"<td>".$period->dewpoint.$legend->dewpoint."</td>".
				"<td>".$period->humidity.$legend->humidity."</td>".
				"<td class='col-wind' style='".$windDirs."' title='".$period->wind."'>".$period->windSpd."<br />".$legend->wind."</td>".
				"<td>".$period->pop->chance.$period->precip.$period->obvis."</td>".
				"<td>".$this->getAlmanac($period->sun->rise,"Rises").$this->getAlmanac($period->sun->set,"Sets")."</td>".
				"<td>".$this->getAlmanac($period->moon->rise,"Rises").$this->getAlmanac($period->moon->set,"Sets").$this->getAlmanac($period->moon->phase,"<br />%:")."</td>".
				"<td></td>".				
				"</tr></table>";
		}
		
		function getAlmanac($action,$mode){
			if($action != "") {
				return $mode."<br />".$action;
			}
			return "";
		}
		
		function byteweewx_display_summary_forecast($summary,$legend) {
			$ip = $this->url."/byteweewx/media/icons/";
			$windDirs = $this->getWindDirs($summary->windDir,$ip);
			$this->output .= "<h6>".$summary->day." ".$summary->date." Summary</h6>";
			$this->output .= "<table class='weewx-table'><tr>".
				"<td></td>".
				"<td><img class='icon' title='".$legend->temp['title']."' src='".$ip.$legend->temp['icon']."'/></td>".
				"<td><img class='icon' title='".$legend->dewpoint['title']."' src='".$ip.$legend->dewpoint['icon']."'/></td>".
				"<td><img class='icon' title='".$legend->humidity['title']."' src='".$ip.$legend->humidity['icon']."'/></td>".
				"<td><img class='icon' title='".$legend->wind['title']."' src='".$ip.$legend->wind['icon']."'/></td>".
				"<td><img class='icon' title='".$legend->pop['title']."' src='".$ip.$legend->pop['icon']."'/></td>".
				"<td><img class='icon' title='".$legend->sun['title']."' src='".$ip.$legend->sun['icon']."'/></td>".
				"<td><img class='icon' title='".$legend->moon['title']."' src='".$ip.$legend->moon['icon']."'/></td>".
				"<td><img class='icon' title='".$legend->tides['title']."' src='".$ip.$legend->tides['icon']."'/></td>".				
				"</tr><tr>".
				"<td><img class='icon' src='".$ip.$summary->outlook['icon']."'/></td>".
				"<td>H:".$summary->temphi.$legend->temp."<br />L:".$summary->templo.$legend->temp."</td>".
				"<td>H:".$summary->dewpointMax.$legend->dewpoint."<br />L:".$summary->dewpointMin.$legend->dewpoint."</td>".
				"<td>H:".$summary->humidityMax.$legend->humidity."<br />L:".$summary->humidityMin.$legend->humidity."</td>".
				"<td class='col-wind' style='".$windDirs."'>".$summary->windSpd."<br />".$legend->wind."</td>".
				"<td>".$summary->pop->chance.$summary->precip.$summary->obvis."</td>".
				"<td>R:".$summary->sun->rise."<br />S:".$summary->sun->set."</td>".
				"<td>R:".$summary->moon->rise."<br />S:".$summary->moon->set."<br />%:".$summary->moon->phase."</td>".
				"<td></td>".
				"</tr></table>";
		}
		

		
		/** 
		* Gets the background urls for the CSS Styles for the wind direction.
		*/
		function getWindDirs($windDir,$ip) {
			$return = "background:url()";
			foreach($windDir->event as $d) {
				$path = ", url(".$ip.$d.".png)";
				$return .= $path;
			}
			$return .= ";";
			return $return;
		} //END getWindDirs
		
				
		/**
		* Method to extract any required atttributes from the shortcode.
		*/
		function byteweewx_display_handler($atts=null,$content=null) {
			$this->output = "";
			if(is_null($atts) || empty($atts)) {
				$this->output .= "Missing Short Code Argument";
			} else {
				// extract our shortcode e.g. [wewgpi station="43" id="ad53f7d7f4"]
				extract(shortcode_atts(array(
					'element' => null,
					'id' => null,
				), $atts));
				
				//If we have a element attribute then process it.
				if (!empty($element) && $this->byteweewx_get_data()==1) {
					//$this->output .= $element;
					switch($element) {
						case "stationinfo":
							$this->byteweewx_display_stationinfo();
							break;
						case "location":
							$this->byteweewx_display_location($id);
							break;
						case "current":
							$this->byteweewx_display_current();
							break;
						case "day":
							$this->byteweewx_display_table($this->xml->day);
							break;
						case "week":
							$this->byteweewx_display_table($this->xml->week);
							break;
						case "month":
							$this->byteweewx_display_table($this->xml->month);
							break;
						case "year":
							$this->byteweewx_display_table($this->xml->year);
							break;							
						case "image":
							$this->byteweewx_display_image($id);
							break;
						case "almanac":
							$this->byteweewx_display_almanac();
							break;
						case "archive":
							$this->byteweewx_display_archive();
							break;
						case "forecast":
							$this->byteweewx_display_forecast($id);
							break;
						case "single":
							$this->byteweewx_display_element($id);
							break;						
						default:
							$this->output .= "Unknown Short Code Argument ($element)";
							break;	
					}
				} else {
					$this->output .= "Missing Element Value";
				}				
				
			}
			return $this->output;
			
			
		} //END byteweewx_display_handler


	} //END of Class
		
} //END of If Exists



/***** Plugin start up code is below. *****/


//Check a class object has been created
if(class_exists("byteweewx")) { $byteweewx = new byteweewx(); }


//Run these functions on install
//register_activation_hook(__FILE__,array(&$wewgpi,'wewgpi_install'));
//register_activation_hook(__FILE__,array(&$wewgpi,'wewgpi_install_data'));


//Set up all required Actions and Filters for the byteweewx Object
if(isset($byteweewx)) {
	
	//Add any code to the <head> element and queue scripts
	add_action('wp_head',array(&$byteweewx,'byteweewx_addHeaderCode'),1);
	add_action( 'wp_enqueue_scripts', array(&$byteweewx,'byteweewx_scripts'),1);
	
	//add_action('admin_menu', array(&$wewgpi,'wewgpi_admin_actions'));  
	
	// Add shortcode support.
	if (function_exists('add_shortcode')) {
		add_shortcode('weewx', array(&$byteweewx,'byteweewx_display_handler'));
	}
	



} //END Actions and Filters.
?>
