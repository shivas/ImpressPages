/*!
ImpressPages core init
 */

var ipGoogleMapsLoading = false;
var ipPingInterval;

/*
 * hook all widgets with plugins
 */

$(document).ready(function() {

    // Map widget
    if ($('.ipWidget-Map').length) {
        $('body').on('ipGoogleMapsLoaded', function () {
            $('.ipWidget-Map').ipWidgetMap();
        });

        if (typeof(google) !== 'undefined' && typeof(google.maps) !== 'undefined' && typeof(google.maps.LatLng) !== 'undefined') {
            ipGoogleMapsLoaded();
        } else {
            ipLoadGoogleMaps();
        }
    }

    ipInitForms();

    ipPingInterval = setInterval(ipPing, 1000 * 60 * 4);  //4min

    // add ipHas... classes when core elements load
    $(document).on('ipContentManagementInit', function() {
        $(document.body).addClass('ipHasAdminPanel');
    });
    $(document).on('ipAdminPanelInit', function() {
        $(document.body).addClass('ipHasAdminNavbar');
    });

});

var ipGoogleMapsLoaded = function () {
    $(document.body).trigger('ipGoogleMapsLoaded');
};

var ipLoadGoogleMaps = function () {
    if (ipGoogleMapsLoading) {
        return;
    }
    ipGoogleMapsLoading = true;
    var script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&' +
        'callback=ipGoogleMapsLoaded';
    document.body.appendChild(script);
};


var ipPing = function () {
    $.ajax({
        url: ip.baseUrl,
        data: {pa: 'Core.ping'},
        method: 'GET'
    })
}
