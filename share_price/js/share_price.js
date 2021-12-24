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
  Drupal.behaviors.share_price = {
    attach: function (context, settings) {

      if (typeof context === 'undefined') {
        context = document;
      }

      // Make the process only if link with class "share-price" exist and
      // only once.
      var $sharePriceElmt = $(context).find('.SharePriceHeader');
      $sharePriceElmt.append('<span class="sr-only">' + Drupal.t('Share price') + '</span>');

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
        $.getJSON(drupalSettings.SharePrice.webserviceUrl).done(function( json ) {
          var perfD = getPerf(json, 'D');
          var perfY = getPerf(json, 'Y');
          var perf6M = getPerf(json, '6M');
          var perf3M = getPerf(json, '3M');
          var perf52W = getPerf(json, '52W');
          var perf1M = getPerf(json, '1M');

          // Find xml node that interests us.
          var data = json.instr;
          // Price + percentage.
          var strongElmt = $(
            '<strong class="SharePrice-rate">' +
            formatPrice(
              data.currInstrSess.lastPx,
              drupalSettings.SharePrice.currencyCode,
              drupalSettings.SharePrice.language
            ) + '  <span class="SharePrice-percent"> ' +
            formatPercentage(
              perfD.var,
              drupalSettings.SharePrice.language
            ) +
            '</span></strong>'
          );
          $sharePriceElmt.append(strongElmt);

          // Date.
          var rawDate = data.currInstrSess.dateTime;
          var dateObj = moment(data.currInstrSess.dateTime, "YYYYMMDD-HH:mm:ss").toDate();
          $sharePriceElmt.append('<span class="SharePrice-date"></span>');
          $sharePriceElmt.find('.SharePrice-date').append(
            document.createTextNode(formatDate(dateObj, drupalSettings.SharePrice.language))
          );
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

      /* TODO Move this code to custom_breadcrumb */

      var $breadcrumb = $(context).find('.breadcrumb');
      var $li = $breadcrumb.find('li');
      $li.each(function(index, el){
        var $this = $(this);
        if(!$this.find('a').length){
          if($li.length !== index + 1){
            $this.html(('<a href="' + window.location.href.substring(0, window.location.href.lastIndexOf('/')) + '">'+$this.html()+'</a>'));
          }
        }
      });
        if($('article.node').hasClass('node--view-mode-restricted')){
            var text = 'Restricted content';
            if(window.location.href.indexOf('fr/') > 0 ){
                text = 'Accès restreint';
            }
            if(!$('.restricted-content-text').length){
                $('.LayerBanner-heading').after('<p class="restricted-content-text">'+text+'</p>');
            }
            var enUrl = 'restricted-content';
            var frUrl = 'acces-restreint';
            if(window.location.href.indexOf('fr/' + enUrl) > 0 ){
              window.location.href = window.location.toString().replace(enUrl, frUrl);
            }
            if(window.location.href.indexOf('en/' + frUrl) > 0){
              window.location.href = window.location.toString().replace(frUrl, enUrl);
            }
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
