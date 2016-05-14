/*
* Look at http://simple2kx.com/use-jquery-ajax-and-json-format-to-update-multiple-fields-on-webpage/
*/






jQuery(document).ready(function () {
	updateWeather()
setInterval(function(){
 updateWeather() // this will run after every 5 seconds
}, 5000);
});

function updateWeather() {
	jQuery.getJSON("/current_weather.php", { } )
	  .done(function( json ) {
	    console.log( "JSON Data: " + json.windSpeed.value );
	    jQuery("#windSpeed").text(json.windSpeed.value);
	    jQuery("#windGust").text(json.windGust.value);
		 jQuery("#windDir").text(json.windDir.value);
		 jQuery("#outTemp").text(json.outTemp.value);
		 jQuery("#timestamp").text(json.timestamp);
		 jQuery("#pressure").text(json.pressure.value);
		 
	    
	    
	  })
	  .fail(function( jqxhr, textStatus, error ) {
	    var err = textStatus + ", " + error;
	    console.log( "Request Failed: " + err );
	});

	
}


