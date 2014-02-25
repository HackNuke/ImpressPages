/**
 * @package ImpressPages
 *
 */
var IpWidget_Map;

(function($){
    "use strict";

    IpWidget_Map = function() {
        var controllerScope = this;
        this.$widgetObject = null;
        this.data = null;
        this.map = null;
        this.marker = null;

        this.init = function($widgetObject, data) {
            this.$widgetObject = $widgetObject;
            this.data = data;
            var context = this;
            var $map = this.$widgetObject.find('.ipsMap');

            if (jQuery.fn.ipWidgetMap && typeof(google) !== 'undefined' && typeof(google.maps) !== 'undefined' && typeof(google.maps.LatLng) !== 'undefined') {
                jQuery(this.$widgetObject.get()).ipWidgetMap();
            }



            var $resizeContainer = $('<div></div>');
            $map.replaceWith($resizeContainer);
            $resizeContainer.append($map);



            $resizeContainer.resizable({
                aspectRatio: false,
                maxWidth: context.$widgetObject.width(),
                resize: function(event, ui) {
                    $map.width(ui.size.width);
                    $map.height(ui.size.height);
                    $.proxy(context.initMap, context)();
                    $.proxy(context.save, context)();
                }
            });





                if (typeof(google) !== 'undefined' && typeof(google.maps) !== 'undefined' && typeof(google.maps.LatLng) !== 'undefined') {
                    $.proxy(initMap, this)();
                } else {
                    jQuery('body').on('ipGoogleMapsLoaded', jQuery.proxy(initMap, this));
                    ipLoadGoogleMaps();
                }





        };

        var initMap = function () {
            var context = this;
            var $widget = this.$widgetObject;
            var $map = $widget.find('.ipsMap');
            var data = this.data;
            //init map
            if (typeof(data.lat) == 'undefined') {
                data.lat = 0;
            }
            if (typeof(data.lng) == 'undefined') {
                data.lng = 0;
            }

            var mapOptions = {
                center: new google.maps.LatLng(data.lat, data.lng),
                zoom: 0
            };

            if (data.mapTypeId) {
                mapOptions.mapTypeId = data.mapTypeId;
            }
            if (data.zoom) {
                mapOptions.zoom = parseInt(data.zoom);
            }

            var map = new google.maps.Map($map.get(0), mapOptions);
            this.map = map;




            if ((typeof (data.markerlat) !== 'undefined') && (typeof (data.markerlng) !== 'undefined')) {
                this.marker = new google.maps.Marker({
                    position: new google.maps.LatLng(data.markerlat, data.markerlng),
                    map: this.map
                });
            }

            //bind map events
            google.maps.event.addListener(this.map, 'bounds_changed', $.proxy(save, this));
            google.maps.event.addListener(this.map, 'maptypeid_changed', $.proxy(save, this));

            google.maps.event.addListener(this.map, 'click', function(event) {
                $.proxy(placeMarker, context)(event.latLng);
            });


        }

        function placeMarker(location) {
            if ( this.marker ) {
                this.marker.setPosition(location);
            } else {
                this.marker = new google.maps.Marker({
                    position: location,
                    map: this.map
                });
            }


        }


        var save = function() {

            var curLatLng = this.map.getCenter();

            var data = {};

            data.lat = curLatLng.lat();
            data.lng = curLatLng.lng();
            data.zoom = this.map.getZoom();
            data.mapTypeId = this.map.mapTypeId;
            data.height = parseInt( this.$widgetObject.height());

            if (this.marker) {
                var markerPos = this.marker.getPosition();
                data.markerlat = markerPos.lat();
                data.markerlng = markerPos.lng();
            }
//            if (this.$widgetObject.width() - width <= 2) {
//                data = {
//                    method: 'autosize'
//                }
//            }

            this.$widgetObject.save(data, 0);
        }



    };

})(ip.jQuery);
