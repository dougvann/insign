/**
 * @file
 * Masonry for newsroom.
 */

(function (Drupal, drupalSettings, $) {

  'use strict';

  /**
   * Attaches behavior to HTML element.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.share_price_investors = {
    attach: function (context, settings) {

      if (typeof context === 'undefined') {
        context = document;
      }

      // Make the process only if link with class "share-price" exist and
      // only once.
      var $sharePriceElmt = $(context).find('.SharePriceInvestors');
      if (
        $sharePriceElmt.length === 1 &&
        !$sharePriceElmt.data('init-share-price')
      ) {

        $sharePriceElmt.data('init-share-price', true);

        var getPerf = function (data, perType){
          var result = null;
          if(!data || !data.instr) return null;
          data.instr.perf.forEach(function(perf) {
            if(perf && perf.perType === perType){
              return result = perf;
            }
          });
          return result;
        };

        // Xml is automatically parsed by jquery.
        $.getJSON(drupalSettings.SharePriceInvestors.webserviceUrl).done(function( json ) {
          var perfD = getPerf(json, 'D');
          var perfY = getPerf(json, 'Y');
          var perf6M = getPerf(json, '6M');
          var perf3M = getPerf(json, '3M');
          var perf52W = getPerf(json, '52W');
          var perf1M = getPerf(json, '1M');

          // Find xml node that interests us.
          var data = json.instr;
          // Price + percentage.
          $('#SharePrice-rate').append(formatPrice(
            data.currInstrSess.lastPx,
            drupalSettings.SharePriceInvestors.currencyCode,
            drupalSettings.SharePriceInvestors.language
          ));
          $('#SharePrice-percent').append(formatPercentage(
            perfD.var,
            drupalSettings.SharePriceInvestors.language
          ));
          $('#SharePrice-start').append(formatPrice(data.currInstrSess.openPx,
            drupalSettings.SharePriceInvestors.currencyCode,
            drupalSettings.SharePriceInvestors.language));
          // Change Symbol.
          if (perfD.var > 0) {
            $sharePriceElmt.addClass('SharePrice--up');
          }
          else if (perfD.var < 0) {
            $sharePriceElmt.addClass('SharePrice--down');
          }

          // Display share-price element. Don't use show/display property to let
          // responsive rules work.
          $sharePriceElmt.removeAttr('style');
        });
      }

      function formatPrice(price, currencyCode, language) {
        price = new Number(price);
        return price.toLocaleString(language, {style: 'currency', currency: currencyCode, currencyDisplay: 'symbol'});
      }

      function formatPercentage(percentage, language) {
        percentage = (new Number(percentage));
        return (percentage > 0 ? '+' : '') +
          percentage.toLocaleString(language, {style: 'percent', maximumFractionDigits: 2});
      }

      function formatDate(dateObj, language) {
        return  dateObj.toLocaleDateString(language, { year: 'numeric', month: 'numeric', day: 'numeric' }) +
                ' ' + dateObj.toLocaleTimeString(language, { hour: '2-digit', minute:'2-digit', timeZoneName:'short' });
      }
    }
  };

}(Drupal, drupalSettings, jQuery));
