(function ($, window, Drupal) {

  'use strict';

  Drupal.behaviors.simplified_bookkeeping_statements_form = {
    attach: function () {

      $('.view-statements #edit-date-from').datepicker({ dateFormat: 'yy/mm/dd' });
      $('.view-statements #edit-date-to').datepicker({ dateFormat: 'yy/mm/dd' });

    }
  };

})(jQuery, window, Drupal);