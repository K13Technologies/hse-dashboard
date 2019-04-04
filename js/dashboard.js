// Docs at http://simpleweatherjs.com

/* Does your browser support geolocation? */
if ("geolocation" in navigator) {
  $('.js-geolocation').show(); 
} else {
  $('.js-geolocation').hide();
}

/* Where in the world are you? */
$('.js-geolocation').on('click', function() {
  navigator.geolocation.getCurrentPosition(function(position) {
    loadWeather(position.coords.latitude+','+position.coords.longitude); //load weather using your lat/lng coordinates
  });
});

/* 
* Test Locations
* Austin lat/long: 30.2676,-97.74298
* Austin WOEID: 2357536
*/
$(document).ready(function() {
    $( "#datepicker" ).datepicker();

    // If the browser supports geolocation, get the weather. If not, don't show the module.
    if ("geolocation" in navigator) {
        $("#weather").html('Loading weather...');
        setInterval(
            navigator.geolocation.getCurrentPosition(function(position) {
                //load weather using user lat/lng coordinates
                // Attempts to use cached coordinates from the last ten minutes
                //Update the weather every 10 minutes.
                loadWeather(position.coords.latitude+','+position.coords.longitude);
              }, function (error) { 
                        if (error.code == error.PERMISSION_DENIED){
                            html = 'Please allow this web page access to your location to display local weather';
                            $("#weather").html(html);
                        }
                        else {
                            html = 'Error getting weather (' + error + ')';
                            $("#weather").html(html);
                        }
                    })
                , {maximumAge:60000}
                , 600000
        ); 
    }
    else {
        // Browser does not support geolocation
        $('#weatherBox').hide();
    }
});

function loadWeather(location, woeid) {
      $.simpleWeather({
        location: location,
        woeid: woeid,
        unit: 'c',
        success: function(weather) {
            html = '<table style="width:100%">' +
                      '<tr>' +
                        '<td>' +
                            '<h2><img src="' + weather.thumbnail + '"></img> ' + weather.temp + '&deg;' + weather.units.temp + '</h2>' +
                        '</td>' +
                        '<td>' +
                            '<ul>' +
                                '<li>' + weather.city + ', ' + weather.region + '</li>' +
                                '<li class="currently">' + weather.currently + '</li>' +
                                '<li>High: ' + weather.high + '</li>' +
                            '</ul>' +
                        '</td>' +
                        '<td>' +
                            '<ul>' +
                                '<li>Low: '+ weather.low + '</li>' +
                                '<li>Sunrise: '+ weather.sunrise + '</li>' +
                                '<li>Sunset: '+ weather.sunset + '</li></ul>' +
                            '</ul>' +
                        '</td>' +
                      '</tr>' +
                      '<tr>' +
                        '<p>Updated '+ moment(timestamp).fromNow() +'</p>' +
                      '</tr>' +
                    '</table>';
            //html += '<li>'+weather.alt.temp+'&deg;C</li></ul>'; // Could enable double click for switching to farenheit later

            //Don't forget to include the moment.js plugin.
            var timestamp = moment(weather.updated);
            
            $("#weather").html(html);
        },
        error: function(error) {
          $("#weather").html('<p>'+error+'</p>');
        }
      });
}

// CUSTOM RESIZER FOR TEXT SAVED $ TEXT
var textContainer = document.getElementById('cont');
var text = document.getElementById('textContent');
var textLength = $("#textContent").text().length;
var firstLoadWidth;

if (textLength >= 1 && textLength < 8) {
    cont.style.fontSize = '60px';
}
else if (textLength >= 8 && textLength < 11) {
    cont.style.fontSize = '50px';
}
else if (textLength >= 12) {
    cont.style.fontSize = '40px';
}