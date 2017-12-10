(function ($, window, Drupal) {

  'use strict';

  Drupal.behaviors.simplified_bookkeeping = {
    attach: function () {

      var statement_amount = $("#edit-field-booking-amount-wrapper input").val();
      var left_amount = Number(statement_amount);
      var statement_date = $(".form-item-field-booking-date-0-value-date input").val();

      $(".ief-entity-table tr td.inline-entity-form-booking-amount").each(function(e, amount) {
        var line_amount = Number($(this).html());
        left_amount = Number(left_amount) - line_amount;
      });

      $(".ief-form-bottom .form-item-field-booking-form-inline-entity-form-field-booking-amount-0-value input").val(left_amount);
      $(".ief-form-bottom .form-item-field-booking-form-inline-entity-form-field-booking-date-0-value-date input").val(statement_date);
    }
  };

})(jQuery, window, Drupal);