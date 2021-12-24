(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.share_price_page = {
    attach: function (context, settings) {
      var Url = drupalSettings.share_price.euronext_url;
      var sbf120Url = 'https://gateway.euronext.com/api/instrumentDetail?code=FR0003999481&codification=ISIN&exchCode=XPAR&sessionQuality=DT&view=FULL&authKey=6f3c04e7e101e0257b0a2e1a28694787c978f9a6d264372d84275906b41d4f99';

      // load a locale
      numeral.register('locale', 'fr', {
        delimiters: {
          thousands: ' ',
          decimal: ','
        },
        abbreviations: {
          thousand: 'k',
          million: 'm',
          billion: 'M',
          trillion: 't'
        },
        ordinal : function (number) {
          return number === 1 ? 'er' : 'ème';
        },
        currency: {
          symbol: '€'
        }
      });
      numeral.locale(drupalSettings.share_price.language);

      function getPerf(data, perType){
        var result = null;
        if(!data || !data.instr) return null;
        data.instr.perf.forEach(function(perf) {
          if(perf && perf.perType === perType){
            return result = perf;
          }
        });
        return result;
      }

      $.ajax({
        url: Url,
        success: function(data){
          var perfD = getPerf(data, 'D');
          var perfY = getPerf(data, 'Y');
          var perf6M = getPerf(data, '6M');
          var perf3M = getPerf(data, '3M');
          var perf52W = getPerf(data, '52W');
          var perf1M = getPerf(data, '1M');

          //variations %
          $('#d_var').prepend(formatPercentage(perfD.var));
          // Change Symbol.
          if (perfD.var > 0) {
            $('#d_var').addClass('cellPrice--up');
          }
          else if (perfD.var < 0) {
            $('#d_var').addClass('cellPrice--down');
          }

          $('#y_var').prepend(formatPercentage(perfY.var));
          // Change Symbol.
          if (perfY.var > 0) {
            $('#y_var').addClass('cellPrice--up');
          }
          else if (perfY.var < 0) {
            $('#y_var').addClass('cellPrice--down');
          }

          $('.cellPrice--up .cellPrice-rate').append('<span class="sr-only">' + Drupal.t('Increasing') + '</span>');
          $('.cellPrice--down .cellPrice-rate').append('<span class="sr-only">' + Drupal.t('Decreasing') + '</span>');

          $('#m_var').append(formatPercentage(perf1M.var));
          $('#3m_var').append(formatPercentage(perf3M.var));
          $('#6m_var').append(formatPercentage(perf6M.var));
          $('#y_vartrade').append(formatPercentage(perf52W.var));
          $('#52w_var').append(formatPercentage(perfY.var));

          // prices & points
          $('#lastPx').append(formatPrice(data.instr.currInstrSess.lastPx));
          $('#d_perStartPx').append(formatPrice(data.instr.currInstrSess.openPx));
          $('#prevInstrSess_lastPx').append(formatPrice(data.instr.prevInstrSess.lastPx));
          $('#d_highPx').append(formatPrice(perfD.highPx));
          $('#d_lowPx').append(formatPrice(perfD.lowPx));
          $('#d_tradedQty').append(numeral(parseFloat(perfD.tradedQty)).format('0'));
          $('#d_tradedAmt').append(formatPrice(perfD.tradedAmt));
          $('#y_highPx').append(formatPrice(perfY.highPx ));
          $('#y_lowPx').append(formatPrice(perfY.lowPx));

          //large number quantity & amount
          $('#m_tradedAmt').append(numeral(parseFloat(perf1M.tradedAmt.toLowerCase())).format('0.0a'));
          $('#3m_tradedAmt').append(numeral(parseFloat(perf3M.tradedAmt.toLowerCase())).format('0.0a'));
          $('#6m_tradedAmt').append(numeral(parseFloat(perf6M.tradedAmt.toLowerCase())).format('0.0a'));
          $('#y_tradedAmt').append(numeral(parseFloat(perfY.tradedAmt.toLowerCase())).format('0.0a'));
          $('#52w_tradedAmt').append(numeral(parseFloat(perf52W.tradedAmt.toLowerCase())).format('0.0a'));
          $('#m_tradedQty').append(numeral(parseFloat(perf1M.tradedQty.toLowerCase())).format('0.0a'));
          $('#3m_tradedQty').append(numeral(parseFloat(perf3M.tradedQty.toLowerCase())).format('0.0a'));
          $('#6m_tradedQty').append(numeral(parseFloat(perf6M.tradedQty.toLowerCase())).format('0.0a'));
          $('#y_tradedQty').append(numeral(parseFloat(perfY.tradedQty.toLowerCase())).format('0.0a'));
          $('#52w_tradedQty').append(numeral(parseFloat(perf52W.tradedQty.toLowerCase())).format('0.0a'));

          var dateObj = moment(data.instr.currInstrSess.dateTime, "YYYYMMDD-HH:mm:ss").toDate();
          $('#shareDate').append(formatDate(dateObj, drupalSettings.share_price.language));

        }
      });

      $.ajax({
        url: sbf120Url,
        success: function(data){
          var perfD = getPerf(data, 'D');
          var perfY = getPerf(data, 'Y');
          var perf6M = getPerf(data, '6M');
          var perf3M = getPerf(data, '3M');
          var perf52W = getPerf(data, '52W');
          var perf1M = getPerf(data, '1M');
          //variation %
          $('#sbf120_y_var_short').prepend(formatPercentage(perfY.var));
          // Change Symbol.
          if (perfY.var > 0) {
            $('#sbf120_y_var_short').addClass('cellPrice--up');
          }
          else if (perfY.var < 0) {
            $('#sbf120_y_var_short').addClass('cellPrice--down');
          }
          $('#sbf120_m_var').append(formatPercentage(perf1M.var));
          $('#sbf120_3m_var').append(formatPercentage(perf3M.var));
          $('#sbf120_6m_var').append(formatPercentage(perf6M.var));
          $('#sbf120_y_var').append(formatPercentage(perfY.var));
          $('#sbf120_52w_var').append(formatPercentage(perf52W.var));
          $('#sbf120_d_var').prepend(formatPercentage(perfD.var));
          // Change Symbol.
          if (perfD.var > 0) {
            $('#sbf120_d_var').addClass('cellPrice--up');
          }
          else if (perfD.var < 0) {
            $('#sbf120_d_var').addClass('cellPrice--down');
          }

          // prices & points
          $('#sbf120_lastPx').append(numeral(parseFloat(data.instr.currInstrSess.lastPx)).format('0.00')+' pts');
          $('#sbf120_prevInstrSess_lastPx').append(numeral(parseFloat(data.instr.prevInstrSess.lastPx)).format('0.00'));
          $('#sbf120_d_perStartPx').append(numeral(parseFloat(data.instr.currInstrSess.openPx)).format('0.00'));
          $('#sbf120_d_highPx').append(numeral(parseFloat(perfD.highPx)).format('0.00'));
          $('#sbf120_d_lowPx').append(numeral(parseFloat(perfD.lowPx)).format('0.00'));
          $('#sbf120_y_highPx').append(numeral(parseFloat(perfY.highPx)).format('0.00'));
          $('#sbf120_y_lowPx').append(numeral(parseFloat(perfY.lowPx)).format('0.00'));

        }
      });

      function formatPrice(price) {
        price = new Number(price);
        return price.toLocaleString(drupalSettings.share_price.language, {style: 'currency', currency: 'EUR', currencyDisplay: 'symbol'});
      }

      function formatPercentage(percentage) {
        percentage = (new Number(percentage));
        return (percentage > 0 ? '+' : '') +
          percentage.toLocaleString(drupalSettings.share_price.language, {style: 'percent', maximumFractionDigits: 2});
      }

      function formatDate(dateObj) {
        return  dateObj.toLocaleDateString(drupalSettings.share_price.language, { year: 'numeric', month: 'numeric', day: 'numeric' }) +
          ' ' + dateObj.toLocaleTimeString(drupalSettings.share_price.language, { hour: '2-digit', minute:'2-digit', timeZoneName:'short' });
      }

    }
  };

})(jQuery, Drupal,drupalSettings);
